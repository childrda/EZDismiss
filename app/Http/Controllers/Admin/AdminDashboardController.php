<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Driver;
use App\Models\Homeroom;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $school = $request->user()->school;

        return view('admin.dashboard', [
            'school' => $school,
            'studentCount' => Student::count(),
            'driverCount' => Driver::count(),
            'homeroomCount' => Homeroom::count(),
            'recentLogs' => ActivityLog::latest()->limit(10)->get(),
        ]);
    }
}

