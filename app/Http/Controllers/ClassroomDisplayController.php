<?php

namespace App\Http\Controllers;

use App\Models\Homeroom;
use App\Models\School;
use App\Services\QueueService;
use App\Support\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassroomDisplayController extends Controller
{
    public function show(Request $request, Homeroom $homeroom, QueueService $queueService): View
    {
        $homeroom->load('students.calls.checkin');

        $students = $homeroom->students->map(function ($student) use ($queueService) {
            $call = $student->calls->sortByDesc('created_at')->first();

            $position = $call?->checkin?->position;
            $status = $call?->status ?? 'waiting';

            $color = $position
                ? $queueService->positionColor($position, $status)
                : 'gray';

            $indicator = match ($color) {
                'green' => 'Send Now',
                'yellow' => 'Get Ready',
                default => 'Waiting',
            };

            return [
                'id' => $student->id,
                'name' => $student->name,
                'position' => $position,
                'lane' => $call?->checkin?->lane,
                'status' => $status,
                'indicator' => $indicator,
            ];
        });

        $canViewQueue = !$request->user()->isTeacher() || $request->boolean('full');

        return view('classroom.show', [
            'homeroom' => $homeroom,
            'students' => $students,
            'canViewQueue' => $canViewQueue,
        ]);
    }
}

