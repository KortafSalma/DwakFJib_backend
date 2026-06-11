<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::logAudit($model, 'created');
        });

        static::updated(function ($model) {
            if ($model->isDirty()) {
                self::logAudit($model, 'updated', $model->getChanges());
            }
        });

        static::deleted(function ($model) {
            self::logAudit($model, 'deleted');
        });
    }

    protected static function logAudit($model, string $action, ?array $changes = null): void
    {
        $user = auth()->user();

        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'changes' => $changes,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
