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
        $school = $request->user()->school ?? School::findOrFail($request->get('school_id'));

        $lanes = Checkin::with(['driver', 'calls.student'])
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
}

