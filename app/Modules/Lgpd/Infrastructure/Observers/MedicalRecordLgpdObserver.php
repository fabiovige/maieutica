<?php

namespace App\Modules\Lgpd\Infrastructure\Observers;

use App\Models\MedicalRecord;
use App\Modules\Lgpd\Application\Services\AccessLogService;
use Illuminate\Support\Facades\Log;

class MedicalRecordLgpdObserver
{
    public function __construct(
        private AccessLogService $accessLogService
    ) {}

    /**
     * Handle the MedicalRecord "updated" event.
     */
    public function updated(MedicalRecord $record): void
    {
        try {
            $this->accessLogService->create(
                operatorId: auth()->id() ?? 0,
                recordId: $record->id,
                operationType: 'edit',
                ip: request()->ip(),
                userAgent: request()->userAgent()
            );
        } catch (\Throwable $e) {
            Log::error('[LGPD] Observer failed', [
                'observer' => static::class,
                'event' => 'updated',
                'record_id' => $record->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the MedicalRecord "deleted" event.
     */
    public function deleted(MedicalRecord $record): void
    {
        try {
            $this->accessLogService->create(
                operatorId: auth()->id() ?? 0,
                recordId: $record->id,
                operationType: 'delete',
                ip: request()->ip(),
                userAgent: request()->userAgent()
            );
        } catch (\Throwable $e) {
            Log::error('[LGPD] Observer failed', [
                'observer' => static::class,
                'event' => 'deleted',
                'record_id' => $record->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the MedicalRecord "restored" event.
     */
    public function restored(MedicalRecord $record): void
    {
        try {
            $this->accessLogService->create(
                operatorId: auth()->id() ?? 0,
                recordId: $record->id,
                operationType: 'restore',
                ip: request()->ip(),
                userAgent: request()->userAgent()
            );
        } catch (\Throwable $e) {
            Log::error('[LGPD] Observer failed', [
                'observer' => static::class,
                'event' => 'restored',
                'record_id' => $record->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
