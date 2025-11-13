<?php

namespace App\Policies;

use App\Models\RfidReader;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class RfidReaderPolicy
{
    use HandlesSchoolAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSchoolAdmin();
    }

    public function view(User $user, RfidReader $rfidReader): bool
    {
        return $this->sameSchool($user, $rfidReader->school_id);
    }

    public function create(User $user): bool
    {
        return $user->isSchoolAdmin();
    }

    public function update(User $user, RfidReader $rfidReader): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $rfidReader->school_id);
    }

    public function delete(User $user, RfidReader $rfidReader): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $rfidReader->school_id);
    }
}

