<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $role_id
 * @property string $module
 * @property string $action
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Permission extends Model
{
    protected $fillable = [
        'role_id',
        'module',
        'action',
    ];

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
