<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Méthode de paiement manuel configurée par le superadmin.
 * Stockée dans payment_method_configs (table partagée).
 */
class ManualPaymentMethod extends Model
{
    use SoftDeletes;

    protected $table = 'payment_method_configs';

    protected $guarded = [];

    protected $casts = [
        'allowed_plan_ids' => 'array',
        'metadata'         => 'array',
        'is_active'        => 'boolean',
        'min_amount'       => 'decimal:2',
        'max_amount'       => 'decimal:2',
    ];

    // ---------------------------------------------------------------------------
    // Scopes
    // ---------------------------------------------------------------------------

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForCountry($query, string $country)
    {
        return $query->where(fn ($s) => $s->where('country', $country)->orWhereNull('country'));
    }

    // ---------------------------------------------------------------------------
    // Accesseurs de compatibilité (les colonnes historiques ont des noms différents)
    // ---------------------------------------------------------------------------

    /** Nom affiché : préfère `label` si `name` n'est pas renseigné. */
    public function getNameAttribute(?string $value): ?string
    {
        return $value ?? $this->attributes['label'] ?? null;
    }

    /** Titulaire : préfère `account_holder` si `account_name` n'est pas renseigné. */
    public function getAccountNameAttribute(?string $value): ?string
    {
        return $value ?? $this->attributes['account_holder'] ?? null;
    }

    /** Alias minimum_amount → min_amount */
    public function getMinimumAmountAttribute(): ?string
    {
        return $this->attributes['min_amount'] ?? null;
    }

    /** Alias maximum_amount → max_amount */
    public function getMaximumAmountAttribute(): ?string
    {
        return $this->attributes['max_amount'] ?? null;
    }

    // ---------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------

    public function getMobileMoneyInstructions(): string
    {
        $operator = $this->operator ?? $this->label ?? '';
        $number   = $this->account_number ?? '';
        $holder   = $this->account_name ?? '';
        $currency = $this->currency ?? 'XOF';

        return "1. Ouvrez votre application {$operator}\n"
             . "2. Envoyez {amount} {$currency} au {$number} ({$holder})\n"
             . "3. Conservez la référence de votre transaction\n"
             . "4. Remplissez le formulaire ci-dessous";
    }
}
