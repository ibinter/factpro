<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Gabarit de facture récurrente (abonnements automatiques — cahier §3).
 */
class RecurringTemplate extends Model
{
    use SoftDeletes;

    /** Fréquences supportées → nombre de mois par période (null = hebdomadaire). */
    public const FREQUENCIES = [
        'weekly' => null,
        'monthly' => 1,
        'quarterly' => 3,
        'semiannual' => 6,
        'yearly' => 12,
    ];

    protected $guarded = [];

    protected $casts = [
        'lines' => 'array',
        'next_run_date' => 'date',
        'last_run_date' => 'date',
        'end_date' => 'date',
        'interval' => 'integer',
        'day_of_month' => 'integer',
        'occurrences_limit' => 'integer',
        'occurrences_done' => 'integer',
        'due_days' => 'integer',
        'auto_finalize' => 'boolean',
        'auto_send' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Prochaine date d'émission après $from : ajoute interval × fréquence.
     * Si day_of_month est défini (fréquences mensuelles et plus), cale au jour
     * demandé du mois cible — min(jour, fin de mois) pour les mois courts.
     */
    public function computeNextRunDate(Carbon $from): Carbon
    {
        $interval = max(1, (int) $this->interval);
        $next = $from->copy()->startOfDay();

        if ($this->frequency === 'weekly') {
            return $next->addWeeks($interval);
        }

        $months = (self::FREQUENCIES[$this->frequency] ?? 1) * $interval;
        $next = $next->addMonthsNoOverflow($months);

        if ($this->day_of_month !== null) {
            $next->day(min((int) $this->day_of_month, $next->daysInMonth));
        }

        return $next;
    }
}
