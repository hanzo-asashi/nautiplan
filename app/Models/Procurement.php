<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $activity_budget_id
 * @property int $vendor_id
 * @property string $title
 * @property string $procurement_type
 * @property string $document_number
 * @property Carbon $document_date
 * @property string|null $work_duration
 * @property string|null $nota_dinas_number
 * @property Carbon|null $nota_dinas_date
 * @property string|null $ba_pl_number
 * @property Carbon|null $ba_pl_date
 * @property int|null $ppk_id
 * @property int|null $kpa_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Procurement extends Model
{
    protected $fillable = [
        'activity_budget_id',
        'vendor_id',
        'title',
        'procurement_type',
        'document_number',
        'document_date',
        'work_duration',
        'nota_dinas_number',
        'nota_dinas_date',
        'ba_pl_number',
        'ba_pl_date',
        'ppk_id',
        'kpa_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'nota_dinas_date' => 'date',
            'ba_pl_date' => 'date',
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
     * @return BelongsTo<Vendor, $this>
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function ppk(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ppk_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function kpa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kpa_id');
    }

    /**
     * @return HasMany<BudgetRealization, $this>
     */
    public function realizations(): HasMany
    {
        return $this->hasMany(BudgetRealization::class);
    }
}
