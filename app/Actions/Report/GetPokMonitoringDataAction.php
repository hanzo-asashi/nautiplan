<?php

namespace App\Actions\Report;

use App\Models\FiscalYear;
use Illuminate\Http\Request;

class GetPokMonitoringDataAction
{
    protected BuildPokTreeAction $buildPokTreeAction;

    public function __construct(BuildPokTreeAction $buildPokTreeAction)
    {
        $this->buildPokTreeAction = $buildPokTreeAction;
    }

    /**
     * Get data required for the POK Monitoring report.
     *
     * @return array<string, mixed>
     */
    public function execute(Request $request): array
    {
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year', 'is_active']);
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();

        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);

        $tree = $this->buildPokTreeAction->execute($selectedYearId ? (int) $selectedYearId : null);

        return [
            'tree' => $tree,
            'fiscalYears' => $fiscalYears,
            'filters' => [
                'fiscal_year_id' => $selectedYearId ? (int) $selectedYearId : null,
            ],
        ];
    }
}
