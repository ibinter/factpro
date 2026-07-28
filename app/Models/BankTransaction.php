<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankTransaction extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
        'value_date' => 'date',
        'matched_at' => 'datetime',
        'amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function matchedPayment(): BelongsTo
    {
        return $this->belongsTo(DocumentPayment::class, 'matched_payment_id');
    }

    public function scopeUnreconciled(Builder $q): void
    {
        $q->where('is_reconciled', false);
    }

    public function scopeReconciled(Builder $q): void
    {
        $q->where('is_reconciled', true);
    }
}
