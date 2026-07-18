<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyProgram extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'points_per_1000' => 'integer',
        'bronze_threshold' => 'integer',
        'silver_threshold' => 'integer',
        'gold_threshold' => 'integer',
        'expiry_months' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(LoyaltyReward::class, 'company_id', 'company_id');
    }
}
