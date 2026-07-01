<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityReportController extends Controller
{
    public function index(): Response
    {
        $activities = Activity::with(['unit', 'reports'])->get()->map(function ($activity) {
            $quarterStatuses = [];
            foreach (['Q1', 'Q2', 'Q3', 'Q4'] as $q) {
                $report = $activity->reports->firstWhere('quarter', $q);
                $quarterStatuses[$q] = $report ? $report->status : 'none';
            }

            return [
                'id' => $activity->id,
                'code' => $activity->code,
                'name' => $activity->name,
                'unit_name' => $activity->unit->name ?? 'Unknown',
                'quarters' => $quarterStatuses,
            ];
        });

        return Inertia::render('monitoring/reports/Index', [
            'activities' => $activities,
        ]);
    }

    public function show(Activity $activity, string $quarter): Response
    {
        if (! in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'])) {
            abort(404, 'Invalid quarter.');
        }

        // Get indicator targets filtered by the selected quarter
        $indicators = $activity->indicators()
            ->where('quarter', $quarter)
            ->get()
            ->map(function ($ind) {
                return [
                    'id' => $ind->id,
                    'code' => $ind->code,
                    'name' => $ind->name,
                    'target_value' => (float) $ind->target_value,
                    'actual_value' => $ind->actual_value !== null ? (float) $ind->actual_value : null,
                    'unit_of_measure' => $ind->unit_of_measure,
                    'achievement' => $ind->achievement_percentage,
                ];
            });

        // Get existing report or default details
        $report = ActivityReport::with(['submittedBy', 'reviewedBy'])
            ->where('activity_id', $activity->id)
            ->where('quarter', $quarter)
            ->first();

        // Sub-activities progress
        $subActivities = $activity->subActivities()
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'name' => $sub->name,
                    'progress_percentage' => (int) $sub->progress_percentage,
                ];
            });

        $user = auth()->user();
        $isAdmin = $user->isSuperAdmin() || $user->hasRole('admin');

        return Inertia::render('monitoring/reports/Show', [
            'activity' => [
                'id' => $activity->id,
                'code' => $activity->code,
                'name' => $activity->name,
                'description' => $activity->description,
                'unit_name' => $activity->unit->name ?? 'Unknown',
                'total_budget' => $activity->total_budget,
                'total_realized' => $activity->total_realized,
                'progress_percentage' => (int) $activity->progress_percentage,
            ],
            'quarter' => $quarter,
            'indicators' => $indicators,
            'sub_activities' => $subActivities,
            'report' => $report ? [
                'id' => $report->id,
                'status' => $report->status,
                'progress_description' => $report->progress_description,
                'obstacles' => $report->obstacles,
                'solutions' => $report->solutions,
                'submitted_by_name' => $report->submittedBy->name ?? null,
                'submitted_at' => $report->submitted_at ? $report->submitted_at->format('d M Y H:i') : null,
                'evaluation_score' => $report->evaluation_score,
                'evaluation_notes' => $report->evaluation_notes,
                'recommendations' => $report->recommendations,
                'reviewed_by_name' => $report->reviewedBy->name ?? null,
                'reviewed_at' => $report->reviewed_at ? $report->reviewed_at->format('d M Y H:i') : null,
            ] : null,
            'auth_is_admin' => $isAdmin,
        ]);
    }

    public function storeOrUpdate(Request $request, Activity $activity, string $quarter): RedirectResponse
    {
        if (! in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'])) {
            abort(404, 'Invalid quarter.');
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,submitted',
            'progress_description' => 'nullable|string',
            'obstacles' => 'nullable|string',
            'solutions' => 'nullable|string',
        ]);

        if ($validated['status'] === 'submitted') {
            $request->validate([
                'progress_description' => 'required|string',
                'obstacles' => 'required|string',
                'solutions' => 'required|string',
            ]);
        }

        $report = ActivityReport::where('activity_id', $activity->id)
            ->where('quarter', $quarter)
            ->firstOrNew([
                'activity_id' => $activity->id,
                'quarter' => $quarter,
            ]);

        // If already reviewed, do not allow changes by operator
        if ($report->status === 'reviewed') {
            abort(403, 'Laporan yang telah dievaluasi tidak dapat diubah.');
        }

        $report->fill([
            'status' => $validated['status'],
            'progress_description' => $validated['progress_description'] ?? null,
            'obstacles' => $validated['obstacles'] ?? null,
            'solutions' => $validated['solutions'] ?? null,
        ]);

        if ($validated['status'] === 'submitted') {
            $report->submitted_at = now();
            $report->submitted_by = (int) auth()->id();
        }

        $report->save();

        $message = $validated['status'] === 'submitted'
            ? 'Laporan kinerja berhasil dikirim.'
            : 'Draft laporan kinerja berhasil disimpan.';

        return redirect()
            ->route('monitoring.reports.show', ['activity' => $activity->id, 'quarter' => $quarter])
            ->with('success', $message);
    }

    public function evaluate(Request $request, Activity $activity, string $quarter): RedirectResponse
    {
        if (! in_array($quarter, ['Q1', 'Q2', 'Q3', 'Q4'])) {
            abort(404, 'Invalid quarter.');
        }

        $user = auth()->user();
        if (! $user->isSuperAdmin() && ! $user->hasRole('admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'evaluation_score' => 'required|integer|min:1|max:100',
            'evaluation_notes' => 'required|string',
            'recommendations' => 'required|string',
        ]);

        $report = ActivityReport::where('activity_id', $activity->id)
            ->where('quarter', $quarter)
            ->first();

        if (! $report) {
            abort(400, 'Laporan harus dibuat dan dikirim terlebih dahulu sebelum dievaluasi.');
        }

        $report->update([
            'status' => 'reviewed',
            'evaluation_score' => $validated['evaluation_score'],
            'evaluation_notes' => $validated['evaluation_notes'],
            'recommendations' => $validated['recommendations'],
            'reviewed_by' => (int) auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('monitoring.reports.show', ['activity' => $activity->id, 'quarter' => $quarter])
            ->with('success', 'Evaluasi Monev berhasil dikirim.');
    }
}
