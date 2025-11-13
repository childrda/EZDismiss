<?php

namespace App\Policies;

use App\Models\ExceptionCase;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class ExceptionPolicy
{
    use HandlesSchoolAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSchoolAdmin() || $user->isStaff();
    }

    public function view(User $user, ExceptionCase $exceptionCase): bool
    {
        return $this->sameSchool($user, $exceptionCase->school_id);
    }

    public function create(User $user): bool
    {
        return $user->isSchoolAdmin() || $user->isStaff();
    }

    public function update(User $user, ExceptionCase $exceptionCase): bool
    {
        return ($user->isSchoolAdmin() || $user->isStaff())
            && $this->sameSchool($user, $exceptionCase->school_id);
    }

    public function delete(User $user, ExceptionCase $exceptionCase): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $exceptionCase->school_id);
    }
}

