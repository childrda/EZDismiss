<?php

namespace App\Policies;

use App\Models\AuthorizedPickup;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class AuthorizedPickupPolicy
{
    use HandlesSchoolAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSchoolAdmin() || $user->isStaff();
    }

    public function view(User $user, AuthorizedPickup $authorizedPickup): bool
    {
        return $this->sameSchool($user, $authorizedPickup->school_id);
    }

    public function create(User $user): bool
    {
        return $user->isSchoolAdmin() || $user->isStaff();
    }

    public function update(User $user, AuthorizedPickup $authorizedPickup): bool
    {
        return ($user->isSchoolAdmin() || $user->isStaff())
            && $this->sameSchool($user, $authorizedPickup->school_id);
    }

    public function delete(User $user, AuthorizedPickup $authorizedPickup): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $authorizedPickup->school_id);
    }
}

