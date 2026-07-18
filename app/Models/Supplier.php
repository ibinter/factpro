<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fournisseur (cahier IBIG §10.1) — répertoire des tiers d'achat d'une société.
 */
class Supplier extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Factures d'achat émises par ce fournisseur. */
    public function invoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }
}
