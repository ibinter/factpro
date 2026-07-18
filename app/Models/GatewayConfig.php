<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayConfig extends Model
{
    protected $guarded = [];

    protected $casts = [
        'config' => 'encrypted:array',
        'supported_countries' => 'array',
        'supported_currencies' => 'array',
        'is_active' => 'boolean',
    ];

    /** Récupère ou initialise la config pour une gateway donnée. */
    public static function forGateway(string $gateway): self
    {
        return static::firstOrNew(['gateway' => $gateway]);
    }
}
