<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentTransaction extends Model
{
    use HasUuids;

    public const STATUSES = [
        'initiated', 'pending', 'processing', 'succeeded', 'failed',
        'cancelled', 'expired', 'under_review', 'manually_validated',
        'rejected', 'refunded', 'disputed',
    ];

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'initiated_at' => 'datetime',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'amount_expected' => 'decimal:2',
        'amount_declared' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'fee_amount' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class, 'transaction_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
