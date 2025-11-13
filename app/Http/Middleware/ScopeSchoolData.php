<?php

namespace App\Http\Middleware;

use App\Support\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeSchoolData
{
    public function handle(Request $request, Closure $next): Response
    {
        Tenant::setFromAuth();

        return $next($request);
    }
}

