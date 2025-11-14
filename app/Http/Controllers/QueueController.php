<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\School;
use App\Services\QueueService;
use App\Support\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QueueController extends Controller
{
    public function index(Request $request, QueueService $queueService): View|JsonResponse
    {
        $school = $this->resolveSchool($request);

        $lanes = [];

        for ($lane = 1; $lane <= $school->lane_count; $lane++) {
            $checkins = Checkin::with(['driver', 'calls.student'])
                ->where('school_id', $school->id)
                ->where('lane', $lane)
                ->orderBy('position')
                ->get()
                ->map(function (Checkin $checkin) use ($queueService) {
                    $status = $checkin->calls->first()?->status ?? 'queued';

                    return [
                        'id' => $checkin->id,
                        'driver' => $checkin->driver?->name,
                        'students' => $checkin->calls
                            ->map(fn ($call) => [
                                'id' => $call->id, // Call ID for marking as released
                                'student_id' => $call->student_id,
                                'name' => $call->student?->name,
                                'status' => $call->status,
                            ])
                            ->filter(fn ($call) => $call['name'] !== null)
                            ->values()
                            ->all(),
                        'status' => $status,
                        'position' => $checkin->position,
                        'color' => $queueService->positionColor($checkin->position, $status),
                    ];
                })
                ->values()
                ->all();

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

        if ($request->user()->school) {
            return $request->user()->school;
        }

        return School::findOrFail(Tenant::schoolId());
    }
}

