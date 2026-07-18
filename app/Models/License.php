<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model
{
    use HasUuids;

    public const STATUSES = [
        'trial', 'pending', 'provisional', 'active', 'grace_period',
        'suspended', 'expired', 'terminated', 'revoked',
    ];

    protected $guarded = [];

    protected $casts = [
        'limits' => 'array',
        'metadata' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isUsable(): bool
    {
        return in_array($this->status, ['trial', 'provisional', 'active', 'grace_period'])
            && $this->effectiveEndsAt()->isFuture();
    }

    public function isTrial(): bool
    {
        return $this->type === 'trial' && $this->status === 'trial';
    }

    public function effectiveEndsAt(): \Illuminate\Support\Carbon
    {
        return $this->grace_period_ends_at ?? $this->ends_at;
    }

    public function daysRemaining(): int
    {
        return max(0, (int) now()->diffInDays($this->effectiveEndsAt(), false));
    }

    public function limit(string $key): ?int
    {
        $value = $this->limits[$key] ?? null;

        return $value === null || $value === 'unlimited' ? null : (int) $value;
    }
}
