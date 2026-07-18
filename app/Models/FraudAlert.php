<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FraudAlert extends Model
{
    protected $guarded = [];

    protected $casts = [
        'flags'       => 'array',
        'reviewed_at' => 'datetime',
    ];

    public const STATUSES = ['open', 'reviewed', 'dismissed', 'confirmed_fraud'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
