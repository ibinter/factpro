<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Parrainage entre deux utilisateurs (cahier IBIG §22 Phase 8).
 *
 * @property int         $id
 * @property int         $referrer_id
 * @property int|null    $referred_id
 * @property string      $referral_code
 * @property string      $status          pending|converted|rewarded
 * @property int         $reward_months
 * @property \Carbon\Carbon|null $converted_at
 * @property \Carbon\Carbon|null $rewarded_at
 */
class Referral extends Model
{
    protected $guarded = [];

    protected $casts = [
        'converted_at' => 'datetime',
        'rewarded_at'  => 'datetime',
        'reward_months' => 'integer',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
