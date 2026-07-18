<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'public_page_enabled' => 'boolean',
        'allow_online_order' => 'boolean',
        'public_images' => 'array',
        'reorder_point' => 'integer',
        'reorder_quantity' => 'integer',
        'lead_time_days' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
