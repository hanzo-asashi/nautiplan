<?php

namespace App\Actions\Report;

use App\Models\Activity;
use App\Models\FiscalYear;
use App\Models\Unit;
use Illuminate\Http\Request;

class GetGanttChartDataAction
{
    /**
     * Get data required for the Gantt Chart report.
     *
     * @return array<string, mixed>
     */
    public function execute(Request $request): array
    {
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year', 'is_active']);
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();

        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);
        $selectedUnitId = $request->input('unit_id');

        $query = Activity::query()
            ->with(['unit', 'subActivities.assignedUser'])
            ->where('fiscal_year_id', $selectedYearId)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        if ($selectedUnitId) {
            $query->where('unit_id', $selectedUnitId);
        }

        $activities = $query->get()->map(function ($activity) {
            return [
                'id' => $activity->id,
                'code' => $activity->code,
                'name' => $activity->name,
                'status' => $activity->status,
                'start_date' => $activity->start_date ? $activity->start_date->format('Y-m-d') : null,
                'end_date' => $activity->end_date ? $activity->end_date->format('Y-m-d') : null,
                'progress_percentage' => $activity->progress_percentage,
                'unit_name' => $activity->unit?->name,
                'sub_activities' => $activity->subActivities->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'name' => $sub->name,
                        'status' => $sub->status,
                        'start_date' => $sub->start_date ? $sub->start_date->format('Y-m-d') : null,
                        'end_date' => $sub->end_date ? $sub->end_date->format('Y-m-d') : null,
                        'progress_percentage' => $sub->progress_percentage,
                        'assigned_user_name' => $sub->assignedUser?->name,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $units = Unit::get(['id', 'name', 'code']);

        return [
            'activities' => $activities,
            'fiscalYears' => $fiscalYears,
            'units' => $units,
            'filters' => [
                'fiscal_year_id' => $selectedYearId ? (int) $selectedYearId : null,
                'unit_id' => $selectedUnitId ? (int) $selectedUnitId : null,
            ],
        ];
    }
}
