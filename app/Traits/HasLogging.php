<?php

namespace App\Traits;

use App\Enums\LogOperation;
use App\Services\Log\LoggingService;
use Illuminate\Database\Eloquent\Model;

trait HasLogging
{
    protected static function bootHasLogging(): void
    {
        static::created(function (Model $model) {
            LoggingService::createLogEntryForModel($model, LogOperation::CREATE);
        });

        static::updated(function (Model $model) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();

            LoggingService::createLogEntryForModel($model, LogOperation::UPDATE, [
                'changes' => $changes,
                'original_values' => array_intersect_key($original, $changes),
                'changed_fields' => array_keys($changes),
            ]);
        });

        static::deleted(function (Model $model) {
            LoggingService::createLogEntryForModel($model, LogOperation::DELETE, [
                'deleted_attributes' => $model->getOriginal(),
                'soft_deleted' => method_exists($model, 'trashed') && $model->trashed(),
            ]);
        });
    }

    public function logCustomOperation(LogOperation $operation, string $message = null, array $context = []): void
    {
        $customMessage = $message ?? $this->getDefaultLogMessage($operation);
        
        LoggingService::createLogEntryForModel($this, $operation, array_merge($context, [
            'custom_operation' => true,
            'custom_message' => $customMessage,
        ]));
    }

    public function logRead(array $context = []): void
    {
        LoggingService::createLogEntryForModel($this, LogOperation::READ, $context);
    }

    public function logView(string $viewName = null, array $context = []): void
    {
        $this->logRead(array_merge($context, [
            'action' => 'view',
            'view_name' => $viewName,
            'viewed_at' => now()->toISOString(),
        ]));
    }

    public function logExport(string $format = null, array $context = []): void
    {
        LoggingService::createLogEntryForModel($this, LogOperation::EXPORT, array_merge($context, [
            'export_format' => $format,
            'exported_at' => now()->toISOString(),
        ]));
    }

    public function getLogIdentifier(): string
    {
        if (isset($this->attributes['name'])) {
            return $this->attributes['name'];
        }

        if (isset($this->attributes['title'])) {
            return $this->attributes['title'];
        }

        if (isset($this->attributes['email'])) {
            return $this->attributes['email'];
        }

        return "ID: {$this->getKey()}";
    }

    private function getDefaultLogMessage(LogOperation $operation): string
    {
        $modelName = class_basename(static::class);
        $identifier = $this->getLogIdentifier();
        $operationName = $operation->getDisplayName();

        return "{$modelName} - {$operationName}: {$identifier}";
    }
}