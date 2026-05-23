<?php

namespace App\Modules\Lgpd\Infrastructure\Models;

use App\Models\User;
use App\Modules\Lgpd\Domain\ValueObjects\DataCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetentionPolicyModel extends Model
{
    protected $table = 'lgpd_retention_policies';

    protected $fillable = [
        'category',
        'retention_days',
        'expiration_action',
        'legal_minimum_days',
        'legal_reference',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'retention_days' => 'integer',
        'legal_minimum_days' => 'integer',
        'category' => DataCategory::class,
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
