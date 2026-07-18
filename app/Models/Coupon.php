<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Coupon de réduction sur l'abonnement (cahier IBIG §22.2).
 */
class Coupon extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_redemptions' => 'integer',
        'redemptions_count' => 'integer',
        'per_user_limit' => 'integer',
        'starts_at' => 'date',
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    /** Coupon actif, dans sa fenêtre de validité et pas épuisé. */
    public function isCurrentlyValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = now()->startOfDay();

        if ($this->starts_at !== null && $this->starts_at->startOfDay()->gt($today)) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->startOfDay()->lt($today)) {
            return false;
        }

        if ($this->max_redemptions !== null && $this->redemptions_count >= $this->max_redemptions) {
            return false;
        }

        return true;
    }

    /** Calcule la remise appliquée à un montant donné. */
    public function discountFor(float $amount): float
    {
        if ($this->type === 'percent') {
            return round($amount * (float) $this->value / 100, 2);
        }

        return round(min((float) $this->value, $amount), 2);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
