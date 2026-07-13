<?php

namespace App\Http\Controllers;

use App\Actions\Budget\CreateBudgetAction;
use App\Actions\Budget\StoreRealizationAction;
use App\Actions\Budget\UpdateBudgetAction;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Http\Requests\Budget\StoreRealizationRequest;
use App\Http\Requests\Budget\UpdateBudgetRequest;
use App\Models\ActivityBudget;
use App\Models\BudgetItem;
use App\Models\BudgetRealization;
use App\Models\BudgetRevision;
use App\Models\BudgetRevisionDetail;
use App\Models\FiscalYear;
use App\Models\RealizationItem;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function transferBudget(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'source_budget_item_id' => 'required|exists:budget_items,id',
            'destination_budget_item_id' => 'required|exists:budget_items,id|different:source_budget_item_id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        $amount = (float) $validated['amount'];

        /** @var BudgetItem $sourceItem */
        $sourceItem = BudgetItem::findOrFail($validated['source_budget_item_id']);

        /** @var BudgetItem $destItem */
        $destItem = BudgetItem::findOrFail($validated['destination_budget_item_id']);

        // Check source item remaining amount (pagu - realizations)
        $realizedVolume = RealizationItem::where('budget_item_id', $sourceItem->id)->sum('volume');
        $realizedTotal = $realizedVolume * $sourceItem->unit_price;
        $availableAmount = $sourceItem->total - $realizedTotal;

        if ($amount > $availableAmount) {
            return back()->with('error', 'Jumlah pemindahan dana melebihi sisa dana yang tersedia pada item sumber.');
        }

        DB::transaction(function () use ($sourceItem, $destItem, $amount, $validated) {
            /** @var ActivityBudget $sourceBudget */
            $sourceBudget = $sourceItem->activityBudget;

            /** @var ActivityBudget $destBudget */
            $destBudget = $destItem->activityBudget;

            // 1. Create Revision for Source Budget
            $sourceRev = BudgetRevision::create([
                'activity_budget_id' => $sourceBudget->id,
                'revision_number' => $sourceBudget->version,
                'description' => "Pemindahan dana ke [{$destBudget->account_code}] {$destItem->name}: {$validated['reason']}",
                'amount_semula' => $sourceBudget->amount,
                'amount_menjadi' => $sourceBudget->amount - $amount,
                'revised_by' => Auth::id(),
            ]);

            BudgetRevisionDetail::create([
                'budget_revision_id' => $sourceRev->id,
                'budget_item_id' => $sourceItem->id,
                'name_semula' => $sourceItem->name,
                'volume_semula' => $sourceItem->volume,
                'unit_semula' => $sourceItem->unit,
                'unit_price_semula' => $sourceItem->unit_price,
                'total_semula' => $sourceItem->total,
                'name_menjadi' => $sourceItem->name,
                'volume_menjadi' => ($sourceItem->total - $amount) / $sourceItem->unit_price,
                'unit_menjadi' => $sourceItem->unit,
                'unit_price_menjadi' => $sourceItem->unit_price,
                'total_menjadi' => $sourceItem->total - $amount,
            ]);

            // Deduct from source item
            $sourceItem->decrement('total', $amount);
            $sourceItem->update(['volume' => $sourceItem->total / $sourceItem->unit_price]);
            $sourceBudget->decrement('amount', $amount);
            $sourceBudget->increment('version');

            // 2. Create Revision for Destination Budget
            $destRev = BudgetRevision::create([
                'activity_budget_id' => $destBudget->id,
                'revision_number' => $destBudget->version,
                'description' => "Penerimaan pemindahan dana dari [{$sourceBudget->account_code}] {$sourceItem->name}: {$validated['reason']}",
                'amount_semula' => $destBudget->amount,
                'amount_menjadi' => $destBudget->amount + $amount,
                'revised_by' => Auth::id(),
            ]);

            BudgetRevisionDetail::create([
                'budget_revision_id' => $destRev->id,
                'budget_item_id' => $destItem->id,
                'name_semula' => $destItem->name,
                'volume_semula' => $destItem->volume,
                'unit_semula' => $destItem->unit,
                'unit_price_semula' => $destItem->unit_price,
                'total_semula' => $destItem->total,
                'name_menjadi' => $destItem->name,
                'volume_menjadi' => ($destItem->total + $amount) / $destItem->unit_price,
                'unit_menjadi' => $destItem->unit,
                'unit_price_menjadi' => $destItem->unit_price,
                'total_menjadi' => $destItem->total + $amount,
            ]);

            // Add to destination item
            $destItem->increment('total', $amount);
            $destItem->update(['volume' => $destItem->total / $destItem->unit_price]);
            $destBudget->increment('amount', $amount);
            $destBudget->increment('version');
        });

        return back()->with('success', 'Dana alokasi anggaran berhasil dipindahkan.');
    }
}
