<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
        'payment_methods' => 'array',
        'document_style' => 'array',
        'default_tax_rate' => 'decimal:2',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function reminderRules(): HasMany
    {
        return $this->hasMany(ReminderRule::class);
    }

    /**
     * Retourne les méthodes de paiement actives groupées par catégorie.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function activePaymentMethods(): array
    {
        $methods = $this->payment_methods;

        if (empty($methods)) {
            return [];
        }

        $categories = ['online' => [], 'mobile_money' => [], 'classic' => [], 'crypto' => []];

        foreach ($methods as $method) {
            $category = $method['category'] ?? null;
            $active   = $method['active'] ?? false;

            if ($active && isset($categories[$category])) {
                $categories[$category][] = $method;
            }
        }

        return array_filter($categories, fn (array $items) => count($items) > 0);
    }

    /**
     * Indique si au moins une méthode de paiement online est activée.
     */
    public function hasOnlinePayment(): bool
    {
        $methods = $this->payment_methods;

        if (empty($methods)) {
            return false;
        }

        foreach ($methods as $method) {
            if (($method['category'] ?? null) === 'online' && ($method['active'] ?? false)) {
                return true;
            }
        }

        return false;
    }
}
