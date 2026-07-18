<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Vendeur / commercial (cahier IBIG §3 CMD) — porte un taux de commission par
 * défaut et se voit affecter des clients (customers.sales_agent_id).
 */
class SalesAgent extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Clients affectés à ce vendeur. */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /** Décomptes de commission générés pour ce vendeur. */
    public function payouts(): HasMany
    {
        return $this->hasMany(CommissionPayout::class);
    }
}
