<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Échéance d'un plan de paiement (cahier IBIG §12).
 * Chaque échéance peut se matérialiser en facture d'acompte/de solde.
 */
class PaymentPlanInstallment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class, 'payment_plan_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
