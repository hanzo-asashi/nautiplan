<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $budget_revision_id
 * @property int|null $budget_item_id
 * @property string|null $name_semula
 * @property float $volume_semula
 * @property string|null $unit_semula
 * @property float $unit_price_semula
 * @property float $total_semula
 * @property string|null $name_menjadi
 * @property float $volume_menjadi
 * @property string|null $unit_menjadi
 * @property float $unit_price_menjadi
 * @property float $total_menjadi
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BudgetRevisionDetail extends Model
{
    protected $fillable = [
        'budget_revision_id',
        'budget_item_id',
        'name_semula',
        'volume_semula',
        'unit_semula',
        'unit_price_semula',
        'total_semula',
        'name_menjadi',
        'volume_menjadi',
        'unit_menjadi',
        'unit_price_menjadi',
        'total_menjadi',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'volume_semula' => 'float',
            'unit_price_semula' => 'float',
            'total_semula' => 'float',
            'volume_menjadi' => 'float',
            'unit_price_menjadi' => 'float',
            'total_menjadi' => 'float',
        ];
    }

    /**
     * @return BelongsTo<BudgetRevision, $this>
     */
    public function revision(): BelongsTo
    {
        return $this->belongsTo(BudgetRevision::class, 'budget_revision_id');
    }

    /**
     * @return BelongsTo<BudgetItem, $this>
     */
    public function budgetItem(): BelongsTo
    {
        return $this->belongsTo(BudgetItem::class);
    }
}
