<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $activity_budget_id
 * @property float $amount
 * @property Carbon $realization_date
 * @property string|null $description
 * @property string|null $receipt_number
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class BudgetRealization extends Model
{
    protected $fillable = [
        'activity_budget_id',
        'realization_type',
        'amount',
        'realization_date',
        'description',
        'receipt_number',
        'vendor_name',
        'vendor_address',
        'vendor_npwp',
        'procurement_number',
        'procurement_date',
        'sp2d_number',
        'sp2d_date',
        'verified_by',
        'verified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'realization_date' => 'date',
            'procurement_date' => 'date',
            'sp2d_date' => 'date',
            'verified_at' => 'datetime',
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
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
