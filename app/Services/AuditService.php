<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditService
{
    public static function log($action, $model, $oldValues = null, $newValues = null)
    {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    public static function logCreated($model, $data = null)
    {
        return self::log('created', $model, null, $data ?? $model->toArray());
    }

    public static function logUpdated($model, $oldValues, $newValues)
    {
        return self::log('updated', $model, $oldValues, $newValues);
    }

    public static function logDeleted($model)
    {
        return self::log('deleted', $model, $model->toArray(), null);
    }
}
