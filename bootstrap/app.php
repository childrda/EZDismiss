<?php

use App\Http\Middleware\DistrictAdminOnly;
use App\Http\Middleware\ScopeSchoolData;
use App\Http\Middleware\SchoolAdminOnly;
use App\Http\Middleware\StaffOnly;
use App\Http\Middleware\TeacherOnly;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'scope.school' => ScopeSchoolData::class,
            'district.admin' => DistrictAdminOnly::class,
            'school.admin' => SchoolAdminOnly::class,
            'teacher' => TeacherOnly::class,
            'staff' => StaffOnly::class,
        ]);

        $middleware->appendToGroup('web', 'scope.school');
        $middleware->appendToGroup('api', 'scope.school');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
