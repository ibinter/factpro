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

    /** Catégories de documents */
    public const CATEGORIES = [
        'vente'         => 'Vente & Facturation',
        'achats'        => 'Achats & Fournisseurs',
        'stocks'        => 'Stocks & Inventaire',
        'sav'           => 'SAV & Maintenance',
        'btp'           => 'BTP & Travaux',
        'logistique'    => 'Logistique & Transport',
        'finance'       => 'Finance & Trésorerie',
        'rh'            => 'Ressources Humaines',
        'administratif' => 'Administratif & Juridique',
        'immobilier'    => 'Immobilier & Location',
        'export'        => 'Export & Douane',
        'sante'         => 'Santé & Médical',
        'education'     => 'Éducation & Formation',
    ];

    /** Moteur de documents universel — 80+ types en 13 catégories */
    public const TYPES = [
        /* ── VENTE & FACTURATION ──────────────────────────────────────── */
        'quote'                  => ['label' => 'Devis',                       'prefix' => 'DEV',  'category' => 'vente'],
        'commercial_offer'       => ['label' => 'Offre Commerciale',           'prefix' => 'OFC',  'category' => 'vente'],
        'commercial_proposal'    => ['label' => 'Proposition Commerciale',     'prefix' => 'PPC',  'category' => 'vente'],
        'proforma'               => ['label' => 'Facture Proforma',            'prefix' => 'PRO',  'category' => 'vente'],
        'sales_order'            => ['label' => 'Bon de Commande Client',      'prefix' => 'BC',   'category' => 'vente'],
        'reservation_order'      => ['label' => 'Bon de Réservation',          'prefix' => 'BRE',  'category' => 'vente'],
        'picking_order'          => ['label' => 'Bon de Préparation',          'prefix' => 'BPR',  'category' => 'vente'],
        'delivery_note'          => ['label' => 'Bon de Livraison',            'prefix' => 'BL',   'category' => 'vente'],
        'dispatch_order'         => ['label' => 'Ordre de Livraison',          'prefix' => 'ODL',  'category' => 'vente'],
        'invoice'                => ['label' => 'Facture',                     'prefix' => 'FAC',  'category' => 'vente'],
        'simple_invoice'         => ['label' => 'Facture Simplifiée',          'prefix' => 'FSI',  'category' => 'vente'],
        'export_invoice'         => ['label' => 'Facture Export',              'prefix' => 'FEXP', 'category' => 'vente'],
        'tax_exempt_invoice'     => ['label' => 'Facture Exonérée TVA',        'prefix' => 'FETV', 'category' => 'vente'],
        'rectification_invoice'  => ['label' => 'Facture Rectificative',       'prefix' => 'FREC', 'category' => 'vente'],
        'deposit_invoice'        => ['label' => "Facture d'Acompte",           'prefix' => 'FA',   'category' => 'vente'],
        'balance_invoice'        => ['label' => 'Facture de Solde',            'prefix' => 'FSO',  'category' => 'vente'],
        'credit_note'            => ['label' => 'Avoir',                       'prefix' => 'AV',   'category' => 'vente'],
        'payment_receipt'        => ['label' => 'Reçu de Paiement',            'prefix' => 'REC',  'category' => 'vente'],
        'pos_ticket'             => ['label' => 'Ticket de Caisse',            'prefix' => 'TK',   'category' => 'vente'],
        'quittance'              => ['label' => 'Quittance de Loyer',          'prefix' => 'QIT',  'category' => 'vente'],
        'stock_exit_sale'        => ['label' => 'Bon de Sortie (Vente)',       'prefix' => 'BSV',  'category' => 'vente'],
        'commercial_contract'    => ['label' => 'Contrat Commercial',          'prefix' => 'CCO',  'category' => 'vente'],

        /* ── ACHATS & FOURNISSEURS ────────────────────────────────────── */
        'purchase_request'       => ['label' => "Demande d'Achat",             'prefix' => 'DA',   'category' => 'achats'],
        'price_request'          => ['label' => 'Demande de Prix',             'prefix' => 'DP',   'category' => 'achats'],
        'supplier_consultation'  => ['label' => 'Consultation Fournisseur',    'prefix' => 'CF',   'category' => 'achats'],
        'purchase_order'         => ['label' => 'Bon de Commande Fournisseur', 'prefix' => 'BCF',  'category' => 'achats'],
        'goods_receipt'          => ['label' => 'Bon de Réception',            'prefix' => 'BR',   'category' => 'achats'],
        'supplier_invoice'       => ['label' => 'Facture Fournisseur',         'prefix' => 'FF',   'category' => 'achats'],
        'supplier_credit'        => ['label' => 'Avoir Fournisseur',           'prefix' => 'AVF',  'category' => 'achats'],
        'supplier_return'        => ['label' => 'Bon de Retour Fournisseur',   'prefix' => 'RTF',  'category' => 'achats'],
        'debit_note'             => ['label' => 'Note de Débit',               'prefix' => 'ND',   'category' => 'achats'],
        'supplier_credit_note'   => ['label' => 'Note de Crédit Fournisseur',  'prefix' => 'NCF',  'category' => 'achats'],

        /* ── STOCKS & INVENTAIRE ─────────────────────────────────────── */
        'stock_entry'            => ['label' => "Bon d'Entrée de Stock",       'prefix' => 'BE',   'category' => 'stocks'],
        'stock_exit'             => ['label' => 'Bon de Sortie de Stock',      'prefix' => 'BSS',  'category' => 'stocks'],
        'stock_transfer'         => ['label' => 'Bon de Transfert',            'prefix' => 'BTR',  'category' => 'stocks'],
        'stock_consumption'      => ['label' => 'Bon de Consommation',         'prefix' => 'BCO',  'category' => 'stocks'],
        'inventory'              => ['label' => "Bon d'Inventaire",            'prefix' => 'INV',  'category' => 'stocks'],
        'stock_adjustment'       => ['label' => 'Ajustement de Stock',         'prefix' => 'AJS',  'category' => 'stocks'],
        'destruction_note'       => ['label' => 'Bon de Destruction / Casse',  'prefix' => 'BDE',  'category' => 'stocks'],
        'manufacturing_order'    => ['label' => 'Ordre de Fabrication',        'prefix' => 'OF',   'category' => 'stocks'],
        'transformation_note'    => ['label' => 'Bon de Transformation',       'prefix' => 'BTF',  'category' => 'stocks'],

        /* ── SAV & MAINTENANCE ───────────────────────────────────────── */
        'rma'                    => ['label' => 'Bon de Retour RMA',           'prefix' => 'RMA',  'category' => 'sav'],
        'sav_sheet'              => ['label' => 'Fiche SAV',                   'prefix' => 'SAV',  'category' => 'sav'],
        'repair_order'           => ['label' => 'Bon de Réparation',           'prefix' => 'BRP',  'category' => 'sav'],
        'intervention_report'    => ['label' => "Rapport d'Intervention",      'prefix' => 'RI',   'category' => 'sav'],
        'maintenance_order'      => ['label' => 'Bon de Maintenance',          'prefix' => 'BM',   'category' => 'sav'],
        'warranty_certificate'   => ['label' => 'Certificat de Garantie',      'prefix' => 'GAR',  'category' => 'sav'],
        'maintenance_contract'   => ['label' => 'Contrat de Maintenance',      'prefix' => 'CM',   'category' => 'sav'],

        /* ── BTP & TRAVAUX ───────────────────────────────────────────── */
        'work_order'             => ['label' => 'Bon de Travaux',              'prefix' => 'BT',   'category' => 'btp'],
        'service_order'          => ['label' => 'Ordre de Service',            'prefix' => 'OS',   'category' => 'btp'],
        'progress_statement'     => ['label' => 'Situation de Travaux',        'prefix' => 'ST',   'category' => 'btp'],
        'provisional_account'    => ['label' => 'Décompte Provisoire',         'prefix' => 'DCP',  'category' => 'btp'],
        'final_account'          => ['label' => 'Décompte Définitif',          'prefix' => 'DCD',  'category' => 'btp'],
        'provisional_acceptance' => ['label' => 'PV Réception Provisoire',     'prefix' => 'PVP',  'category' => 'btp'],
        'final_acceptance'       => ['label' => 'PV Réception Définitive',     'prefix' => 'PVD',  'category' => 'btp'],
        'site_report'            => ['label' => 'Rapport de Chantier',         'prefix' => 'RCH',  'category' => 'btp'],

        /* ── LOGISTIQUE & TRANSPORT ──────────────────────────────────── */
        'shipping_note'          => ['label' => "Bon d'Expédition",            'prefix' => 'BEX',  'category' => 'logistique'],
        'waybill'                => ['label' => 'Lettre de Voiture',           'prefix' => 'LV',   'category' => 'logistique'],
        'packing_list'           => ['label' => 'Packing List',                'prefix' => 'PL',   'category' => 'logistique'],
        'loading_slip'           => ['label' => 'Bordereau de Chargement',     'prefix' => 'BCH',  'category' => 'logistique'],
        'inter_depot_transfer'   => ['label' => 'Transfert Inter-Dépôts',      'prefix' => 'TID',  'category' => 'logistique'],
        'delivery_manifest'      => ['label' => 'Manifeste de Livraison',      'prefix' => 'MNF',  'category' => 'logistique'],

        /* ── FINANCE & TRÉSORERIE ────────────────────────────────────── */
        'remittance'             => ['label' => 'Bordereau de Remise',         'prefix' => 'BDR',  'category' => 'finance'],
        'expense_report'         => ['label' => 'Note de Frais',               'prefix' => 'NF',   'category' => 'finance'],
        'cash_voucher'           => ['label' => 'Bon de Caisse',               'prefix' => 'BCA',  'category' => 'finance'],
        'bank_deposit'           => ['label' => 'Bordereau de Dépôt Bancaire', 'prefix' => 'DB',   'category' => 'finance'],
        'bank_withdrawal'        => ['label' => 'Bon de Retrait Bancaire',     'prefix' => 'RB',   'category' => 'finance'],
        'bill_of_exchange'       => ['label' => 'Effet de Commerce / Traite',  'prefix' => 'EC',   'category' => 'finance'],
        'promissory_note'        => ['label' => 'Billet à Ordre',              'prefix' => 'BO',   'category' => 'finance'],

        /* ── RESSOURCES HUMAINES ─────────────────────────────────────── */
        'mission_order'          => ['label' => 'Ordre de Mission',            'prefix' => 'OM',   'category' => 'rh'],
        'leave_request'          => ['label' => 'Demande de Congé',            'prefix' => 'DC',   'category' => 'rh'],
        'payslip'                => ['label' => 'Bulletin de Paie',            'prefix' => 'BP',   'category' => 'rh'],
        'salary_advance'         => ['label' => 'Avance sur Salaire',          'prefix' => 'AS',   'category' => 'rh'],
        'service_note'           => ['label' => 'Note de Service',             'prefix' => 'NS',   'category' => 'rh'],
        'absence_authorization'  => ['label' => "Autorisation d'Absence",      'prefix' => 'AA',   'category' => 'rh'],

        /* ── ADMINISTRATIF & JURIDIQUE ───────────────────────────────── */
        'contract'               => ['label' => 'Contrat',                     'prefix' => 'CTR',  'category' => 'administratif'],
        'minutes'                => ['label' => 'Procès-Verbal',               'prefix' => 'PV',   'category' => 'administratif'],
        'attestation'            => ['label' => 'Attestation',                 'prefix' => 'ATT',  'category' => 'administratif'],
        'certificate'            => ['label' => 'Certificat',                  'prefix' => 'CER',  'category' => 'administratif'],
        'reminder_letter'        => ['label' => 'Lettre de Relance',           'prefix' => 'LR',   'category' => 'administratif'],
        'formal_notice'          => ['label' => 'Mise en Demeure',             'prefix' => 'MED',  'category' => 'administratif'],
        'acknowledgement'        => ['label' => "Accusé de Réception",         'prefix' => 'AR',   'category' => 'administratif'],
        'authorization'          => ['label' => 'Autorisation',                'prefix' => 'AUT',  'category' => 'administratif'],

        /* ── IMMOBILIER & LOCATION ───────────────────────────────────── */
        'lease_contract'         => ['label' => 'Contrat de Bail',             'prefix' => 'CBL',  'category' => 'immobilier'],
        'entry_inventory'        => ['label' => "État des Lieux d'Entrée",     'prefix' => 'ELE',  'category' => 'immobilier'],
        'exit_inventory'         => ['label' => "État des Lieux de Sortie",    'prefix' => 'ELS',  'category' => 'immobilier'],
        'rent_notice'            => ['label' => 'Appel de Loyer',              'prefix' => 'AL',   'category' => 'immobilier'],
        'deposit_receipt'        => ['label' => 'Reçu de Caution',             'prefix' => 'RCA',  'category' => 'immobilier'],

        /* ── EXPORT & DOUANE ─────────────────────────────────────────── */
        'origin_certificate'     => ['label' => "Certificat d'Origine",        'prefix' => 'CO',   'category' => 'export'],
        'customs_declaration'    => ['label' => 'Déclaration Douanière',       'prefix' => 'DD',   'category' => 'export'],
        'boarding_pass_doc'      => ['label' => "Bon d'Embarquement",          'prefix' => 'BEM',  'category' => 'export'],
        'export_invoice_custom'  => ['label' => 'Facture Export (Douane)',      'prefix' => 'FEXD', 'category' => 'export'],

        /* ── SANTÉ & MÉDICAL ─────────────────────────────────────────── */
        'medical_invoice'        => ['label' => 'Facture Médicale',            'prefix' => 'FM',   'category' => 'sante'],
        'prescription'           => ['label' => 'Ordonnance',                  'prefix' => 'ORD',  'category' => 'sante'],
        'lab_order'              => ['label' => 'Bon de Laboratoire',          'prefix' => 'LAB',  'category' => 'sante'],
        'care_sheet'             => ['label' => 'Feuille de Soins',            'prefix' => 'FS',   'category' => 'sante'],

        /* ── ÉDUCATION & FORMATION ───────────────────────────────────── */
        'school_receipt'         => ['label' => 'Reçu de Scolarité',           'prefix' => 'RS',   'category' => 'education'],
        'training_invoice'       => ['label' => 'Facture de Formation',        'prefix' => 'FRF',  'category' => 'education'],
        'payment_attestation'    => ['label' => 'Attestation de Paiement',     'prefix' => 'AP',   'category' => 'education'],
        'report_card'            => ['label' => 'Bulletin de Notes',           'prefix' => 'BN',   'category' => 'education'],
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
        'meta'        => 'array',
    ];

    public function getCategoryLabelAttribute(): string
    {
        $cat = self::TYPES[$this->type]['category'] ?? 'vente';
        return self::CATEGORIES[$cat] ?? $cat;
    }

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
