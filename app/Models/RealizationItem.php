<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $budget_realization_id
 * @property string $name
 * @property float $volume
 * @property string $unit
 * @property float $unit_price
 * @property float $tax_pph21
 * @property bool $tax_pph21_mixed
 * @property float $tax_pph22
 * @property float $tax_pph23
 * @property float $tax_ppn
 * @property string|null $remarks
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class RealizationItem extends Model
{
    protected $fillable = [
        'budget_realization_id',
        'budget_item_id',
        'name',
        'volume',
        'unit',
        'unit_price',
        'tax_pph21',
        'tax_pph21_mixed',
        'tax_pph22',
        'tax_pph23',
        'tax_ppn',
        'remarks',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'volume' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_pph21' => 'decimal:2',
            'tax_pph21_mixed' => 'boolean',
            'tax_pph22' => 'decimal:2',
            'tax_pph23' => 'decimal:2',
            'tax_ppn' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<BudgetRealization, $this>
     */
    public function realization(): BelongsTo
    {
        return $this->belongsTo(BudgetRealization::class, 'budget_realization_id');
    }

    /**
     * @return BelongsTo<BudgetItem, $this>
     */
    public function budgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class, 'budget_item_id');
    }

    public function getTotalPriceAttribute(): float
    {
        return (float) ($this->volume * $this->unit_price);
    }
}
