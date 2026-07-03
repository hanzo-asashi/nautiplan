<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $output_id
 * @property string $code
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SubOutput extends Model
{
    use HasFactory;

    protected $fillable = [
        'output_id',
        'code',
        'name',
    ];

    /**
     * @return BelongsTo<Output, $this>
     */
    public function output(): BelongsTo
    {
        return $this->belongsTo(Output::class);
    }

    /**
     * @return HasMany<Component, $this>
     */
    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }
}
