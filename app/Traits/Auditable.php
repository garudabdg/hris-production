<?php

namespace App\Traits;

use App\Models\DataAuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            self::logAudit($model, 'create');
        });

        static::updated(function (Model $model) {
            self::logAudit($model, 'update');
        });

        static::deleted(function (Model $model) {
            self::logAudit($model, 'delete');
        });
    }

    protected static function logAudit(Model $model, $action)
    {
        // Jangan track event jika tidak ada perubahan pada update
        if ($action === 'update' && empty($model->getDirty())) {
            return;
        }

        $oldValues = [];
        $newValues = [];

        if ($action === 'create') {
            $newValues = $model->getAttributes();
        } elseif ($action === 'update') {
            foreach ($model->getDirty() as $key => $value) {
                $oldValues[$key] = $model->getOriginal($key);
                $newValues[$key] = $value;
            }
        } elseif ($action === 'delete') {
            $oldValues = $model->getAttributes();
        }

        DataAuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => get_class($model),
            'model_id'   => $model->getKey(),
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
