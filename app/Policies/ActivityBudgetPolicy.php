<?php

namespace App\Policies;

use App\Models\ActivityBudget;
use App\Models\User;

class ActivityBudgetPolicy
{
    /**
     * Get global roles that bypass unit restrictions.
     */
    private function hasGlobalRole(User $user): bool
    {
        $globalRoles = ['super-admin', 'admin', 'direktur', 'wakil-direktur', 'auditor', 'staf-keuangan', 'staf-perencanaan'];

        return $user->isSuperAdmin() || $user->hasAnyRole(...$globalRoles);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ActivityBudget $budget): bool
    {
        if ($this->hasGlobalRole($user)) {
            return true;
        }

        return $budget->activity && $user->unit_id === $budget->activity->unit_id;
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
    public function update(User $user, ActivityBudget $budget): bool
    {
        if ($this->hasGlobalRole($user)) {
            return true;
        }

        return $budget->activity && $user->unit_id === $budget->activity->unit_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ActivityBudget $budget): bool
    {
        if ($this->hasGlobalRole($user)) {
            return true;
        }

        return $budget->activity && $user->unit_id === $budget->activity->unit_id;
    }
}
