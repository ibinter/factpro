<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'signature_valid' => 'boolean',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];
}
