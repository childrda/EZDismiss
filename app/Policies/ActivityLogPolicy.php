<?php

namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class ActivityLogPolicy
{
    use HandlesSchoolAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isDistrictAdmin() || $user->isSchoolAdmin();
    }

    public function view(User $user, ActivityLog $activityLog): bool
    {
        return $this->sameSchool($user, $activityLog->school_id);
    }
}

