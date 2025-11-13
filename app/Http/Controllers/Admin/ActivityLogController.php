<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function __invoke(Request $request): View
    {
        $logs = ActivityLog::latest()->paginate(50);

        return view('admin.logs.index', [
            'logs' => $logs,
        ]);
    }
}

