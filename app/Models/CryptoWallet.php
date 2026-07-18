<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CryptoWallet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'currency',
        'network',
        'wallet_address',
        'label',
        'qr_code_url',
        'instructions',
        'confirmations_required',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'confirmations_required' => 'integer',
        'display_order' => 'integer',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function getDisplayLabelAttribute(): string
    {
        return $this->label ?: "{$this->currency} ({$this->network})";
    }
}
