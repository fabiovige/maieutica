<?php

namespace App\Modules\Lgpd\Infrastructure\Models;

use App\Models\User;
use App\Modules\Lgpd\Domain\Exceptions\ImmutableRecordException;
use App\Modules\Lgpd\Domain\ValueObjects\OperationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessLogModel extends Model
{
    /**
     * Tabela imutável — sem updated_at.
     */
    const UPDATED_AT = null;

    protected $table = 'lgpd_access_logs';

    protected $fillable = [
        'operator_id',
        'medical_record_id',
        'operation_type',
        'ip_address',
        'user_agent',
        'accessed_at',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
        'operation_type' => OperationType::class,
    ];

    // ─── Boot — Imutabilidade ────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            throw ImmutableRecordException::forAccessLog($model->id, 'alterado');
        });

        static::deleting(function ($model) {
            throw ImmutableRecordException::forAccessLog($model->id, 'excluído');
        });
    }

    // ─── Relationships ───────────────────────────────────────────────

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }
}
