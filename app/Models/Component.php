<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $sub_output_id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'sub_output_id',
        'code',
        'name',
    ];

    /**
     * @return BelongsTo<SubOutput, $this>
     */
    public function subOutput(): BelongsTo
    {
        return $this->belongsTo(SubOutput::class);
    }

    /**
     * @return HasMany<SubComponent, $this>
     */
    public function subComponents(): HasMany
    {
        return $this->hasMany(SubComponent::class);
    }
}
