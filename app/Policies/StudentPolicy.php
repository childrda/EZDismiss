<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Policies\Concerns\HandlesSchoolAuthorization;

class StudentPolicy
{
    use HandlesSchoolAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSchoolAdmin() || $user->isTeacher() || $user->isStaff();
    }

    public function view(User $user, Student $student): bool
    {
        return $this->sameSchool($user, $student->school_id);
    }

    public function create(User $user): bool
    {
        return $user->isSchoolAdmin();
    }

    public function update(User $user, Student $student): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $student->school_id);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->isSchoolAdmin() && $this->sameSchool($user, $student->school_id);
    }
}

