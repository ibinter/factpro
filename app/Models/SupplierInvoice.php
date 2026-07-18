<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Facture d'achat fournisseur (cahier IBIG §10.1 « Journal des achats ») —
 * HT/TVA/TTC, échéance, statut de paiement, justificatif privé.
 */
class SupplierInvoice extends Model
{
    use SoftDeletes;

    /** Catégories de charge (slug => libellé FR). */
    public const CATEGORIES = [
        'marchandises' => 'Marchandises',
        'services' => 'Services',
        'fournitures' => 'Fournitures',
        'loyer' => 'Loyer',
        'energie' => 'Énergie',
        'transport' => 'Transport',
        'autre' => 'Autre',
    ];

    /** Cycle de paiement. */
    public const STATUSES = ['unpaid', 'partial', 'paid'];

    protected $guarded = [];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'date',
        'amount_ht' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'amount_ttc' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected $appends = ['balance_due'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /** Auteur de la saisie. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Reste à payer (TTC − déjà réglé), plancher à 0. */
    public function getBalanceDueAttribute(): float
    {
        return round(max((float) $this->amount_ttc - (float) $this->amount_paid, 0), 2);
    }
}
