<?php

namespace App\Actions\Budget;

use App\Models\ActivityBudget;
use App\Models\BudgetItem;
use App\Models\BudgetRevision;
use App\Models\BudgetRevisionDetail;
use App\Models\Notification;
use App\Models\RealizationItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateBudgetAction
{
    /**
     * Update an activity budget and create a revision history.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(ActivityBudget $budget, array $data): void
    {
        $amountMenjadi = (float) array_reduce($data['items'], function ($sum, $item) {
            return $sum + ($item['volume'] * $item['unit_price']);
        }, 0.0);

        $oldItems = $budget->budgetItems->keyBy('id');
        /** @var array<int, array<string, mixed>> $validatedItems */
        $validatedItems = $data['items'];
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

        DB::transaction(function () use ($data, $budget, $amountMenjadi, $oldItems, $newItems, $newItemIds) {
            // Create budget revision record
            $revision = BudgetRevision::create([
                'activity_budget_id' => $budget->id,
                'revision_number' => $budget->version, // Old version
                'description' => $data['revision_description'],
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
                'budget_category' => $data['budget_category'],
                'account_code' => $data['account_code'] ?? null,
                'account_name' => $data['account_name'] ?? null,
                'description' => $data['description'],
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
    }
}
