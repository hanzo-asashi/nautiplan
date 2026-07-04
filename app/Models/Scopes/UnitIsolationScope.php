<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UnitIsolationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::hasUser()) {
            $user = Auth::user();

            // Allow super admin, admin, and central verifier roles to see everything
            $globalRoles = ['super-admin', 'admin', 'direktur', 'wakil-direktur', 'auditor', 'staf-keuangan', 'staf-perencanaan'];

            if ($user->isSuperAdmin() || $user->hasAnyRole(...$globalRoles)) {
                return;
            }

            if ($user->unit_id) {
                // Determine how to filter based on model
                $table = $model->getTable();

                if ($table === 'activities') {
                    $builder->where($table.'.unit_id', $user->unit_id);
                } elseif (in_array($table, ['activity_budgets', 'activity_documents', 'sub_activities', 'activity_indicators'])) {
                    $builder->whereHas('activity', function ($query) use ($user) {
                        $query->where('unit_id', $user->unit_id);
                    });
                } elseif (in_array($table, ['budget_realizations', 'budget_revisions'])) {
                    $builder->whereHas('activityBudget.activity', function ($query) use ($user) {
                        $query->where('unit_id', $user->unit_id);
                    });
                }
            }
        }
    }
}
