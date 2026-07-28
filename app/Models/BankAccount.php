<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $guarded = [];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function reconciledTransactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class)->where('is_reconciled', true);
    }

    public function getReconciledCountAttribute(): int
    {
        return $this->transactions()->where('is_reconciled', true)->count();
    }

    public function getPendingCountAttribute(): int
    {
        return $this->transactions()->where('is_reconciled', false)->count();
    }
}
