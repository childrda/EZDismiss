<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Driver;
use App\Models\School;
use App\Models\Student;
use App\Services\ActivityLogger;
use App\Services\QueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MobileEntryController extends Controller
{
    public function __construct(
        protected QueueService $queueService,
        protected ActivityLogger $logger,
    ) {
    }

    public function index(Request $request): View
    {
        $school = $this->resolveSchool($request);
        $lanes = range(1, $school->lane_count);

        // Load existing checkins for display
        // Positions are ordered correctly in the database, but we display sequential numbers (1, 2, 3...)
        $laneEntries = [];
        foreach ($lanes as $lane) {
            $checkins = Checkin::with(['driver', 'calls.student'])
                ->where('school_id', $school->id)
                ->where('lane', $lane)
                ->orderBy('position')
                ->get()
                ->map(function ($checkin) {
                    return [
                        'id' => $checkin->id,
                        'position' => $checkin->position, // Store actual DB position
                        'driver' => [
                            'id' => $checkin->driver_id,
                            'name' => $checkin->driver?->name,
                        ],
                        'students' => $checkin->calls->map(fn ($call) => [
                            'id' => $call->student_id,
                            'name' => $call->student?->name,
                        ])->filter()->values()->all(),
                    ];
                })
                ->values()
                ->all();

            $laneEntries[$lane] = $checkins;
        }

        return view('mobile-entry.index', [
            'school' => $school,
            'lanes' => $lanes,
            'laneEntries' => $laneEntries,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $term = $request->validate([
            'term' => ['required', 'string', 'min:2'],
        ])['term'];

        $school = $this->resolveSchool($request);

        // Get students who are already in the queue (not released)
        $studentsInQueue = Checkin::where('school_id', $school->id)
            ->whereHas('calls', function ($query) {
                $query->whereNotIn('status', ['released', 'exception', 'hold']);
            })
            ->with('calls.student')
            ->get()
            ->pluck('calls')
            ->flatten()
            ->pluck('student_id')
            ->unique()
            ->filter()
            ->toArray();

        $students = Student::query()
            ->where('school_id', $school->id)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('powerschool_id', 'like', "%{$term}%");
            })
            ->with('authorizedPickups.driver')
            ->limit(10)
            ->get()
            ->map(function ($student) use ($studentsInQueue) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'grade' => $student->grade,
                    'inQueue' => in_array($student->id, $studentsInQueue, true),
                    'drivers' => $student->authorizedPickups->map(fn ($pickup) => [
                        'id' => $pickup->driver_id,
                        'name' => $pickup->driver?->name,
                        'tag_uid' => $pickup->driver?->tag_uid,
                    ]),
                ];
            });

        $drivers = Driver::query()
            ->where('school_id', $school->id)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'phone', 'email', 'tag_uid', 'vehicle_desc']);

        return response()->json([
            'students' => $students,
            'drivers' => $drivers,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'student_ids' => ['array'],
            'lane' => ['required', 'integer', 'min:1'],
            'position' => ['nullable', 'integer', 'min:0'],
            'school_id' => ['nullable', 'integer'],
        ]);

        $driver = Driver::with('authorizedPickups.student')->findOrFail($data['driver_id']);
        $school = $this->resolveSchool($request, $driver);

        $position = isset($data['position']) ? $data['position'] : null;
        $checkin = $this->queueService->manualCheckin($school, $driver, $data['lane'], $position);

        // Get all students linked to this driver
        $linkedStudentIds = $driver->authorizedPickups
            ->pluck('student_id')
            ->filter()
            ->unique()
            ->toArray();

        // Merge with explicitly provided student_ids (if any)
        $studentIdsToAdd = !empty($data['student_ids']) 
            ? array_unique(array_merge($linkedStudentIds, $data['student_ids']))
            : $linkedStudentIds;

        // Add all students to the checkin
        foreach ($studentIdsToAdd as $studentId) {
            // Check if student exists and belongs to the same school
            $student = Student::where('id', $studentId)
                ->where('school_id', $school->id)
                ->first();

            if ($student) {
                $checkin->calls()->create([
                    'school_id' => $school->id,
                    'student_id' => $studentId,
                    'status' => 'called',
                    'by_user_id' => $request->user()->id,
                ]);
            }
        }

        $this->logger->log('manual_insert', 'Manual entry created', [
            'driver_id' => $driver->id,
            'student_ids' => $studentIdsToAdd,
            'linked_students_auto_added' => !empty($linkedStudentIds),
            'lane' => $data['lane'],
            'position' => $checkin->position,
            'checkin_id' => $checkin->id,
        ]);

        return response()->json([
            'message' => 'Manual checkin created.',
            'checkin_id' => $checkin->id,
        ]);
    }

    protected function resolveSchool(Request $request, ?Driver $driver = null): School
    {
        if ($request->user()->isDistrictAdmin()) {
            // Priority: URL parameter > Session > First school
            $schoolId = null;
            
            if ($request->has('school_id') && $request->filled('school_id')) {
                $schoolId = (int) $request->input('school_id');
            } elseif (session()->has('district_admin_school_id')) {
                $schoolId = session('district_admin_school_id');
            } else {
                $schoolId = School::query()->value('id');
                // Set default school in session if none selected
                if ($schoolId) {
                    session(['district_admin_school_id' => $schoolId]);
                }
            }

            if (!$schoolId || $schoolId === 0) {
                abort(404, 'No school available. Please create a school first.');
            }

            return School::findOrFail($schoolId);
        }

        if ($request->user()?->school) {
            return $request->user()->school;
        }

        if ($request->filled('school_id')) {
            return School::findOrFail($request->integer('school_id'));
        }

        if ($driver?->school) {
            return $driver->school;
        }

        return School::query()->firstOrFail();
    }
}

