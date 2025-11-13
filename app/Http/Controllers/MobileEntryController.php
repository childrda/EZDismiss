<?php

namespace App\Http\Controllers;

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
        $school = $request->user()->school ?? School::findOrFail($request->get('school_id'));

        return view('mobile-entry.index', [
            'school' => $school,
            'lanes' => range(1, $school->lane_count),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $term = $request->validate([
            'term' => ['required', 'string', 'min:2'],
        ])['term'];

        $students = Student::query()
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('powerschool_id', 'like', "%{$term}%");
            })
            ->with('authorizedPickups.driver')
            ->limit(10)
            ->get()
            ->map(fn ($student) => [
                'id' => $student->id,
                'name' => $student->name,
                'grade' => $student->grade,
                'drivers' => $student->authorizedPickups->map(fn ($pickup) => [
                    'id' => $pickup->driver_id,
                    'name' => $pickup->driver?->name,
                    'tag_uid' => $pickup->driver?->tag_uid,
                ]),
            ]);

        $drivers = Driver::query()
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
        ]);

        $driver = Driver::findOrFail($data['driver_id']);
        $school = $driver->school;

        $checkin = $this->queueService->manualCheckin($school, $driver, $data['lane']);

        if (!empty($data['student_ids'])) {
            foreach ($data['student_ids'] as $studentId) {
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
            'student_ids' => $data['student_ids'] ?? [],
            'lane' => $data['lane'],
            'checkin_id' => $checkin->id,
        ]);

        return response()->json([
            'message' => 'Manual checkin created.',
            'checkin_id' => $checkin->id,
        ]);
    }
}

