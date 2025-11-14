<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GymDisplayController extends Controller
{
    public function __invoke(Request $request): View
    {
        $school = $this->resolveSchool($request);

        $lanes = Checkin::with(['driver', 'calls.student'])
            ->where('school_id', $school->id)
            ->orderBy('lane')
            ->orderBy('position')
            ->get()
            ->groupBy('lane')
            ->map(fn ($lane) => $lane->map(function ($checkin) {
                return [
                    'driver' => $checkin->driver?->name,
                    'students' => $checkin->calls->map(fn ($call) => $call->student?->name)->filter()->values(),
                    'position' => $checkin->position,
                    'status' => $checkin->calls->first()?->status ?? 'queued',
                ];
            }));

        return view('gym.index', [
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

        if ($request->user()?->school) {
            return $request->user()->school;
        }

        if ($request->filled('school_id')) {
            return School::findOrFail($request->integer('school_id'));
        }

        return School::query()->firstOrFail();
    }
}

