<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\School;
use App\Services\QueueService;
use App\Support\Tenant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function index(Request $request, QueueService $queueService): View
    {
        $school = $this->resolveSchool($request);

        $lanes = [];

        for ($lane = 1; $lane <= $school->lane_count; $lane++) {
            $checkins = Checkin::with(['driver', 'calls.student'])
                ->where('lane', $lane)
                ->orderBy('position')
                ->get()
                ->map(function (Checkin $checkin) use ($queueService) {
                    return [
                        'id' => $checkin->id,
                        'driver' => $checkin->driver?->name,
                        'students' => $checkin->calls->map(fn ($call) => $call->student?->name)->filter()->values(),
                        'status' => $checkin->calls->first()?->status ?? 'queued',
                        'position' => $checkin->position,
                        'color' => $queueService->positionColor($checkin->position, $checkin->calls->first()?->status ?? 'queued'),
                    ];
                });

            $lanes[] = [
                'number' => $lane,
                'checkins' => $checkins,
            ];
        }

        if ($request->wantsJson()) {
            return response()->json([
                'lanes' => $lanes,
            ]);
        }

        return view('queue.index', [
            'school' => $school,
            'lanes' => $lanes,
        ]);
    }

    protected function resolveSchool(Request $request): School
    {
        if ($request->user()->isDistrictAdmin()) {
            $schoolId = $request->integer('school_id');

            if (!$schoolId) {
                $schoolId = School::query()->value('id');
            }

            return School::findOrFail($schoolId);
        }

        if ($request->user()->school) {
            return $request->user()->school;
        }

        return School::findOrFail(Tenant::schoolId());
    }
}

