<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Plan de paiement échelonné (cahier IBIG §12).
 * Décompose le total d'un document source en échéances datées.
 */
class PaymentPlan extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'source_document_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PaymentPlanInstallment::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Montant déjà facturé (échéances émises ou payées). */
    public function getTotalInvoicedAttribute(): float
    {
        return round($this->installments
            ->whereIn('status', ['invoiced', 'paid'])
            ->sum(fn (PaymentPlanInstallment $i) => (float) $i->amount), 2);
    }

    /** Reste à facturer. */
    public function getRemainingAttribute(): float
    {
        return round((float) $this->total_amount - $this->total_invoiced, 2);
    }
}
