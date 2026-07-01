<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $activity_id
 * @property string $quarter
 * @property string $status
 * @property string|null $progress_description
 * @property string|null $obstacles
 * @property string|null $solutions
 * @property int|null $submitted_by
 * @property CarbonImmutable|null $submitted_at
 * @property int|null $evaluation_score
 * @property string|null $evaluation_notes
 * @property string|null $recommendations
 * @property int|null $reviewed_by
 * @property CarbonImmutable|null $reviewed_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class ActivityReport extends Model
{
    protected $fillable = [
        'activity_id',
        'quarter',
        'status',
        'progress_description',
        'obstacles',
        'solutions',
        'submitted_by',
        'submitted_at',
        'evaluation_score',
        'evaluation_notes',
        'recommendations',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'evaluation_score' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
