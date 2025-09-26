<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LgpdConsent extends Model
{
    protected $fillable = [
        'user_id',
        'consent_type',
        'purposes',
        'granted',
        'granted_at',
        'revoked_at',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'purposes' => 'array',
        'metadata' => 'array',
        'granted' => 'boolean',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('granted', true)->whereNull('revoked_at');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('consent_type', $type);
    }

    public function isActive(): bool
    {
        return $this->granted && is_null($this->revoked_at);
    }

    public function revoke(): bool
    {
        return $this->update([
            'granted' => false,
            'revoked_at' => now(),
        ]);
    }
}