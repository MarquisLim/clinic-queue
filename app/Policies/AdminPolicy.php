<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    /**
     * Determine if the user can access admin panel.
     */
    public function admin(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can manage specialties.
     */
    public function manageSpecialties(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can manage doctors.
     */
    public function manageDoctors(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine if the user can manage users.
     */
    public function manageUsers(User $user): bool
    {
        return $user->hasRole('admin');
    }
}

