<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Note de frais (cahier §3 NDF) — remboursements collaborateurs
 * avec justificatif photo/PDF. Réservé BUSINESS/ENTERPRISE (§22.1).
 */
class Expense extends Model
{
    use SoftDeletes;

    /** Catégories de dépense (slug => libellé FR). */
    public const CATEGORIES = [
        'transport' => 'Transport',
        'repas' => 'Repas & restauration',
        'hebergement' => 'Hébergement',
        'fournitures' => 'Fournitures',
        'carburant' => 'Carburant',
        'communication' => 'Communication',
        'autre' => 'Autre',
    ];

    /** Cycle de vie : soumission → approbation/rejet → remboursement. */
    public const STATUSES = ['draft', 'submitted', 'approved', 'rejected', 'reimbursed'];

    /** Statuts encore modifiables par le déclarant. */
    public const EDITABLE_STATUSES = ['draft', 'submitted', 'rejected'];

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'reviewed_at' => 'datetime',
        'reimbursed_at' => 'date',
    ];

    /** Déclarant de la dépense. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Approbateur (owner/admin) ayant validé ou rejeté. */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
