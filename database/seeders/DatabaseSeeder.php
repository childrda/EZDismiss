<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\AuthorizedPickup;
use App\Models\Driver;
use App\Models\Homeroom;
use App\Models\RfidReader;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $districtAdmin = User::factory()
            ->districtAdmin()
            ->create([
                'name' => 'District Admin',
                'email' => 'district.admin@example.com',
                'password' => Hash::make('password'),
            ]);

        $schools = collect([
            [
                'name' => 'Northview Elementary',
                'address' => '100 Main St, Springfield',
                'phone' => '555-0100',
                'primary_color' => '#0ea5e9',
            ],
            [
                'name' => 'Lakeside Academy',
                'address' => '200 Lake Rd, Springfield',
                'phone' => '555-0200',
                'primary_color' => '#6366f1',
            ],
        ])->map(function (array $data) {
            return School::create(array_merge($data, [
                'lane_count' => 2,
                'lane_color_mode' => 'per_lane',
                'default_lane_behavior' => 'manual',
                'api_key' => Str::uuid()->toString(),
            ]));
        });

        $driverCounter = 1;

        $schools->each(function (School $school, int $index) use (&$driverCounter): void {
            $admin = User::factory()->forSchool($school->id, 'school_admin')->create([
                'name' => "{$school->name} Admin",
                'email' => "admin{$index + 1}@example.com",
                'password' => Hash::make('password'),
            ]);

            $staff = User::factory()->forSchool($school->id, 'staff')->create([
                'name' => "{$school->name} Staff",
                'email' => "staff{$index + 1}@example.com",
                'password' => Hash::make('password'),
            ]);

            $teachers = collect(range(1, 2))->map(function ($num) use ($school) {
                return User::factory()->forSchool($school->id, 'teacher')->create([
                    'name' => "{$school->name} Teacher {$num}",
                    'email' => "teacher{$school->id}{$num}@example.com",
                    'password' => Hash::make('password'),
                ]);
            });

            $homerooms = collect(range(1, 2))->map(function ($num) use ($school, $teachers) {
                return Homeroom::create([
                    'school_id' => $school->id,
                    'name' => "Room {$num}",
                    'teacher_name' => $teachers[$num - 1]->name ?? "Teacher {$num}",
                ]);
            });

            $students = collect(range(1, 5))->map(function ($num) use ($school, $homerooms) {
                return Student::create([
                    'school_id' => $school->id,
                    'name' => "{$school->name} Student {$num}",
                    'grade' => ['K', '1st', '2nd', '3rd', '4th'][$num - 1],
                    'powerschool_id' => "{$school->id}00{$num}",
                    'homeroom_id' => $homerooms[$num % $homerooms->count()]->id,
                ]);
            });

            $drivers = collect(range(1, 3))->map(function () use ($school, &$driverCounter) {
                $driver = Driver::create([
                    'school_id' => $school->id,
                    'name' => "Driver {$driverCounter}",
                    'email' => "driver{$driverCounter}@example.com",
                    'phone' => "555-03{$driverCounter}",
                    'vehicle_desc' => 'Blue SUV',
                    'external_id' => "P{$driverCounter}",
                    'tag_uid' => strtoupper(Str::random(8)),
                ]);

                $driverCounter++;

                return $driver;
            });

            $students->each(function (Student $student, int $key) use ($drivers): void {
                $driver = $drivers[$key % $drivers->count()];

                AuthorizedPickup::create([
                    'school_id' => $student->school_id,
                    'student_id' => $student->id,
                    'driver_id' => $driver->id,
                    'relationship' => 'Parent',
                ]);
            });

            foreach (range(1, $school->lane_count) as $lane) {
                RfidReader::create([
                    'school_id' => $school->id,
                    'name' => "{$school->name} Lane {$lane}",
                    'lane' => $lane,
                    'endpoint_type' => 'http',
                    'ip_address' => "192.168.1.1{$lane}",
                    'api_key' => Str::uuid()->toString(),
                    'enabled' => true,
                ]);
            }

            ActivityLog::create([
                'school_id' => $school->id,
                'user_id' => $admin->id,
                'event_type' => 'seed',
                'description' => 'Seed data created for school',
                'context' => [
                    'students' => $students->count(),
                    'drivers' => $drivers->count(),
                ],
                'created_at' => now(),
            ]);
        });

        ActivityLog::create([
            'school_id' => null,
            'user_id' => $districtAdmin->id,
            'event_type' => 'seed',
            'description' => 'Initial district admin created.',
            'context' => [],
            'created_at' => now(),
        ]);
    }
}
