<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $renstra_id
 * @property string $code
 * @property string $name
 * @property float $target_value
 * @property string $unit_of_measure
 * @property float $baseline_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class RenstraIndicator extends Model
{
    protected $fillable = [
        'renstra_id',
        'code',
        'name',
        'target_value',
        'unit_of_measure',
        'baseline_value',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'baseline_value' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Renstra, $this>
     */
    public function renstra(): BelongsTo
    {
        return $this->belongsTo(Renstra::class);
    }
}
