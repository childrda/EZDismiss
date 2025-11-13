<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\AuthorizedPickup;
use App\Models\Driver;
use App\Models\ExceptionCase;
use App\Models\Homeroom;
use App\Models\RfidReader;
use App\Models\School;
use App\Models\Student;
use App\Policies\ActivityLogPolicy;
use App\Policies\AuthorizedPickupPolicy;
use App\Policies\DriverPolicy;
use App\Policies\ExceptionPolicy;
use App\Policies\HomeroomPolicy;
use App\Policies\RfidReaderPolicy;
use App\Policies\SchoolPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        ActivityLog::class => ActivityLogPolicy::class,
        AuthorizedPickup::class => AuthorizedPickupPolicy::class,
        Driver::class => DriverPolicy::class,
        ExceptionCase::class => ExceptionPolicy::class,
        Homeroom::class => HomeroomPolicy::class,
        RfidReader::class => RfidReaderPolicy::class,
        School::class => SchoolPolicy::class,
        Student::class => StudentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}

