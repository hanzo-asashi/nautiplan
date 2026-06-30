<?php

namespace App\Http\Controllers;

use App\Models\ActivityBudget;
use App\Models\BudgetRealization;
use App\Models\Program;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // 1. Total Programs
        $totalPrograms = Program::count();

        // 2. Total Pagu (Total Budget Ceiling)
        $totalPagu = (float) ActivityBudget::sum('amount');

        // 3. Total Realisasi (Total Expenditures Realized)
        $totalRealisasi = (float) BudgetRealization::sum('amount');

        // 4. Sisa Anggaran (Remaining)
        $sisaAnggaran = $totalPagu - $totalRealisasi;

        // 5. Percentage Realisasi
        $persenRealisasi = $totalPagu > 0 ? round(($totalRealisasi / $totalPagu) * 100, 2) : 0;

        // 6. Program list for the chart
        $programs = Program::with(['activities.budgets.realizations'])
            ->get()
            ->map(function ($program) {
                $pagu = $program->activities->flatMap->budgets->sum('amount');
                $realisasi = $program->activities->flatMap->budgets->flatMap->realizations->sum('amount');

                return [
                    'code' => $program->code,
                    'name' => $program->name,
                    'pagu' => (float) $pagu,
                    'realisasi' => (float) $realisasi,
                    'sisa' => (float) ($pagu - $realisasi),
                    'persen' => $pagu > 0 ? round(($realisasi / $pagu) * 100, 2) : 0,
                ];
            });

        // 7. Recent activities/realizations
        $recentRealizations = BudgetRealization::with(['activityBudget.activity.unit'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($realization) {
                return [
                    'id' => $realization->id,
                    'activity_name' => $realization->activityBudget->activity->name,
                    'unit_name' => $realization->activityBudget->activity->unit->name,
                    'amount' => (float) $realization->amount,
                    'date' => $realization->realization_date->format('d M Y'),
                    'description' => $realization->description,
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_programs' => $totalPrograms,
                'total_pagu' => $totalPagu,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $sisaAnggaran,
                'persen_realisasi' => $persenRealisasi,
            ],
            'programs' => $programs,
            'recent_realizations' => $recentRealizations,
        ]);
    }
}
