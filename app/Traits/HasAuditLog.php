<?php

namespace App\Traits;

use App\Models\AuditLog;

trait HasAuditLog
{
    protected static function bootHasAuditLog(): void
    {
        static::created(function ($model) {
            $model->logAuditAction('CREATE', null, $model->getAuditableData());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();

            if (!empty($changes)) {
                $auditableBefore = [];
                $auditableAfter = [];

                foreach ($changes as $key => $value) {
                    if ($model->isAuditableField($key)) {
                        $auditableBefore[$key] = $original[$key] ?? null;
                        $auditableAfter[$key] = $value;
                    }
                }

                if (!empty($auditableBefore) || !empty($auditableAfter)) {
                    $model->logAuditAction('UPDATE', $auditableBefore, $auditableAfter);
                }
            }
        });

        static::deleted(function ($model) {
            $model->logAuditAction('DELETE', $model->getAuditableData(), null);
        });
    }

    public function logAuditAction(string $action, array $dataBefore = null, array $dataAfter = null, string $context = null): void
    {
        AuditLog::logAction(
            $action,
            get_class($this),
            $this->getKey(),
            $dataBefore,
            $dataAfter,
            $context
        );
    }

    public function logReadAccess(string $context = null): void
    {
        $this->logAuditAction('READ', null, null, $context);
    }

    protected function getAuditableData(): array
    {
        $data = [];
        $attributes = $this->getAttributes();

        foreach ($attributes as $key => $value) {
            if ($this->isAuditableField($key)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    protected function isAuditableField(string $field): bool
    {
        $nonAuditableFields = $this->getNonAuditableFields();

        if (in_array($field, $nonAuditableFields)) {
            return false;
        }

        if (property_exists($this, 'auditableFields') && !empty($this->auditableFields)) {
            return in_array($field, $this->auditableFields);
        }

        return !in_array($field, ['created_at', 'updated_at', 'deleted_at', 'remember_token']);
    }

    protected function getNonAuditableFields(): array
    {
        return array_merge(
            ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token'],
            $this->nonAuditableFields ?? []
        );
    }

    public function auditLogs()
    {
        return AuditLog::where('resource', class_basename(get_class($this)))
            ->where('resource_id', $this->getKey())
            ->orderBy('created_at', 'desc');
    }

    public function getRecentAuditLogs(int $limit = 10)
    {
        return $this->auditLogs()->limit($limit)->get();
    }

    public function getAuditLogsForAction(string $action)
    {
        return $this->auditLogs()->where('action', $action)->get();
    }
}