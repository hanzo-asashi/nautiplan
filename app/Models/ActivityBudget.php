<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $activity_id
 * @property string $budget_category
 * @property string $description
 * @property float $amount
 * @property int $fiscal_year_id
 * @property int $version
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ActivityBudget extends Model
{
    protected $fillable = [
        'activity_id',
        'budget_category',
        'account_code',
        'account_name',
        'description',
        'amount',
        'fiscal_year_id',
        'version',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'version' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * @return BelongsTo<FiscalYear, $this>
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * @return HasMany<BudgetRealization, $this>
     */
    public function realizations(): HasMany
    {
        return $this->hasMany(BudgetRealization::class);
    }

    public function getTotalRealizedAttribute(): float
    {
        return (float) $this->realizations()->sum('amount');
    }

    public function getRealizationPercentageAttribute(): float
    {
        if ((float) $this->amount === 0.0) {
            return 0;
        }

        return round(($this->total_realized / (float) $this->amount) * 100, 2);
    }
}
