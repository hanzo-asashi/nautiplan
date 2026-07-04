<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Get global roles that bypass unit restrictions.
     */
    private function hasGlobalRole(User $user): bool
    {
        $globalRoles = ['super-admin', 'admin', 'direktur', 'wakil-direktur', 'auditor', 'staf-keuangan', 'staf-perencanaan'];

        return $user->isSuperAdmin() || $user->hasAnyRole(...$globalRoles);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Activity $activity): bool
    {
        if ($this->hasGlobalRole($user)) {
            return true;
        }

        return $user->unit_id === $activity->unit_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Activity $activity): bool
    {
        if ($this->hasGlobalRole($user)) {
            return true;
        }

        return $user->unit_id === $activity->unit_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Activity $activity): bool
    {
        if ($this->hasGlobalRole($user)) {
            return true;
        }

        return $user->unit_id === $activity->unit_id;
    }
}
