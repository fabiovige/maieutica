<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LgpdDataRequest extends Model
{
    protected $fillable = [
        'user_id',
        'request_type',
        'status',
        'description',
        'requested_data',
        'response_data',
        'requested_at',
        'processed_at',
        'processed_by',
        'notes',
    ];

    protected $casts = [
        'requested_data' => 'array',
        'response_data' => 'array',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('request_type', $type);
    }

    public function markAsProcessing(): bool
    {
        return $this->update(['status' => 'processing']);
    }

    public function complete(array $responseData, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'completed',
            'response_data' => $responseData,
            'processed_at' => now(),
            'notes' => $notes,
        ]);
    }

    public function reject(string $notes): bool
    {
        return $this->update([
            'status' => 'rejected',
            'processed_at' => now(),
            'notes' => $notes,
        ]);
    }
}