<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $activity_id
 * @property string $code
 * @property string $name
 * @property string $indicator_type
 * @property float $target_value
 * @property float|null $actual_value
 * @property string $unit_of_measure
 * @property string $quarter
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ActivityIndicator extends Model
{
    protected $fillable = [
        'activity_id',
        'code',
        'name',
        'indicator_type',
        'target_value',
        'actual_value',
        'unit_of_measure',
        'quarter',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'actual_value' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function getAchievementPercentageAttribute(): float
    {
        if ((float) $this->target_value === 0.0) {
            return 0;
        }

        return round(((float) ($this->actual_value ?? 0) / (float) $this->target_value) * 100, 2);
    }
}
