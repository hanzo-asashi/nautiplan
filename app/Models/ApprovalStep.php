<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $approval_request_id
 * @property int $step_order
 * @property int $role_id
 * @property int|null $approver_id
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $acted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ApprovalStep extends Model
{
    protected $fillable = [
        'approval_request_id',
        'step_order',
        'role_id',
        'approver_id',
        'status',
        'notes',
        'acted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'acted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ApprovalRequest, $this>
     */
    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
