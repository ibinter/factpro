<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use SoftDeletes;

    /** Types de documents (cahier des charges §4) */
    public const TYPES = [
        'quote'             => ['label' => 'Devis',                'prefix' => 'DEV'],
        'proforma'          => ['label' => 'Facture Proforma',     'prefix' => 'PRO'],
        'sales_order'       => ['label' => 'Bon de Commande',      'prefix' => 'BC'],
        'purchase_order'    => ['label' => 'Commande Fournisseur', 'prefix' => 'BCF'],
        'delivery_note'     => ['label' => 'Bon de Livraison',     'prefix' => 'BL'],
        'invoice'           => ['label' => 'Facture',              'prefix' => 'FAC'],
        'credit_note'       => ['label' => 'Avoir',                'prefix' => 'AV'],
        'payment_receipt'   => ['label' => 'Reçu de Paiement',     'prefix' => 'REC'],
        'deposit_invoice'   => ['label' => "Facture d'Acompte",    'prefix' => 'FA'],
        'balance_invoice'   => ['label' => 'Facture de Solde',     'prefix' => 'FS'],
        'work_order'        => ['label' => 'Bon de Travaux',       'prefix' => 'BT'],
        'pos_ticket'        => ['label' => 'Ticket de Caisse',     'prefix' => 'TK'],
        'quittance'         => ['label' => 'Quittance',            'prefix' => 'QIT'],
        'rma'               => ['label' => 'Bon de Retour RMA',    'prefix' => 'RMA'],
        'remittance'        => ['label' => 'Bordereau de Remise',  'prefix' => 'BDR'],
    ];

    public const STATUSES = [
        'draft', 'sent', 'viewed', 'accepted', 'rejected',
        'partial', 'paid', 'overdue', 'cancelled', 'converted',
    ];

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'finalized_at' => 'datetime',
        'sent_at' => 'datetime',
        'trial_watermark' => 'boolean',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Document $document) {
            $document->uuid ??= (string) Str::uuid();
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Document::class, 'parent_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(DocumentLine::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DocumentPayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class)->orderBy('step_number');
    }

    public function approvalWorkflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function getBalanceDueAttribute(): float
    {
        return round((float) $this->total - (float) $this->amount_paid, 2);
    }

    public function verificationUrl(): string
    {
        return rtrim(config('factpro.verify_base_url'), '/').'/'.$this->uuid;
    }

    public function isFinalized(): bool
    {
        return $this->finalized_at !== null;
    }

    public function archive(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\DocumentArchive::class);
    }
}
