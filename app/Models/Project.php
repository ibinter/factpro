<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Projet client (cahier §9) — budget heures/montant, taux horaire par défaut,
 * saisie des heures et facturation du temps passé.
 */
class Project extends Model
{
    use SoftDeletes;

    public const STATUSES = ['active', 'paused', 'completed', 'archived'];

    protected $guarded = [];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'budget_hours' => 'decimal:2',
        'budget_amount' => 'decimal:2',
        'budget_alert_sent_at' => 'datetime',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /** Alias utilisé dans ProjectBillingService pour la cohérence avec l'API. */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    /** Total des minutes saisies sur le projet (utilise l'alias withSum s'il est présent). */
    public function getTotalMinutesAttribute($value = null): int
    {
        if ($value !== null) {
            return (int) $value; // alias withSum('entries as total_minutes', ...)
        }

        return (int) ($this->relationLoaded('entries')
            ? $this->entries->sum('duration_minutes')
            : $this->entries()->sum('duration_minutes'));
    }

    /** Montant facturable total : Σ durée/60 × taux effectif (entrées facturables). */
    public function getTotalBillableAmountAttribute(): float
    {
        $entries = $this->relationLoaded('entries') ? $this->entries : $this->entries()->get();

        return round(
            $entries
                ->where('is_billable', true)
                ->sum(fn (TimeEntry $entry) => $entry->duration_minutes / 60
                    * (float) ($entry->hourly_rate ?? $this->hourly_rate ?? 0)),
            2
        );
    }
}
