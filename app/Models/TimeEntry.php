<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Entrée de temps (cahier §9) — saisie manuelle ou chronomètre.
 * duration_minutes est la source de vérité de la durée.
 */
class TimeEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'entry_date' => 'date',
        'duration_minutes' => 'integer',
        'hourly_rate' => 'decimal:2',
        'is_billable' => 'boolean',
        'is_billed' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** Taux horaire effectif : celui de l'entrée, sinon celui du projet. */
    public function getEffectiveRateAttribute(): float
    {
        return (float) ($this->hourly_rate ?? $this->project?->hourly_rate ?? 0);
    }

    /** Montant de l'entrée : durée/60 × taux effectif, arrondi à 2 décimales. */
    public function getAmountAttribute(): float
    {
        return round($this->duration_minutes / 60 * $this->effective_rate, 2);
    }
}
