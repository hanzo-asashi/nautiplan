<?php

namespace App\Http\Controllers;

use App\Actions\Budget\CreateBudgetAction;
use App\Actions\Budget\StoreRealizationAction;
use App\Actions\Budget\UpdateBudgetAction;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Http\Requests\Budget\StoreRealizationRequest;
use App\Http\Requests\Budget\UpdateBudgetRequest;
use App\Models\ActivityBudget;
use App\Models\BudgetRealization;
use App\Models\FiscalYear;
use App\Models\RealizationItem;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ActivityBudget::with([
            'activity.unit',
            'fiscalYear',
            'realizations.items',
            'realizations.procurement.vendor',
            'realizations.procurement.ppk',
            'realizations.procurement.kpa',
            'budgetItems',
            'revisions.details',
            'revisions.revisedBy',
        ]);

        if ($request->filled('unit_id')) {
            $unitId = $request->input('unit_id');
            $query->whereHas('activity', function ($q) use ($unitId) {
                $q->where('unit_id', $unitId);
            });
        }

        if ($request->filled('fiscal_year_id')) {
            $query->where('fiscal_year_id', $request->input('fiscal_year_id'));
        }

        if ($request->filled('category')) {
            $query->where('budget_category', $request->input('category'));
        }

        $budgets = $query->paginate(15)->withQueryString();

        // Calculate totals for filtering
        $totalPagu = (float) $query->sum('amount');
        $totalRealisasi = (float) BudgetRealization::whereIn(
            'activity_budget_id',
            (clone $query)->pluck('id')
        )->sum('amount');

        $units = Unit::get(['id', 'name', 'code']);
        $fiscalYears = FiscalYear::orderBy('year', 'desc')->get(['id', 'year']);
        $vendors = Vendor::orderBy('name')->get();
        $officers = User::orderBy('name')->get(['id', 'name', 'employee_id', 'rank']);

        return Inertia::render('budgets/Index', [
            'budgets' => $budgets,
            'units' => $units,
            'fiscalYears' => $fiscalYears,
            'vendors' => $vendors,
            'officers' => $officers,
            'summary' => [
                'total_pagu' => $totalPagu,
                'total_realisasi' => $totalRealisasi,
                'sisa_anggaran' => $totalPagu - $totalRealisasi,
                'persen_realisasi' => $totalPagu > 0 ? round(($totalRealisasi / $totalPagu) * 100, 2) : 0,
            ],
            'filters' => $request->only(['unit_id', 'fiscal_year_id', 'category']),
        ]);
    }

    public function storeBudget(StoreBudgetRequest $request, CreateBudgetAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return back()->with('success', 'Pagu anggaran berhasil ditambahkan.');
    }

    public function editBudget(ActivityBudget $budget): Response|RedirectResponse
    {
        Gate::authorize('update', $budget);

        if ($budget->fiscalYear->is_locked) {
            return back()->with('error', 'Tahun anggaran sudah dikunci.');
        }

        $budget->load([
            'activity.unit',
            'activity.program',
            'budgetItems',
            'revisions.revisedBy',
            'revisions.details',
            'fiscalYear',
        ]);

        return Inertia::render('budgets/Edit', [
            'budget' => $budget,
        ]);
    }

    public function updateBudget(UpdateBudgetRequest $request, ActivityBudget $budget, UpdateBudgetAction $action): RedirectResponse
    {
        Gate::authorize('update', $budget);

        if ($budget->fiscalYear->is_locked) {
            return back()->with('error', 'Tahun anggaran sudah dikunci.');
        }

        $action->execute($budget, $request->validated());

        return back()->with('success', 'Pagu anggaran berhasil direvisi.');
    }

    public function deleteBudget(ActivityBudget $budget): RedirectResponse
    {
        Gate::authorize('delete', $budget);

        if ($budget->fiscalYear->is_locked) {
            return back()->with('error', 'Tahun anggaran sudah dikunci.');
        }

        if ($budget->realizations()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus pagu yang telah memiliki transaksi realisasi.');
        }

        $budget->delete();

        return back()->with('success', 'Pagu anggaran berhasil dihapus.');
    }

    public function createRealization(ActivityBudget $budget): Response|RedirectResponse
    {
        $budget->load([
            'activity.unit',
            'fiscalYear',
            'realizations.items',
            'budgetItems',
        ]);

        if ($budget->fiscalYear->is_locked) {
            return redirect()->route('budgets.index')->with('error', 'Tahun anggaran sudah dikunci.');
        }

        if ($budget->activity->status !== 'approved') {
            return redirect()->route('budgets.index')->with('error', 'Realisasi anggaran hanya dapat dicatat untuk kegiatan yang sudah disetujui.');
        }

        $budgetItems = $budget->budgetItems->map(function ($item) {
            $realizedVolume = (float) RealizationItem::where('budget_item_id', $item->id)
                ->sum('volume');

            return [
                'id' => $item->id,
                'name' => $item->name,
                'volume' => (float) $item->volume,
                'unit' => $item->unit,
                'unit_price' => (float) $item->unit_price,
                'total' => (float) $item->total,
                'realized_volume' => $realizedVolume,
                'remaining_volume' => max(0.0, (float) $item->volume - $realizedVolume),
            ];
        });

        $budget->setRelation('budgetItems', $budgetItems);

        $vendors = Vendor::orderBy('name')->get();
        $officers = User::orderBy('name')->get(['id', 'name', 'employee_id', 'rank']);

        return Inertia::render('budgets/RealizationForm', [
            'budget' => $budget,
            'vendors' => $vendors,
            'officers' => $officers,
        ]);
    }

    public function storeRealization(StoreRealizationRequest $request, StoreRealizationAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return back()->with('success', 'Realisasi anggaran berhasil dicatat.');
    }

    public function verifyRealization(BudgetRealization $realization): RedirectResponse
    {
        $realization->update([
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Realisasi anggaran berhasil diverifikasi.');
    }

    public function deleteRealization(BudgetRealization $realization): RedirectResponse
    {
        $realization->delete();

        return back()->with('success', 'Realisasi anggaran berhasil dihapus.');
    }
}
