<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyReward extends Model
{
    protected $guarded = [];

    protected $casts = [
        'points_cost' => 'integer',
        'reward_value' => 'decimal:2',
        'is_active' => 'boolean',
        'stock' => 'integer',
        'redemptions_count' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
