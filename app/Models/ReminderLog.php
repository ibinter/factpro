<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trace d'une relance envoyée pour une facture impayée
 * (module Relances intelligentes — cahier des charges §13).
 */
class ReminderLog extends Model
{
    /** Libellés des 3 niveaux d'escalade */
    public const LEVEL_LABELS = [
        1 => 'Rappel courtois',
        2 => 'Relance ferme',
        3 => 'Mise en demeure',
    ];

    protected $guarded = [];

    protected $casts = [
        'level' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function getLevelLabelAttribute(): string
    {
        return self::LEVEL_LABELS[$this->level] ?? "Niveau {$this->level}";
    }
}
