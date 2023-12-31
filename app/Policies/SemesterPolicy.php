<?php

namespace App\Policies;

use App\Models\Semester;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SemesterPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role !== User::ROLE_STUDENT;
    }

    /**
     * Determine whether the user can view the model.
     */
}
