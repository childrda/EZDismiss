<?php

namespace App\Http\Controllers\DistrictAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $logs = ActivityLog::query()
            ->when($request->get('school_id'), fn ($q, $schoolId) => $q->where('school_id', $schoolId))
            ->latest()
            ->paginate(50);

        return view('district.logs.index', [
            'logs' => $logs,
        ]);
    }
}

