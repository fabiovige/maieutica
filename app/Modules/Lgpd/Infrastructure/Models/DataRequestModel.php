<?php

namespace App\Modules\Lgpd\Infrastructure\Models;

use App\Models\User;
use App\Modules\Lgpd\Domain\ValueObjects\DataRequestStatus;
use App\Modules\Lgpd\Domain\ValueObjects\DataRequestType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataRequestModel extends Model
{
    protected $table = 'lgpd_data_requests';

    protected $fillable = [
        'type',
        'requester_name',
        'requester_document',
        'contact_method',
        'status',
        'opened_at',
        'deadline_at',
        'started_at',
        'completed_at',
        'response',
        'retention_justification',
        'assigned_operator_id',
        'created_by',
        'alerted_at',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'deadline_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'alerted_at' => 'datetime',
        'type' => DataRequestType::class,
        'status' => DataRequestStatus::class,
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function assignedOperator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_operator_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeAberta($query)
    {
        return $query->where('status', 'aberta');
    }

    public function scopeEmAndamento($query)
    {
        return $query->where('status', 'em_andamento');
    }

    public function scopeConcluida($query)
    {
        return $query->where('status', 'concluida');
    }

    public function scopeVencida($query)
    {
        return $query->where('status', 'vencida');
    }

    public function scopePendentes($query)
    {
        return $query->whereIn('status', ['aberta', 'em_andamento']);
    }
}
