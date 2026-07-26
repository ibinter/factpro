<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppVersion extends Model
{
    protected $fillable = [
        'version',
        'title',
        'description',
        'type',
        'target_plans',
        'published_at',
        'notification_sent_at',
        'published_by',
    ];

    protected $casts = [
        'target_plans'          => 'array',
        'published_at'          => 'datetime',
        'notification_sent_at'  => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Returns true if this version targets the given plan
     * (null target_plans means it targets all plans).
     */
    public function isForPlan(?int $planId): bool
    {
        if (is_null($this->target_plans)) {
            return true;
        }

        return in_array($planId, $this->target_plans);
    }

    public function isPublished(): bool
    {
        return ! is_null($this->published_at) && $this->published_at->lte(now());
    }
}
