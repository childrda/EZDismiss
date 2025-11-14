<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSchoolContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if school is in route parameters (district admin accessing school data)
        if ($request->route()->hasParameter('school')) {
            $school = $request->route()->parameter('school');
            
            if ($school instanceof School) {
                Tenant::set($school->id);
            } elseif (is_numeric($school)) {
                $schoolModel = School::find($school);
                if ($schoolModel) {
                    Tenant::set($schoolModel->id);
                }
            }
        }

        return $next($request);
    }
}

