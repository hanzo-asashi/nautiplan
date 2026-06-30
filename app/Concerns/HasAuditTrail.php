<?php

namespace App\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Automatically log model changes to the audit_logs table.
 *
 * Add `use HasAuditTrail;` to any model you want to track.
 */
trait HasAuditTrail
{
    public static function bootHasAuditTrail(): void
    {
        static::created(function (Model $model) {
            static::logAudit($model, 'created', null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();

            // Remove timestamp changes from audit
            unset($changes['updated_at'], $original['updated_at']);

            if (! empty($changes)) {
                $oldValues = array_intersect_key($original, $changes);
                static::logAudit($model, 'updated', $oldValues, $changes);
            }
        });

        static::deleted(function (Model $model) {
            static::logAudit($model, 'deleted', $model->getAttributes(), null);
        });
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    protected static function logAudit(Model $model, string $event, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
