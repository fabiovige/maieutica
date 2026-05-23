<?php

namespace App\Modules\Lgpd\Infrastructure\Models;

use App\Models\User;
use App\Modules\Lgpd\Domain\ValueObjects\ConsentStatus;
use App\Modules\Lgpd\Domain\ValueObjects\LegalBasis;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsentRecordModel extends Model
{
    protected $table = 'lgpd_consent_records';

    protected $fillable = [
        'subject_id',
        'subject_type',
        'purpose',
        'legal_basis',
        'term_version',
        'status',
        'collected_at',
        'revoked_at',
        'collected_by',
        'revoked_by',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'revoked_at' => 'datetime',
        'term_version' => 'integer',
        'status' => ConsentStatus::class,
        'legal_basis' => LegalBasis::class,
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    public function legalBasisHistory(): HasMany
    {
        return $this->hasMany(ConsentLegalBasisHistoryModel::class, 'consent_record_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeRevoked($query)
    {
        return $query->where('status', 'revogado');
    }

    public function scopeForSubject($query, int $subjectId, string $subjectType)
    {
        return $query->where('subject_id', $subjectId)
            ->where('subject_type', $subjectType);
    }
}
