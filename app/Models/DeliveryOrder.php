<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryOrder extends Model
{
    protected $guarded = [];

    protected $casts = [
        'cod_amount'            => 'decimal:2',
        'amount_received'       => 'decimal:2',
        'assigned_at'           => 'datetime',
        'delivered_at'          => 'datetime',
        'payment_confirmed_at'  => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(DeliveryAgent::class, 'delivery_agent_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
