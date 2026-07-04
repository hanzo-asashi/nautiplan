<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use App\Models\User;

class GetActivityKanbanDataAction
{
    /**
     * Get data required for the kanban activity page.
     *
     * @return array<string, mixed>
     */
    public function execute(Activity $activity): array
    {
        $activity->load([
            'unit',
            'fiscalYear',
            'subActivities.assignedUser',
        ]);

        $users = User::get(['id', 'name']);

        return [
            'activity' => $activity,
            'users' => $users,
        ];
    }
}
