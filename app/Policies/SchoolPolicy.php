<?php

namespace App\Policies;

use App\Models\School;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class SchoolPolicy
{
    use HandlesSchoolAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isDistrictAdmin();
    }

    public function view(User $user, School $school): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $school->id);
    }

    public function create(User $user): bool
    {
        return $user->isDistrictAdmin();
    }

    public function update(User $user, School $school): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $school->id);
    }

    public function delete(User $user, School $school): bool
    {
        return false;
    }
}

