<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $activity_budget_id
 * @property string $name
 * @property float $volume
 * @property string $unit
 * @property float $unit_price
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BudgetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_budget_id',
        'name',
        'volume',
        'unit',
        'unit_price',
        'total',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'volume' => 'float',
            'unit_price' => 'float',
            'total' => 'float',
        ];
    }

    /**
     * @return BelongsTo<ActivityBudget, $this>
     */
    public function activityBudget(): BelongsTo
    {
        return $this->belongsTo(ActivityBudget::class);
    }
}
