<?php

namespace App\Actions\Activity;

use App\Models\Activity;
use App\Models\FiscalYear;
use App\Models\Unit;
use Illuminate\Http\Request;

class GetActivitiesAction
{
    /**
     * Get paginated activities and required filter data.
     *
     * @return array<string, mixed>
     */
    public function execute(Request $request): array
    {
        $query = Activity::with(['program', 'unit', 'fiscalYear', 'responsibleUser'])
            ->filter($request->all());

        $activities = $query->latest()->paginate(10)->withQueryString();
        $units = Unit::get(['id', 'name', 'code']);
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year']);

        return [
            'activities' => $activities,
            'units' => $units,
            'fiscalYears' => $fiscalYears,
            'filters' => $request->only(['search', 'unit_id', 'fiscal_year_id', 'status']),
        ];
    }
}
