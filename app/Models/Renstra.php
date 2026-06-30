<?php

namespace App\Models;

use Database\Factories\RenstraFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int $start_year
 * @property int $end_year
 * @property string $status
 * @property string|null $vision
 * @property array<int, string>|null $mission
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Renstra extends Model
{
    /** @use HasFactory<RenstraFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'start_year',
        'end_year',
        'status',
        'vision',
        'mission',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mission' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<RenstraIndicator, $this>
     */
    public function indicators(): HasMany
    {
        return $this->hasMany(RenstraIndicator::class);
    }

    /**
     * @return HasMany<Program, $this>
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    /**
     * @return HasMany<Renja, $this>
     */
    public function renjas(): HasMany
    {
        return $this->hasMany(Renja::class);
    }

    public function getPeriodAttribute(): string
    {
        return "{$this->start_year} - {$this->end_year}";
    }
}
