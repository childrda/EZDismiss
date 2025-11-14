<?php

namespace App\Services;

use App\Models\AuthorizedPickup;
use App\Models\Driver;
use App\Models\Homeroom;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CsvImportService
{
    public function preview(string $type, UploadedFile $file, School $school): array
    {
        return $this->process($type, $file, $school, true);
    }

    public function import(string $type, UploadedFile $file, School $school): array
    {
        return $this->process($type, $file, $school, false);
    }

    protected function process(string $type, UploadedFile $file, School $school, bool $dryRun): array
    {
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            throw new \RuntimeException('Unable to read uploaded file.');
        }

        $header = null;
        $rows = collect();

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            if (!$header) {
                $header = $this->normaliseHeader($data);
                continue;
            }

            $rows->push(array_combine($header, array_map('trim', $data)));
        }

        fclose($handle);

        return match ($type) {
            'students' => $this->processStudents($rows, $school, $dryRun),
            'parents' => $this->processParents($rows, $school, $dryRun),
            'teachers' => $this->processTeachers($rows, $school, $dryRun),
            'homerooms' => $this->processHomerooms($rows, $school, $dryRun),
            'authorized_pickups' => $this->processAuthorizedPickups($rows, $school, $dryRun),
            default => [
                'created' => 0,
                'updated' => 0,
                'skipped' => $rows->count(),
                'errors' => ['Unsupported import type.'],
                'rows' => [],
            ],
        };
    }

    protected function normaliseHeader(array $header): array
    {
        return array_map(fn ($value) => Str::of($value)->lower()->replace(' ', '_')->toString(), $header);
    }

    protected function processStudents(Collection $rows, School $school, bool $dryRun): array
    {
        $summary = $this->initialSummary();
        $results = [];

        DB::transaction(function () use ($rows, $school, $dryRun, &$summary, &$results): void {
            foreach ($rows as $row) {
                $identifier = $row['powerschool_id'] ?? null;
                $name = $row['name'] ?? null;

                if (!$identifier || !$name) {
                    $summary['skipped']++;
                    $summary['errors'][] = "Missing powerschool_id or name for row.";
                    continue;
                }

                $student = Student::where('powerschool_id', $identifier)->first();
                $action = $student ? 'updated' : 'created';

                if (!$dryRun) {
                    $student ??= new Student(['school_id' => $school->id]);
                    $student->fill([
                        'powerschool_id' => $identifier,
                        'name' => $name,
                        'grade' => $row['grade'] ?? null,
                    ]);

                    if (!empty($row['homeroom'])) {
                        $teacherId = null;
                        
                        // Try to link to teacher User account if teacher_email is provided
                        if (!empty($row['teacher_email'])) {
                            $teacher = User::where('email', $row['teacher_email'])
                                ->where('school_id', $school->id)
                                ->where('role', 'teacher')
                                ->first();
                            
                            if ($teacher) {
                                $teacherId = $teacher->id;
                            }
                        }
                        
                        // If no teacher_email or teacher not found, try matching by teacher_name
                        if (!$teacherId && !empty($row['teacher_name'])) {
                            $teacher = User::where('name', $row['teacher_name'])
                                ->where('school_id', $school->id)
                                ->where('role', 'teacher')
                                ->first();
                            
                            if ($teacher) {
                                $teacherId = $teacher->id;
                            }
                        }
                        
                        $homeroom = Homeroom::firstOrCreate(
                            ['school_id' => $school->id, 'name' => $row['homeroom']],
                            [
                                'teacher_name' => $row['teacher_name'] ?? null,
                                'teacher_id' => $teacherId,
                            ],
                        );
                        
                        // Update teacher_id if it wasn't set initially but we now have a match
                        if ($homeroom->teacher_id !== $teacherId && $teacherId) {
                            $homeroom->update(['teacher_id' => $teacherId]);
                        }
                        
                        $student->homeroom()->associate($homeroom);
                    }

                    $student->save();

                    if (!empty($row['parent_ids'])) {
                        $this->linkParentsToStudent($student, $row['parent_ids']);
                    }
                }

                $summary[$action]++;
                $results[] = [
                    'identifier' => $identifier,
                    'name' => $name,
                    'action' => $action,
                ];
            }

            if ($dryRun) {
                DB::rollBack();
            }
        });

        $summary['rows'] = $results;

        return $summary;
    }

    protected function processParents(Collection $rows, School $school, bool $dryRun): array
    {
        $summary = $this->initialSummary();
        $results = [];

        DB::transaction(function () use ($rows, $school, $dryRun, &$summary, &$results): void {
            foreach ($rows as $row) {
                $email = $row['email'] ?? null;
                $externalId = $row['parent_id'] ?? null;
                $name = $row['name'] ?? null;

                if (!$email && !$externalId) {
                    $summary['skipped']++;
                    $summary['errors'][] = 'Parent row missing email or parent_id.';
                    continue;
                }

                $driver = Driver::query()
                    ->where(function ($query) use ($email, $externalId) {
                        if ($email) {
                            $query->where('email', $email);
                        }

                        if ($externalId) {
                            $query->when(
                                $email,
                                fn ($q) => $q->orWhere('external_id', $externalId),
                                fn ($q) => $q->where('external_id', $externalId),
                            );
                        }
                    })
                    ->first();

                $action = $driver ? 'updated' : 'created';

                if (!$dryRun) {
                    $driver ??= new Driver(['school_id' => $school->id]);
                    $driver->fill([
                        'name' => $name ?? 'Unknown',
                        'email' => $email,
                        'phone' => $row['phone'] ?? null,
                        'vehicle_desc' => $row['vehicle_desc'] ?? null,
                        'external_id' => $externalId,
                    ]);

                    if (!empty($row['tag_uid'])) {
                        $driver->tag_uid = $row['tag_uid'];
                    }

                    $driver->save();

                    if (!empty($row['student_ids'])) {
                        $studentIdentifiers = array_map('trim', explode(',', $row['student_ids']));
                        $students = Student::whereIn('powerschool_id', $studentIdentifiers)->get();

                        foreach ($students as $student) {
                            AuthorizedPickup::updateOrCreate(
                                [
                                    'student_id' => $student->id,
                                    'driver_id' => $driver->id,
                                ],
                                [
                                    'school_id' => $school->id,
                                    'relationship' => $row['relationship'] ?? null,
                                ]
                            );
                        }
                    }
                }

                $summary[$action]++;
                $results[] = [
                    'identifier' => $externalId ?? $email,
                    'name' => $name,
                    'action' => $action,
                    'tag_uid' => $row['tag_uid'] ?? null,
                ];
            }

            if ($dryRun) {
                DB::rollBack();
            }
        });

        $summary['rows'] = $results;

        return $summary;
    }

    protected function processTeachers(Collection $rows, School $school, bool $dryRun): array
    {
        $summary = $this->initialSummary();
        $results = [];

        DB::transaction(function () use ($rows, $school, $dryRun, &$summary, &$results): void {
            foreach ($rows as $row) {
                $email = $row['email'] ?? null;
                $name = $row['name'] ?? null;

                if (!$email || !$name) {
                    $summary['skipped']++;
                    $summary['errors'][] = 'Teacher row missing email or name.';
                    continue;
                }

                $user = User::where('email', $email)->first();
                $action = $user ? 'updated' : 'created';

                if (!$dryRun) {
                    $user ??= new User();
                    $user->fill([
                        'name' => $name,
                        'email' => $email,
                        'role' => 'teacher',
                        'school_id' => $school->id,
                    ]);

                    if (!$user->exists) {
                        $user->password = Hash::make(Str::random(12));
                    }

                    $user->save();
                }

                $summary[$action]++;
                $results[] = [
                    'identifier' => $email,
                    'name' => $name,
                    'action' => $action,
                ];
            }

            if ($dryRun) {
                DB::rollBack();
            }
        });

        $summary['rows'] = $results;

        return $summary;
    }

    protected function processHomerooms(Collection $rows, School $school, bool $dryRun): array
    {
        $summary = $this->initialSummary();
        $results = [];

        DB::transaction(function () use ($rows, $school, $dryRun, &$summary, &$results): void {
            foreach ($rows as $row) {
                $name = $row['name'] ?? null;

                if (!$name) {
                    $summary['skipped']++;
                    $summary['errors'][] = 'Homeroom row missing name.';
                    continue;
                }

                $homeroom = Homeroom::where('name', $name)->first();
                $action = $homeroom ? 'updated' : 'created';

                if (!$dryRun) {
                    $teacherId = null;
                    
                    // Try to link to teacher User account if teacher_email is provided
                    if (!empty($row['teacher_email'])) {
                        $teacher = User::where('email', $row['teacher_email'])
                            ->where('school_id', $school->id)
                            ->where('role', 'teacher')
                            ->first();
                        
                        if ($teacher) {
                            $teacherId = $teacher->id;
                        }
                    }
                    
                    // If no teacher_email or teacher not found, try matching by teacher_name
                    if (!$teacherId && !empty($row['teacher_name'])) {
                        $teacher = User::where('name', $row['teacher_name'])
                            ->where('school_id', $school->id)
                            ->where('role', 'teacher')
                            ->first();
                        
                        if ($teacher) {
                            $teacherId = $teacher->id;
                        }
                    }
                    
                    $homeroom = Homeroom::updateOrCreate(
                        ['school_id' => $school->id, 'name' => $name],
                        [
                            'teacher_name' => $row['teacher_name'] ?? null,
                            'teacher_id' => $teacherId,
                        ],
                    );
                }

                $summary[$action]++;
                $results[] = [
                    'identifier' => $name,
                    'action' => $action,
                ];
            }

            if ($dryRun) {
                DB::rollBack();
            }
        });

        $summary['rows'] = $results;

        return $summary;
    }

    protected function processAuthorizedPickups(Collection $rows, School $school, bool $dryRun): array
    {
        $summary = $this->initialSummary();
        $results = [];

        DB::transaction(function () use ($rows, $school, $dryRun, &$summary, &$results): void {
            foreach ($rows as $row) {
                $parentId = $row['parent_id'] ?? null;
                $studentId = $row['student_powerschool_id'] ?? null;

                if (!$parentId || !$studentId) {
                    $summary['skipped']++;
                    $summary['errors'][] = 'Authorized pickup row missing parent_id or student_powerschool_id.';
                    continue;
                }

                $driver = Driver::where('external_id', $parentId)->first();
                $student = Student::where('powerschool_id', $studentId)->first();

                if (!$driver || !$student) {
                    $summary['skipped']++;
                    $summary['errors'][] = "Could not match driver {$parentId} or student {$studentId}.";
                    continue;
                }

                $pickup = AuthorizedPickup::where('student_id', $student->id)
                    ->where('driver_id', $driver->id)
                    ->first();

                $action = $pickup ? 'updated' : 'created';

                if (!$dryRun) {
                    AuthorizedPickup::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'driver_id' => $driver->id,
                        ],
                        [
                            'school_id' => $school->id,
                            'relationship' => $row['relationship'] ?? null,
                            'expires_at' => $row['expires_at'] ?? null,
                        ]
                    );
                }

                $summary[$action]++;
                $results[] = [
                    'identifier' => "{$parentId}-{$studentId}",
                    'action' => $action,
                ];
            }

            if ($dryRun) {
                DB::rollBack();
            }
        });

        $summary['rows'] = $results;

        return $summary;
    }

    protected function linkParentsToStudent(Student $student, string $parentIds): void
    {
        $identifiers = array_map('trim', explode(',', $parentIds));

        $drivers = Driver::whereIn('external_id', $identifiers)->get();

        foreach ($drivers as $driver) {
            AuthorizedPickup::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'driver_id' => $driver->id,
                ],
                [
                    'school_id' => $student->school_id,
                ]
            );
        }
    }

    protected function initialSummary(): array
    {
        return [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'rows' => [],
        ];
    }
}

