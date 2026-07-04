<?php

namespace App\Models;

use App\Concerns\HasAuditTrail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
use Illuminate\Support\Carbon;

class BudgetItem extends Model
{
    use HasAuditTrail;

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

    /**
     * @return HasMany<RealizationItem, $this>
     */
    public function realizationItems(): HasMany
    {
        return $this->hasMany(RealizationItem::class, 'budget_item_id');
    }

    public function getRealizedVolumeAttribute(): float
    {
        return (float) $this->realizationItems()->sum('volume');
    }

    public function getRemainingVolumeAttribute(): float
    {
        return (float) max(0, $this->volume - $this->realized_volume);
    }
}
