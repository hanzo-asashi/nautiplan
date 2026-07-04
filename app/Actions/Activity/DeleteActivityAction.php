<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use Illuminate\Support\Facades\DB;

class DeleteActivityAction
{
    /**
     * Delete an activity.
     */
    public function execute(Activity $activity): ?bool
    {
        return DB::transaction(function () use ($activity) {
            return $activity->delete();
        });
    }
}
