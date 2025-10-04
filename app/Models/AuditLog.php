<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'resource',
        'resource_id',
        'ip_address',
        'user_agent',
        'data_before',
        'data_after',
        'context',
    ];

    protected $casts = [
        'data_before' => 'array',
        'data_after' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getResourceModelAttribute(): ?Model
    {
        if (!$this->resource || !$this->resource_id) {
            return null;
        }

        $resourceClass = "App\\Models\\{$this->resource}";

        if (!class_exists($resourceClass)) {
            return null;
        }

        return $resourceClass::find($this->resource_id);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForResource($query, $resource, $resourceId = null)
    {
        $query = $query->where('resource', $resource);

        if ($resourceId !== null) {
            $query->where('resource_id', $resourceId);
        }

        return $query;
    }

    public function scopeForAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public static function logAction(string $action, string $resource, $resourceId = null, array $dataBefore = null, array $dataAfter = null, string $context = null): void
    {
        if (!auth()->check()) {
            return;
        }

        $request = request();

        self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'resource' => class_basename($resource),
            'resource_id' => $resourceId,
            'ip_address' => $request?->ip() ?? '127.0.0.1',
            'user_agent' => $request?->userAgent(),
            'data_before' => $dataBefore,
            'data_after' => $dataAfter,
            'context' => $context,
        ]);
    }
}
