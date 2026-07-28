<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepairPart extends Model
{
    protected $fillable = [
        'repair_id',
        'product_id',
        'description',
        'quantity',
        'unit_cost',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_cost'  => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function repair(): BelongsTo
    {
        return $this->belongsTo(Repair::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
