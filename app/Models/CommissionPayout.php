<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Décompte de commission (cahier IBIG §3 CMD) — commission d'un vendeur sur une
 * période, calculée sur le CA commissionnable, avec suivi « commission payée ».
 */
class CommissionPayout extends Model
{
    /** Cycle de règlement. */
    public const STATUSES = ['pending', 'paid'];

    protected $guarded = [];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'date',
        'base_amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(SalesAgent::class, 'sales_agent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
