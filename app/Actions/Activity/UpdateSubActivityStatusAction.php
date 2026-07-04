<?php

namespace App\Actions\Activity;

use App\Models\Notification;
use App\Models\SubActivity;
use Illuminate\Support\Facades\DB;

class UpdateSubActivityStatusAction
{
    /**
     * Update the status of a sub-activity and average the activity progress.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(SubActivity $subActivity, array $data): SubActivity
    {
        return DB::transaction(function () use ($subActivity, $data) {
            $subActivity->update($data);

            if ($data['status'] === 'completed') {
                $subActivity->update(['progress_percentage' => 100]);
            }

            $activity = $subActivity->activity;
            $subActivities = $activity->subActivities;
            $totalSubActivities = $subActivities->count();
            if ($totalSubActivities > 0) {
                $totalProgress = $subActivities->sum('progress_percentage');
                $averageProgress = (int) round($totalProgress / $totalSubActivities);
                $activity->update(['progress_percentage' => $averageProgress]);
            }

            if ($subActivity->assigned_to) {
                Notification::create([
                    'user_id' => $subActivity->assigned_to,
                    'title' => 'Sub-Kegiatan Diperbarui',
                    'message' => "Status sub-kegiatan '{$subActivity->name}' diubah menjadi '".ucfirst(str_replace('_', ' ', $subActivity->status))."' dengan progres {$subActivity->progress_percentage}%.",
                    'type' => 'activity',
                ]);
            }

            return $subActivity;
        });
    }
}
