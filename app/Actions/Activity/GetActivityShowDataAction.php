<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use App\Models\User;

class GetActivityShowDataAction
{
    /**
     * Get data required for the show activity page.
     *
     * @return array<string, mixed>
     */
    public function execute(Activity $activity): array
    {
        $activity->load([
            'program',
            'unit',
            'fiscalYear',
            'responsibleUser',
            'subActivities.assignedUser',
            'budgets.realizations',
            'indicators',
            'documents.uploader',
            'documents.versions.uploader',
            'approvalRequest.steps.role',
            'approvalRequest.steps.approver',
        ]);

        $users = User::get(['id', 'name']);

        return [
            'activity' => $activity,
            'users' => $users,
        ];
    }
}
