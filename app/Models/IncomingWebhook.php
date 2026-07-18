<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class IncomingWebhook extends Model
{
    protected $guarded = [];

    protected $casts = [
        'allowed_actions' => 'array',
        'is_active' => 'boolean',
        'last_called_at' => 'datetime',
    ];

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
