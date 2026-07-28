<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'category',
        'parent_id',
        'is_active',
        'is_system',
        'balance',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'balance' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }

    public function getTypeLabel(): string
    {
        return [
            'asset'     => 'Actif',
            'liability' => 'Passif',
            'equity'    => 'Capitaux propres',
            'revenue'   => 'Produits',
            'expense'   => 'Charges',
        ][$this->type] ?? $this->type;
    }

    /**
     * Returns true if debit increases the account balance (normal debit accounts).
     */
    public function getDebitNormal(): bool
    {
        return in_array($this->type, ['asset', 'expense'], true);
    }
}
