<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use Illuminate\Support\Facades\DB;

class UpdateActivityAction
{
    /**
     * Update an activity along with its sub-activities and indicators.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(Activity $activity, array $data): Activity
    {
        return DB::transaction(function () use ($activity, $data) {
            $activity->update([
                'code' => $data['code'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'program_id' => $data['program_id'],
                'renja_id' => $data['renja_id'] ?? null,
                'unit_id' => $data['unit_id'],
                'fiscal_year_id' => $data['fiscal_year_id'],
                'responsible_user_id' => $data['responsible_user_id'] ?? null,
                'status' => $data['status'],
                'priority' => $data['priority'],
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'progress_percentage' => $data['progress_percentage'] ?? $activity->progress_percentage,
                'location' => $data['location'] ?? null,
            ]);

            // Sub Activities
            $existingSubIds = $activity->subActivities()->pluck('id')->toArray();
            $requestSubIds = [];

            if (! empty($data['sub_activities'])) {
                foreach ($data['sub_activities'] as $sub) {
                    if (isset($sub['id'])) {
                        $activity->subActivities()->where('id', $sub['id'])->update($sub);
                        $requestSubIds[] = $sub['id'];
                    } else {
                        $newSub = $activity->subActivities()->create($sub);
                        $requestSubIds[] = $newSub->id;
                    }
                }
            }
            $activity->subActivities()->whereIn('id', array_diff($existingSubIds, $requestSubIds))->delete();

            // Indicators
            $existingIndIds = $activity->indicators()->pluck('id')->toArray();
            $requestIndIds = [];

            if (! empty($data['indicators'])) {
                foreach ($data['indicators'] as $ind) {
                    if (isset($ind['id'])) {
                        $activity->indicators()->where('id', $ind['id'])->update($ind);
                        $requestIndIds[] = $ind['id'];
                    } else {
                        $newInd = $activity->indicators()->create($ind);
                        $requestIndIds[] = $newInd->id;
                    }
                }
            }
            $activity->indicators()->whereIn('id', array_diff($existingIndIds, $requestIndIds))->delete();

            return $activity;
        });
    }
}
