<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $activity_id
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property int $progress_percentage
 * @property int|null $assigned_to
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class SubActivity extends Model
{
    protected $fillable = [
        'activity_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'progress_percentage',
        'assigned_to',
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
     * @return BelongsTo<Activity, $this>
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
