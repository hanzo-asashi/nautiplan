<?php

namespace App\Actions\Report;

use App\Models\ActivityBudget;
use App\Models\BudgetRealization;
use App\Models\FiscalYear;
use App\Models\Program;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GetAnalyticsDataAction
{
    /**
     * Get data required for the Analytics report.
     *
     * @return array<string, mixed>
     */
    public function execute(Request $request): array
    {
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year', 'is_active']);
        $activeYear = FiscalYear::where('is_active', true)->first() ?? FiscalYear::orderBy('year', 'desc')->first();

        $selectedYearId = $request->input('fiscal_year_id', $activeYear?->id);

        // Budget vs Realization by Unit
        $unitsData = Unit::with([
            'activities' => function ($query) use ($selectedYearId) {
                $query->where('fiscal_year_id', $selectedYearId);
            },
            'activities.budgets.realizations',
        ])
            ->get()
            ->map(function ($unit) {
                $activities = $unit->activities;
                $pagu = $activities->flatMap->budgets->sum('amount');
                $realisasi = $activities->flatMap->budgets->flatMap->realizations->sum('amount');

                return [
                    'label' => $unit->name,
                    'value1' => (float) $pagu,
                    'value2' => (float) $realisasi,
                ];
            })
            ->filter(fn ($u) => $u['value1'] > 0 || $u['value2'] > 0)
            ->values()
            ->all();

        // Budget vs Realization by Program
        $programsData = Program::where('fiscal_year_id', $selectedYearId)
            ->with(['activities.budgets.realizations'])
            ->get()
            ->map(function ($program) {
                $pagu = $program->activities->flatMap->budgets->sum('amount');
                $realisasi = $program->activities->flatMap->budgets->flatMap->realizations->sum('amount');

                return [
                    'label' => $program->name,
                    'value1' => (float) $pagu,
                    'value2' => (float) $realisasi,
                ];
            })
            ->filter(fn ($p) => $p['value1'] > 0 || $p['value2'] > 0)
            ->values()
            ->all();

        // Multi-year comparison
        $multiYearData = FiscalYear::orderBy('year', 'asc')
            ->with(['activities.budgets.realizations', 'activities.indicators'])
            ->get()
            ->map(function ($fy) {
                $pagu = $fy->activities->flatMap->budgets->sum('amount');
                $realisasi = $fy->activities->flatMap->budgets->flatMap->realizations->sum('amount');
                $totalActivities = $fy->activities->count();

                $indicators = $fy->activities->flatMap->indicators;
                $achievementAvg = 0;
                if ($indicators->count() > 0) {
                    $totalPct = $indicators->sum(function ($ind) {
                        if (is_null($ind->actual_value) || $ind->target_value <= 0) {
                            return 0;
                        }

                        return min(100, round(($ind->actual_value / $ind->target_value) * 100));
                    });
                    $achievementAvg = round($totalPct / $indicators->count(), 1);
                }

                return [
                    'year' => $fy->year,
                    'total_activities' => $totalActivities,
                    'pagu' => (float) $pagu,
                    'realisasi' => (float) $realisasi,
                    'kpi_achievement' => $achievementAvg,
                ];
            })
            ->values()
            ->all();

        // 1. Monthly Absorption Trend
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];

        $realizationsQuery = BudgetRealization::whereHas('activityBudget', function ($q) use ($selectedYearId) {
            $q->where('fiscal_year_id', $selectedYearId);
        })->get();

        $monthlyTrend = [];
        $runningSum = 0;
        foreach ($months as $mNum => $mName) {
            $mAmount = (float) $realizationsQuery->filter(function ($r) use ($mNum) {
                return Carbon::parse($r->realization_date)->month === $mNum;
            })->sum('amount');

            $runningSum += $mAmount;

            $monthlyTrend[] = [
                'month' => $mName,
                'amount' => $mAmount,
                'cumulative' => $runningSum,
            ];
        }

        // 2. Early Warning System (Pagu Kritis)
        $criticalBudgets = ActivityBudget::where('fiscal_year_id', $selectedYearId)
            ->with(['activity.unit', 'realizations'])
            ->get()
            ->map(function ($budget) {
                $realisasi = $budget->realizations->sum('amount');
                $remaining = $budget->amount - $realisasi;
                $pct = $budget->amount > 0 ? ($realisasi / $budget->amount) * 100 : 0;

                return [
                    'id' => $budget->id,
                    'account_code' => $budget->account_code,
                    'description' => $budget->description,
                    'unit' => $budget->activity->unit->name ?? '-',
                    'pagu' => (float) $budget->amount,
                    'realisasi' => (float) $realisasi,
                    'sisa' => (float) $remaining,
                    'percentage' => round($pct, 1),
                ];
            })
            ->filter(function ($b) {
                return ($b['percentage'] >= 85.0 && $b['sisa'] > 0) || ($b['sisa'] > 0 && $b['sisa'] <= 2000000.0);
            })
            ->sortBy('sisa')
            ->values()
            ->all();

        return [
            'unitsData' => $unitsData,
            'programsData' => $programsData,
            'multiYearData' => $multiYearData,
            'monthlyTrend' => $monthlyTrend,
            'criticalBudgets' => $criticalBudgets,
            'fiscalYears' => $fiscalYears,
            'filters' => [
                'fiscal_year_id' => $selectedYearId ? (int) $selectedYearId : null,
            ],
            'importErrors' => session('importErrors'),
            'success' => session('success'),
        ];
    }
}
