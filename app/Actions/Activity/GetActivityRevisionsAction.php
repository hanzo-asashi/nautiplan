<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use App\Models\AuditLog;

class GetActivityRevisionsAction
{
    /**
     * Get revisions/audit logs for an activity, its budgets, and sub-activities.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, AuditLog>
     */
    public function execute(Activity $activity): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $budgetIds = $activity->budgets()->pluck('id')->toArray();
        $subActivityIds = $activity->subActivities()->pluck('id')->toArray();

        return AuditLog::with('user')
            ->where(function ($q) use ($activity) {
                $q->where('auditable_type', Activity::class)
                    ->where('auditable_id', $activity->id);
            })
            ->orWhere(function ($q) use ($budgetIds) {
                if (! empty($budgetIds)) {
                    $q->where('auditable_type', 'App\Models\ActivityBudget')
                        ->whereIn('auditable_id', $budgetIds);
                } else {
                    $q->whereRaw('1=0');
                }
            })
            ->orWhere(function ($q) use ($subActivityIds) {
                if (! empty($subActivityIds)) {
                    $q->where('auditable_type', 'App\Models\SubActivity')
                        ->whereIn('auditable_id', $subActivityIds);
                } else {
                    $q->whereRaw('1=0');
                }
            })
            ->latest()
            ->paginate(30);
    }
}
