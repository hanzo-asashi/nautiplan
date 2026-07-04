<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use Illuminate\Support\Facades\DB;

class CreateActivityAction
{
    /**
     * Create a new activity along with its sub-activities and indicators.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Activity
    {
        return DB::transaction(function () use ($data) {
            $activity = Activity::create([
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
                'progress_percentage' => 0,
                'location' => $data['location'] ?? null,
            ]);

            if (! empty($data['sub_activities'])) {
                foreach ($data['sub_activities'] as $sub) {
                    $activity->subActivities()->create($sub);
                }
            }

            if (! empty($data['indicators'])) {
                foreach ($data['indicators'] as $ind) {
                    $activity->indicators()->create($ind);
                }
            }

            return $activity;
        });
    }
}
