<?php

namespace App\Models;

use App\Concerns\HasAuditTrail;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property int $program_id
 * @property int|null $renja_id
 * @property int $unit_id
 * @property int $fiscal_year_id
 * @property int|null $responsible_user_id
 * @property string $status
 * @property string $priority
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property int $progress_percentage
 * @property string|null $location
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasAuditTrail, HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'program_id',
        'renja_id',
        'unit_id',
        'fiscal_year_id',
        'responsible_user_id',
        'status',
        'priority',
        'start_date',
        'end_date',
        'progress_percentage',
        'location',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'progress_percentage' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Program, $this>
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * @return BelongsTo<Renja, $this>
     */
    public function renja(): BelongsTo
    {
        return $this->belongsTo(Renja::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return BelongsTo<FiscalYear, $this>
     */
    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * @return HasMany<SubActivity, $this>
     */
    public function subActivities(): HasMany
    {
        return $this->hasMany(SubActivity::class);
    }

    /**
     * @return HasMany<ActivityBudget, $this>
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(ActivityBudget::class);
    }

    /**
     * @return HasMany<ActivityIndicator, $this>
     */
    public function indicators(): HasMany
    {
        return $this->hasMany(ActivityIndicator::class);
    }

    /**
     * @return HasMany<ActivityDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ActivityDocument::class);
    }

    /**
     * @return HasMany<ActivityReport, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(ActivityReport::class);
    }

    /**
     * @return MorphMany<ApprovalRequest, $this>
     */
    public function approvalRequests(): MorphMany
    {
        return $this->morphMany(ApprovalRequest::class, 'approvable');
    }

    public function getTotalBudgetAttribute(): float
    {
        return (float) $this->budgets()->sum('amount');
    }

    public function getTotalRealizedAttribute(): float
    {
        return (float) $this->budgets()
            ->join('budget_realizations', 'activity_budgets.id', '=', 'budget_realizations.activity_budget_id')
            ->sum('budget_realizations.amount');
    }
}
