<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class DriverPolicy
{
    use HandlesSchoolAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSchoolAdmin() || $user->isStaff();
    }

    public function view(User $user, Driver $driver): bool
    {
        return $this->sameSchool($user, $driver->school_id);
    }

    public function create(User $user): bool
    {
        return $user->isSchoolAdmin();
    }

    public function update(User $user, Driver $driver): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $driver->school_id);
    }

    public function delete(User $user, Driver $driver): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $driver->school_id);
    }
}

