<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait HandlesSchoolAuthorization
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isDistrictAdmin()) {
            return true;
        }

        return null;
    }

    protected function sameSchool(User $user, ?int $schoolId): bool
    {
        return $schoolId !== null && $user->school_id === $schoolId;
    }
}

