<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Taux de change stocké (cahier IBIG §3 DEV / §14).
 * `rate` exprime : 1 unité de `base_currency` = `rate` unités de `currency`.
 */
class ExchangeRate extends Model
{
    protected $guarded = [];

    protected $casts = [
        'rate' => 'float',
        'fetched_at' => 'datetime',
    ];
}
