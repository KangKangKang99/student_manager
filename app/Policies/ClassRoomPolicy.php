<?php

namespace App\Policies;

use App\Models\ClassRoom;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClassRoomPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role !== User::ROLE_STUDENT;
    }
}
