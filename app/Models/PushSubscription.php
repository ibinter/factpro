<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'endpoint',
        'public_key',
        'auth_token',
        'user_agent',
        'subscribed_at',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'last_used_at'  => 'datetime',
        'is_active'     => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
