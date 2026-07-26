<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ModuleFeature extends Model
{
    protected $fillable = [
        'slug',
        'category',
        'name_fr',
        'name_en',
        'description_fr',
        'description_en',
        'icon',
        'available_in_plans',
        'status',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'available_in_plans' => 'array',
        'is_active'          => 'boolean',
        'sort_order'         => 'integer',
    ];

    public function isAvailableForPlan(string $plan): bool
    {
        return $this->available_in_plans === null || in_array($plan, $this->available_in_plans);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeModules(Builder $q): Builder
    {
        return $q->where('category', 'module');
    }

    public function scopeIntegrations(Builder $q): Builder
    {
        return $q->where('category', 'integration');
    }
}
