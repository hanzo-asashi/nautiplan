<?php

namespace App\Http\Controllers;

use App\Models\ActivityBudget;
use App\Models\BudgetItem;
use App\Models\BudgetRealization;
use App\Models\BudgetRevision;
use App\Models\BudgetRevisionDetail;
use App\Models\FiscalYear;
use App\Models\Notification;
use App\Models\Procurement;
use App\Models\RealizationItem;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

    public function storeBudget(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'budget_category' => 'required|string|in:personnel,goods_services,capital,other',
            'account_code' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'fiscal_year_id' => 'required|exists:fiscal_years,id',
        ]);

        ActivityBudget::create($validated);

        return back()->with('success', 'Pagu anggaran berhasil ditambahkan.');
    }

    public function updateBudget(Request $request, ActivityBudget $budget): RedirectResponse
    {
        if ($budget->fiscalYear->is_locked) {
            return back()->with('error', 'Tahun anggaran sudah dikunci.');
        }

        $validated = $request->validate([
            'budget_category' => 'required|string|in:personnel,goods_services,capital,other',
            'account_code' => 'nullable|string|max:50',
            'account_name' => 'nullable|string|max:255',
            'description' => 'required|string|max:255',
            'revision_description' => 'required|string|max:1000',

            // Items
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:budget_items,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.volume' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $amountMenjadi = (float) array_reduce($validated['items'], function ($sum, $item) {
            return $sum + ($item['volume'] * $item['unit_price']);
        }, 0.0);

        $oldItems = $budget->budgetItems->keyBy('id');
        /** @var array<int, array<string, mixed>> $validatedItems */
        $validatedItems = $validated['items'];
        $newItems = collect($validatedItems);
        $newItemIds = $newItems->pluck('id')->filter()->toArray();

        // Prevent deletion of items that already have realizations
        foreach ($oldItems as $oldId => $oldItem) {
            if (! in_array($oldId, $newItemIds)) {
                if (RealizationItem::where('budget_item_id', $oldId)->exists()) {
                    throw ValidationException::withMessages([
                        'items' => ["Item POK '{$oldItem->name}' tidak dapat dihapus karena sudah memiliki data realisasi belanja."],
                    ]);
                }
            }
        }

        DB::transaction(function () use ($validated, $budget, $amountMenjadi, $oldItems, $newItems, $newItemIds) {
            // Create budget revision record
            $revision = BudgetRevision::create([
                'activity_budget_id' => $budget->id,
                'revision_number' => $budget->version, // Old version
                'description' => $validated['revision_description'],
                'amount_semula' => (float) $budget->amount,
                'amount_menjadi' => $amountMenjadi,
                'revised_by' => Auth::id(),
            ]);

            // 1. Process deleted items
            foreach ($oldItems as $oldId => $oldItem) {
                if (! in_array($oldId, $newItemIds)) {
                    BudgetRevisionDetail::create([
                        'budget_revision_id' => $revision->id,
                        'budget_item_id' => $oldId,
                        'name_semula' => $oldItem->name,
                        'volume_semula' => $oldItem->volume,
                        'unit_semula' => $oldItem->unit,
                        'unit_price_semula' => $oldItem->unit_price,
                        'total_semula' => $oldItem->total,
                        'name_menjadi' => null,
                        'volume_menjadi' => 0.0,
                        'unit_menjadi' => null,
                        'unit_price_menjadi' => 0.0,
                        'total_menjadi' => 0.0,
                    ]);

                    $oldItem->delete();
                }
            }

            // 2. Process new and updated items
            foreach ($newItems as $item) {
                if (isset($item['id']) && $oldItems->has($item['id'])) {
                    $oldItem = $oldItems->get($item['id']);
                    $totalMenjadi = (float) ($item['volume'] * $item['unit_price']);

                    BudgetRevisionDetail::create([
                        'budget_revision_id' => $revision->id,
                        'budget_item_id' => $oldItem->id,
                        'name_semula' => $oldItem->name,
                        'volume_semula' => $oldItem->volume,
                        'unit_semula' => $oldItem->unit,
                        'unit_price_semula' => $oldItem->unit_price,
                        'total_semula' => $oldItem->total,
                        'name_menjadi' => $item['name'],
                        'volume_menjadi' => $item['volume'],
                        'unit_menjadi' => $item['unit'],
                        'unit_price_menjadi' => $item['unit_price'],
                        'total_menjadi' => $totalMenjadi,
                    ]);

                    $oldItem->update([
                        'name' => $item['name'],
                        'volume' => $item['volume'],
                        'unit' => $item['unit'],
                        'unit_price' => $item['unit_price'],
                        'total' => $totalMenjadi,
                    ]);
                } else {
                    $totalMenjadi = (float) ($item['volume'] * $item['unit_price']);

                    $createdItem = BudgetItem::create([
                        'activity_budget_id' => $budget->id,
                        'name' => $item['name'],
                        'volume' => $item['volume'],
                        'unit' => $item['unit'],
                        'unit_price' => $item['unit_price'],
                        'total' => $totalMenjadi,
                    ]);

                    BudgetRevisionDetail::create([
                        'budget_revision_id' => $revision->id,
                        'budget_item_id' => $createdItem->id,
                        'name_semula' => null,
                        'volume_semula' => 0.0,
                        'unit_semula' => null,
                        'unit_price_semula' => 0.0,
                        'total_semula' => 0.0,
                        'name_menjadi' => $item['name'],
                        'volume_menjadi' => $item['volume'],
                        'unit_menjadi' => $item['unit'],
                        'unit_price_menjadi' => $item['unit_price'],
                        'total_menjadi' => $totalMenjadi,
                    ]);
                }
            }

            // Update parent budget
            $budget->update([
                'budget_category' => $validated['budget_category'],
                'account_code' => $validated['account_code'],
                'account_name' => $validated['account_name'],
                'description' => $validated['description'],
                'amount' => $amountMenjadi,
                'version' => $budget->version + 1,
            ]);

            // Reset activity status to draft if it was approved
            if ($budget->activity->status === 'approved') {
                $budget->activity->update(['status' => 'draft']);

                Notification::create([
                    'user_id' => $budget->activity->responsible_user_id ?: Auth::id(),
                    'title' => 'Persetujuan Kegiatan Direset (Revisi POK)',
                    'message' => "POK untuk kegiatan [{$budget->activity->code}] {$budget->activity->name} telah direvisi. Persetujuan direset ke Draft dan harus diajukan ulang.",
                    'type' => 'approval',
                ]);
            }
        });

        return back()->with('success', 'Pagu anggaran berhasil direvisi.');
    }

    public function deleteBudget(ActivityBudget $budget): RedirectResponse
    {
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

    public function storeRealization(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activity_budget_id' => 'required|exists:activity_budgets,id',
            'realization_type' => 'required|string|in:surat_pesanan,non_pengadaan',
            'amount' => 'required|numeric|min:0',
            'realization_date' => 'required|date',
            'description' => 'required|string|max:255',
            'receipt_number' => 'nullable|string|max:50',

            // Dokumen pencairan
            'bast_number' => 'nullable|string|max:100',
            'bast_date' => 'nullable|date',
            'bap_number' => 'nullable|string|max:100',
            'bap_date' => 'nullable|date',
            'ba_penyerahan_number' => 'nullable|string|max:100',
            'ba_penyerahan_date' => 'nullable|date',
            'sp2d_number' => 'nullable|string|max:100',
            'sp2d_date' => 'nullable|date',
            'spp_number' => 'nullable|string|max:100',
            'spp_date' => 'nullable|date',
            'spm_number' => 'nullable|string|max:100',
            'spm_date' => 'nullable|date',
            'sptjb_number' => 'nullable|string|max:100',
            'sptjb_date' => 'nullable|date',

            // Pengadaan
            'procurement_type' => 'nullable|required_if:realization_type,surat_pesanan|string|in:surat_pesanan,spk',
            'procurement_title' => 'nullable|required_if:realization_type,surat_pesanan|string|max:255',
            'procurement_number' => 'nullable|required_if:realization_type,surat_pesanan|string|max:100',
            'procurement_date' => 'nullable|required_if:realization_type,surat_pesanan|date',
            'work_duration' => 'nullable|string|max:100',
            'nota_dinas_number' => 'nullable|string|max:100',
            'nota_dinas_date' => 'nullable|date',
            'ba_pl_number' => 'nullable|string|max:100',
            'ba_pl_date' => 'nullable|date',
            'ppk_id' => 'nullable|exists:users,id',
            'kpa_id' => 'nullable|exists:users,id',

            // Vendor
            'vendor_name' => 'nullable|required_if:realization_type,surat_pesanan|string|max:255',
            'vendor_npwp' => 'nullable|string|max:50',
            'vendor_address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',

            // Items
            'items' => 'required|array|min:1',
            'items.*.budget_item_id' => 'required|exists:budget_items,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.volume' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_pph21' => 'nullable|numeric|min:0',
            'items.*.tax_pph21_mixed' => 'nullable|boolean',
            'items.*.tax_pph22' => 'nullable|numeric|min:0',
            'items.*.tax_pph23' => 'nullable|numeric|min:0',
            'items.*.tax_ppn' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string',
        ]);

        $budget = ActivityBudget::findOrFail($validated['activity_budget_id']);
        if (! $budget instanceof ActivityBudget) {
            throw new \RuntimeException('Invalid activity budget.');
        }

        if ($budget->fiscalYear->is_locked) {
            throw ValidationException::withMessages([
                'activity_budget_id' => ['Tahun anggaran sudah dikunci.'],
            ]);
        }

        if ($budget->activity->status !== 'approved') {
            throw ValidationException::withMessages([
                'activity_budget_id' => ['Realisasi anggaran hanya dapat dicatat untuk kegiatan yang sudah disetujui.'],
            ]);
        }

        // Validate realization date within the range of start_date and end_date of Fiscal Year
        $realizationDate = Carbon::parse($validated['realization_date']);
        $fiscalYear = $budget->fiscalYear;

        if ($realizationDate->lt($fiscalYear->start_date) || $realizationDate->gt($fiscalYear->end_date)) {
            throw ValidationException::withMessages([
                'realization_date' => ["Tanggal realisasi harus berada di dalam rentang tahun anggaran ({$fiscalYear->start_date->format('d-m-Y')} s.d. {$fiscalYear->end_date->format('d-m-Y')})."],
            ]);
        }

        // Validate chronologically: realization_date >= procurement_date (if procurement)
        if ($validated['realization_type'] === 'surat_pesanan' && isset($validated['procurement_date'])) {
            $procurementDate = Carbon::parse($validated['procurement_date']);
            if ($realizationDate->lt($procurementDate)) {
                throw ValidationException::withMessages([
                    'realization_date' => ['Tanggal realisasi belanja tidak boleh mendahului tanggal dokumen kontrak/surat pesanan.'],
                ]);
            }
        }

        // Perform item-level validation
        foreach ($validated['items'] as $item) {
            $budgetItem = BudgetItem::find($item['budget_item_id']);
            if (! $budgetItem instanceof BudgetItem) {
                continue;
            }

            // 1. Mark-up Prevention
            if ((float) $item['unit_price'] > (float) $budgetItem->unit_price) {
                throw ValidationException::withMessages([
                    'items' => ["Harga satuan untuk item '{$item['name']}' (".number_format($item['unit_price'], 0, ',', '.').') melebihi standar pagu rencana POK ('.number_format($budgetItem->unit_price, 0, ',', '.').').'],
                ]);
            }

            // 2. Volume Overage Prevention
            $realizedVolume = (float) RealizationItem::where('budget_item_id', $budgetItem->id)->sum('volume');
            $remainingVolume = (float) max(0.0, (float) $budgetItem->volume - $realizedVolume);

            if ((float) $item['volume'] > $remainingVolume) {
                throw ValidationException::withMessages([
                    'items' => ["Volume kuantitas untuk item '{$item['name']}' ({$item['volume']}) melebihi sisa volume rencana POK (Sisa: {$remainingVolume} {$budgetItem->unit})."],
                ]);
            }
        }

        DB::transaction(function () use ($validated) {
            $procurementId = null;

            if ($validated['realization_type'] === 'surat_pesanan') {
                // 1. Create or Update Vendor
                $vendor = Vendor::updateOrCreate(
                    ['name' => $validated['vendor_name']],
                    [
                        'npwp' => $validated['vendor_npwp'] ?? null,
                        'address' => $validated['vendor_address'] ?? null,
                        'bank_name' => $validated['bank_name'] ?? null,
                        'bank_account_number' => $validated['bank_account_number'] ?? null,
                        'bank_account_name' => $validated['bank_account_name'] ?? null,
                    ]
                );

                // 2. Create or Update Procurement
                $procurement = Procurement::updateOrCreate(
                    [
                        'document_number' => $validated['procurement_number'],
                        'procurement_type' => $validated['procurement_type'] ?? 'surat_pesanan',
                    ],
                    [
                        'activity_budget_id' => $validated['activity_budget_id'],
                        'vendor_id' => $vendor->id,
                        'title' => $validated['procurement_title'] ?? $validated['description'],
                        'document_date' => $validated['procurement_date'],
                        'work_duration' => $validated['work_duration'] ?? null,
                        'nota_dinas_number' => $validated['nota_dinas_number'] ?? null,
                        'nota_dinas_date' => $validated['nota_dinas_date'] ?? null,
                        'ba_pl_number' => $validated['ba_pl_number'] ?? null,
                        'ba_pl_date' => $validated['ba_pl_date'] ?? null,
                        'ppk_id' => $validated['ppk_id'] ?? null,
                        'kpa_id' => $validated['kpa_id'] ?? null,
                    ]
                );

                $procurementId = $procurement->id;
            }

            // 3. Create Budget Realization
            $realization = BudgetRealization::create([
                'activity_budget_id' => $validated['activity_budget_id'],
                'procurement_id' => $procurementId,
                'realization_type' => $validated['realization_type'],
                'amount' => $validated['amount'],
                'realization_date' => $validated['realization_date'],
                'description' => $validated['description'],
                'receipt_number' => $validated['receipt_number'] ?? null,
                'bast_number' => $validated['bast_number'] ?? null,
                'bast_date' => $validated['bast_date'] ?? null,
                'bap_number' => $validated['bap_number'] ?? null,
                'bap_date' => $validated['bap_date'] ?? null,
                'ba_penyerahan_number' => $validated['ba_penyerahan_number'] ?? null,
                'ba_penyerahan_date' => $validated['ba_penyerahan_date'] ?? null,
                'sp2d_number' => $validated['sp2d_number'] ?? null,
                'sp2d_date' => $validated['sp2d_date'] ?? null,
                'spp_number' => $validated['spp_number'] ?? null,
                'spp_date' => $validated['spp_date'] ?? null,
                'spm_number' => $validated['spm_number'] ?? null,
                'spm_date' => $validated['spm_date'] ?? null,
                'sptjb_number' => $validated['sptjb_number'] ?? null,
                'sptjb_date' => $validated['sptjb_date'] ?? null,
                'verified_by' => null,
                'verified_at' => null,
            ]);

            // 4. Create Realization Items
            foreach ($validated['items'] as $item) {
                $realization->items()->create([
                    'budget_item_id' => $item['budget_item_id'],
                    'name' => $item['name'],
                    'volume' => $item['volume'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'tax_pph21' => $item['tax_pph21'] ?? 0,
                    'tax_pph21_mixed' => $item['tax_pph21_mixed'] ?? false,
                    'tax_pph22' => $item['tax_pph22'] ?? 0,
                    'tax_pph23' => $item['tax_pph23'] ?? 0,
                    'tax_ppn' => $item['tax_ppn'] ?? 0,
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }
        });

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
