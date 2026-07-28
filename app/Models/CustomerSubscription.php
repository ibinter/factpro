<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerSubscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'name',
        'description',
        'frequency',
        'amount',
        'currency',
        'tax_rate',
        'start_date',
        'end_date',
        'next_billing_date',
        'status',
        'auto_generate_invoice',
        'payment_terms',
        'notes',
        'last_billed_at',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'start_date'          => 'date',
        'end_date'            => 'date',
        'next_billing_date'   => 'date',
        'amount'              => 'decimal:2',
        'tax_rate'            => 'decimal:2',
        'auto_generate_invoice' => 'boolean',
        'last_billed_at'      => 'datetime',
        'cancelled_at'        => 'datetime',
    ];

    // ── Relations ────────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class, 'subscription_id');
    }

    // ── Labels ───────────────────────────────────────────────────────────────

    public function frequencyLabel(): string
    {
        return [
            'weekly'   => 'Hebdomadaire',
            'monthly'  => 'Mensuel',
            'quarterly' => 'Trimestriel',
            'biannual' => 'Semestriel',
            'annual'   => 'Annuel',
        ][$this->frequency] ?? $this->frequency;
    }

    public function statusLabel(): string
    {
        return [
            'active'    => 'Actif',
            'paused'    => 'Pausé',
            'cancelled' => 'Annulé',
            'expired'   => 'Expiré',
        ][$this->status] ?? $this->status;
    }

    // ── Business logic ───────────────────────────────────────────────────────

    public function calculateNextBillingDate(): Carbon
    {
        $from = $this->last_billed_at
            ? Carbon::parse($this->last_billed_at)
            : Carbon::parse($this->start_date);

        return match ($this->frequency) {
            'weekly'    => $from->copy()->addWeek(),
            'monthly'   => $from->copy()->addMonth(),
            'quarterly' => $from->copy()->addMonths(3),
            'biannual'  => $from->copy()->addMonths(6),
            'annual'    => $from->copy()->addYear(),
            default     => $from->copy()->addMonth(),
        };
    }

    public function generateInvoice(): Document
    {
        $company = $this->company;
        $total   = round((float) $this->amount * (1 + (float) $this->tax_rate / 100), 2);

        $document = Document::create([
            'company_id'   => $this->company_id,
            'customer_id'  => $this->customer_id,
            'type'         => 'invoice',
            'number'       => app(\App\Services\DocumentNumberService::class)->next($company, 'invoice'),
            'status'       => 'draft',
            'issue_date'   => today(),
            'due_date'     => today()->addDays($this->payment_terms),
            'currency'     => $this->currency,
            'subtotal'     => $this->amount,
            'tax_amount'   => round((float) $this->amount * (float) $this->tax_rate / 100, 2),
            'discount_amount' => 0,
            'total'        => $total,
            'notes'        => 'Abonnement : ' . $this->name,
        ]);

        $document->lines()->create([
            'description'        => $this->name,
            'quantity'           => 1,
            'unit'               => 'forfait',
            'unit_price'         => $this->amount,
            'tax_rate'           => $this->tax_rate,
            'line_discount_type' => 'percent',
            'discount_percent'   => 0,
            'line_total'         => $this->amount,
            'sort_order'         => 0,
        ]);

        return $document;
    }
}
