<?php

namespace App\Http\Controllers;

use App\Models\ActivityIndicator;
use Inertia\Inertia;
use Inertia\Response;

class KpiDashboardController extends Controller
{
    public function index(): Response
    {
        $indicators = ActivityIndicator::with(['activity.unit'])->get();

        $totalIndicators = $indicators->count();
        $totalIku = $indicators->where('indicator_type', 'iku')->count();
        $totalIkk = $indicators->where('indicator_type', 'ikk')->count();

        $achievedCount = 0;
        $inProgressCount = 0;
        $noProgressCount = 0;
        $totalAchievement = 0.0;

        foreach ($indicators as $ind) {
            $target = (float) $ind->target_value;
            $actual = (float) ($ind->actual_value ?? 0);

            if ($target === 0.0) {
                $pct = 100.0;
                $achievedCount++;
            } else {
                $pct = ($actual / $target) * 100;
                if ($actual >= $target) {
                    $achievedCount++;
                } elseif ($actual > 0) {
                    $inProgressCount++;
                } else {
                    $noProgressCount++;
                }
            }

            $totalAchievement += min(100.0, $pct);
        }

        $averageAchievement = $totalIndicators > 0 ? round($totalAchievement / $totalIndicators, 2) : 0;

        // Quarters stats
        $quarters = ['annual', 'Q1', 'Q2', 'Q3', 'Q4'];
        $quarterStats = [];
        foreach ($quarters as $q) {
            $qIndicators = $indicators->where('quarter', $q);
            $qTotal = $qIndicators->count();
            $qSum = 0.0;
            foreach ($qIndicators as $ind) {
                $target = (float) $ind->target_value;
                $actual = (float) ($ind->actual_value ?? 0);
                $pct = $target > 0 ? ($actual / $target) * 100 : 100.0;
                $qSum += min(100.0, $pct);
            }
            $quarterStats[$q] = [
                'total' => $qTotal,
                'average' => $qTotal > 0 ? round($qSum / $qTotal, 2) : 0,
            ];
        }

        // Unit stats
        $unitStats = [];
        $groupedByUnit = $indicators->groupBy(fn ($ind) => $ind->activity->unit->name ?? 'Unknown');
        foreach ($groupedByUnit as $unitName => $uIndicators) {
            $uTotal = $uIndicators->count();
            $uAchieved = 0;
            $uSum = 0.0;
            foreach ($uIndicators as $ind) {
                $target = (float) $ind->target_value;
                $actual = (float) ($ind->actual_value ?? 0);
                if ($target === 0.0) {
                    $pct = 100.0;
                    $uAchieved++;
                } else {
                    $pct = ($actual / $target) * 100;
                    if ($actual >= $target) {
                        $uAchieved++;
                    }
                }
                $uSum += min(100.0, $pct);
            }
            $unitStats[] = [
                'unit_name' => $unitName,
                'total' => $uTotal,
                'achieved' => $uAchieved,
                'average' => $uTotal > 0 ? round($uSum / $uTotal, 2) : 0,
            ];
        }

        // Sort unit stats by average descending
        usort($unitStats, fn ($a, $b) => $b['average'] <=> $a['average']);

        $indicatorList = $indicators->map(fn ($ind) => [
            'id' => $ind->id,
            'code' => $ind->code,
            'name' => $ind->name,
            'indicator_type' => $ind->indicator_type,
            'target_value' => (float) $ind->target_value,
            'actual_value' => $ind->actual_value !== null ? (float) $ind->actual_value : null,
            'unit_of_measure' => $ind->unit_of_measure,
            'quarter' => $ind->quarter,
            'achievement' => $ind->achievement_percentage,
            'activity_name' => $ind->activity->name ?? 'Unknown',
            'unit_name' => $ind->activity->unit->name ?? 'Unknown',
        ]);

        return Inertia::render('monitoring/KpiDashboard', [
            'stats' => [
                'total_indicators' => $totalIndicators,
                'total_iku' => $totalIku,
                'total_ikk' => $totalIkk,
                'achieved_count' => $achievedCount,
                'in_progress_count' => $inProgressCount,
                'no_progress_count' => $noProgressCount,
                'average_achievement' => (float) $averageAchievement,
            ],
            'quarter_stats' => $quarterStats,
            'unit_stats' => $unitStats,
            'indicators' => $indicatorList,
        ]);
    }
}
