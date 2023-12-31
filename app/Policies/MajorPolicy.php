<?php

namespace App\Policies;

use App\Models\Major;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MajorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role !== User::ROLE_STUDENT;
    }

}
