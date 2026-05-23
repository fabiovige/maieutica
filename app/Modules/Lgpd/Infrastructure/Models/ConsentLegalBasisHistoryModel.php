<?php

namespace App\Modules\Lgpd\Infrastructure\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentLegalBasisHistoryModel extends Model
{
    /**
     * Tabela imutável de histórico — sem updated_at.
     */
    const UPDATED_AT = null;

    protected $table = 'lgpd_consent_legal_basis_history';

    protected $fillable = [
        'consent_record_id',
        'previous_legal_basis',
        'new_legal_basis',
        'justification',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function consentRecord(): BelongsTo
    {
        return $this->belongsTo(ConsentRecordModel::class, 'consent_record_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
