<?php

namespace App\Http\Controllers\DistrictAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Driver;
use App\Models\School;
use App\Models\Student;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('district.dashboard', [
            'schoolCount' => School::count(),
            'studentCount' => Student::count(),
            'driverCount' => Driver::count(),
            'recentLogs' => ActivityLog::latest()->limit(25)->get(),
        ]);
    }
}

