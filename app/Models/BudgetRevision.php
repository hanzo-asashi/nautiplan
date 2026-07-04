<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $activity_budget_id
 * @property int $revision_number
 * @property string $description
 * @property float $amount_semula
 * @property float $amount_menjadi
 * @property int|null $revised_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BudgetRevision extends Model
{
    protected $fillable = [
        'activity_budget_id',
        'revision_number',
        'description',
        'amount_semula',
        'amount_menjadi',
        'revised_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'revision_number' => 'integer',
            'amount_semula' => 'float',
            'amount_menjadi' => 'float',
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
     * @return BelongsTo<User, $this>
     */
    public function revisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    /**
     * @return HasMany<BudgetRevisionDetail, $this>
     */
    public function details(): HasMany
    {
        return $this->hasMany(BudgetRevisionDetail::class);
    }
}
