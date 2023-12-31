<?php

namespace App\Policies;

use App\Models\ClassStudent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClassStudentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role !== User::ROLE_STUDENT;
    }

}
