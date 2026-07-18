<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasUuids;

    public const STATUSES = [
        'draft', 'pending_payment', 'payment_initiated', 'proof_submitted',
        'under_review', 'missing_info', 'awaiting_delivery', 'paid', 'expired',
        'cancelled', 'rejected', 'refunded',
    ];

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function deliveryOrder(): HasOne
    {
        return $this->hasOne(DeliveryOrder::class);
    }

    public function isPayable(): bool
    {
        return in_array($this->status, ['pending_payment', 'payment_initiated', 'missing_info'])
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
