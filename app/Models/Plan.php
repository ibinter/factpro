<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = [];

    protected $casts = [
        'features' => 'array',
        'limits' => 'array',
        'metadata' => 'array',
        'price_monthly' => 'decimal:2',
        'promo_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /** Prix pour une durée avec remise annuelle (12 mois au prix de 10 — cahier §22.2) */
    public function priceFor(int $months): float
    {
        $monthly = (float) ($this->promo_price ?? $this->price_monthly);

        return match ($months) {
            12 => round($monthly * 10, 2), // -20% ≈ payez 10 mois
            default => round($monthly * $months, 2),
        };
    }

    public function limit(string $key): ?int
    {
        $value = $this->limits[$key] ?? null;

        return $value === null || $value === 'unlimited' ? null : (int) $value;
    }
}
