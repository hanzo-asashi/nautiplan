<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $component_id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SubComponent extends Model
{
    protected $fillable = [
        'component_id',
        'code',
        'name',
    ];

    /**
     * @return BelongsTo<Component, $this>
     */
    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    /**
     * @return HasMany<ActivityBudget, $this>
     */
    public function activityBudgets(): HasMany
    {
        return $this->hasMany(ActivityBudget::class);
    }
}
