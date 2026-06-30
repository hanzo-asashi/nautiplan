<?php

namespace App\Models;

use Database\Factories\FiscalYearFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $year
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property bool $is_active
 * @property bool $is_locked
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class FiscalYear extends Model
{
    /** @use HasFactory<FiscalYearFactory> */
    use HasFactory;

    protected $fillable = [
        'year',
        'start_date',
        'end_date',
        'is_active',
        'is_locked',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Renstra, $this>
     */
    public function renstras(): HasMany
    {
        return $this->hasMany(Renstra::class);
    }

    /**
     * @return HasMany<Renja, $this>
     */
    public function renjas(): HasMany
    {
        return $this->hasMany(Renja::class);
    }

    /**
     * @return HasMany<Program, $this>
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    /**
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}
