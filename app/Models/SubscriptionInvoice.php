<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'subscription_id',
        'document_id',
        'billing_date',
        'amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'billing_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(CustomerSubscription::class, 'subscription_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
