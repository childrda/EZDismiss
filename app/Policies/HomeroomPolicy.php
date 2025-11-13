<?php

namespace App\Policies;

use App\Models\Homeroom;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class HomeroomPolicy
{
    use HandlesSchoolAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSchoolAdmin() || $user->isTeacher();
    }

    public function view(User $user, Homeroom $homeroom): bool
    {
        return $this->sameSchool($user, $homeroom->school_id);
    }

    public function create(User $user): bool
    {
        return $user->isSchoolAdmin();
    }

    public function update(User $user, Homeroom $homeroom): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $homeroom->school_id);
    }

    public function delete(User $user, Homeroom $homeroom): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $homeroom->school_id);
    }
}

