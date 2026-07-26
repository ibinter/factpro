<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Clé d'activation formule — générée en lot par l'admin, saisie par le client
 * pour activer ou renouveler sa licence (cahier §19.6).
 */
class ActivationKey extends Model
{
    protected $guarded = [];

    protected $casts = [
        'used_at'    => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /** Société pour laquelle la clé a été spécifiquement réservée (nullable). */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Société qui a utilisé la clé. */
    public function usedByCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'used_by_company_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where(fn ($s) => $s->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function isUsable(): bool
    {
        return $this->status === 'available'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
