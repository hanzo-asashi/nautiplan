<?php

namespace App\Actions\Report;

use App\Models\Activity;
use App\Models\FiscalYear;
use App\Models\Unit;
use Illuminate\Http\Request;

class GetCalendarDataAction
{
    /**
     * Get data required for the Calendar report.
     *
     * @return array<string, mixed>
     */
    public function execute(Request $request): array
    {
        $selectedYearId = $request->input('fiscal_year_id') ?: FiscalYear::where('is_active', true)->value('id') ?: FiscalYear::value('id');
        $selectedUnitId = $request->input('unit_id');
        $selectedStatus = $request->input('status');

        $query = Activity::with(['unit', 'subActivities.assignedUser'])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date');

        if ($selectedYearId) {
            $query->where('fiscal_year_id', $selectedYearId);
        }
        if ($selectedUnitId) {
            $query->where('unit_id', $selectedUnitId);
        }
        if ($selectedStatus) {
            $query->where('status', $selectedStatus);
        }

        $activities = $query->get()->map(fn ($act) => [
            'id' => $act->id,
            'code' => $act->code,
            'name' => $act->name,
            'status' => $act->status,
            'priority' => $act->priority,
            'start_date' => $act->start_date ? $act->start_date->format('Y-m-d') : null,
            'end_date' => $act->end_date ? $act->end_date->format('Y-m-d') : null,
            'unit_name' => $act->unit->name,
            'progress_percentage' => $act->progress_percentage,
            'sub_activities' => $act->subActivities->map(fn ($sub) => [
                'id' => $sub->id,
                'name' => $sub->name,
                'status' => $sub->status,
                'start_date' => $sub->start_date ? $sub->start_date->format('Y-m-d') : null,
                'end_date' => $sub->end_date ? $sub->end_date->format('Y-m-d') : null,
                'progress_percentage' => $sub->progress_percentage,
                'assigned_user_name' => $sub->assignedUser?->name,
            ])->all(),
        ]);

        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year', 'is_active']);
        $units = Unit::orderBy('name')->get(['id', 'name', 'code']);

        return [
            'activities' => $activities,
            'fiscalYears' => $fiscalYears,
            'units' => $units,
            'filters' => [
                'fiscal_year_id' => (int) $selectedYearId,
                'unit_id' => $selectedUnitId ? (int) $selectedUnitId : null,
                'status' => $selectedStatus,
            ],
        ];
    }
}
