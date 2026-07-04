<?php

namespace App\Actions\Budget;

use App\Models\ActivityBudget;
use App\Models\BudgetItem;
use App\Models\BudgetRealization;
use App\Models\Procurement;
use App\Models\RealizationItem;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StoreRealizationAction
{
    /**
     * Store a new budget realization with validations and transactions.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): void
    {
        $budget = ActivityBudget::findOrFail($data['activity_budget_id']);
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
        $realizationDate = Carbon::parse($data['realization_date']);
        $fiscalYear = $budget->fiscalYear;

        if ($realizationDate->lt($fiscalYear->start_date) || $realizationDate->gt($fiscalYear->end_date)) {
            throw ValidationException::withMessages([
                'realization_date' => ["Tanggal realisasi harus berada di dalam rentang tahun anggaran ({$fiscalYear->start_date->format('d-m-Y')} s.d. {$fiscalYear->end_date->format('d-m-Y')})."],
            ]);
        }

        // Validate chronologically: realization_date >= procurement_date (if procurement)
        if ($data['realization_type'] === 'surat_pesanan' && isset($data['procurement_date'])) {
            $procurementDate = Carbon::parse($data['procurement_date']);
            if ($realizationDate->lt($procurementDate)) {
                throw ValidationException::withMessages([
                    'realization_date' => ['Tanggal realisasi belanja tidak boleh mendahului tanggal dokumen kontrak/surat pesanan.'],
                ]);
            }
        }

        // Perform item-level validation
        foreach ($data['items'] as $item) {
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

        DB::transaction(function () use ($data) {
            $procurementId = null;

            if ($data['realization_type'] === 'surat_pesanan') {
                // 1. Create or Update Vendor
                $vendor = Vendor::updateOrCreate(
                    ['name' => $data['vendor_name']],
                    [
                        'npwp' => $data['vendor_npwp'] ?? null,
                        'address' => $data['vendor_address'] ?? null,
                        'bank_name' => $data['bank_name'] ?? null,
                        'bank_account_number' => $data['bank_account_number'] ?? null,
                        'bank_account_name' => $data['bank_account_name'] ?? null,
                    ]
                );

                // 2. Create or Update Procurement
                $procurement = Procurement::updateOrCreate(
                    [
                        'document_number' => $data['procurement_number'],
                        'procurement_type' => $data['procurement_type'] ?? 'surat_pesanan',
                    ],
                    [
                        'activity_budget_id' => $data['activity_budget_id'],
                        'vendor_id' => $vendor->id,
                        'title' => $data['procurement_title'] ?? $data['description'],
                        'document_date' => $data['procurement_date'],
                        'work_duration' => $data['work_duration'] ?? null,
                        'nota_dinas_number' => $data['nota_dinas_number'] ?? null,
                        'nota_dinas_date' => $data['nota_dinas_date'] ?? null,
                        'ba_pl_number' => $data['ba_pl_number'] ?? null,
                        'ba_pl_date' => $data['ba_pl_date'] ?? null,
                        'ppk_id' => $data['ppk_id'] ?? null,
                        'kpa_id' => $data['kpa_id'] ?? null,
                    ]
                );

                $procurementId = $procurement->id;
            }

            // 3. Create Budget Realization
            $realization = BudgetRealization::create([
                'activity_budget_id' => $data['activity_budget_id'],
                'procurement_id' => $procurementId,
                'realization_type' => $data['realization_type'],
                'amount' => $data['amount'],
                'realization_date' => $data['realization_date'],
                'description' => $data['description'],
                'receipt_number' => $data['receipt_number'] ?? null,
                'bast_number' => $data['bast_number'] ?? null,
                'bast_date' => $data['bast_date'] ?? null,
                'bap_number' => $data['bap_number'] ?? null,
                'bap_date' => $data['bap_date'] ?? null,
                'ba_penyerahan_number' => $data['ba_penyerahan_number'] ?? null,
                'ba_penyerahan_date' => $data['ba_penyerahan_date'] ?? null,
                'sp2d_number' => $data['sp2d_number'] ?? null,
                'sp2d_date' => $data['sp2d_date'] ?? null,
                'spp_number' => $data['spp_number'] ?? null,
                'spp_date' => $data['spp_date'] ?? null,
                'spm_number' => $data['spm_number'] ?? null,
                'spm_date' => $data['spm_date'] ?? null,
                'sptjb_number' => $data['sptjb_number'] ?? null,
                'sptjb_date' => $data['sptjb_date'] ?? null,
                'verified_by' => null,
                'verified_at' => null,
            ]);

            // 4. Create Realization Items
            foreach ($data['items'] as $item) {
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
    }
}
