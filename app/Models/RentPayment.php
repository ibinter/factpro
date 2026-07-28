<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentPayment extends Model
{
    protected $fillable = [
        'lease_id', 'document_id', 'period_month',
        'amount', 'status', 'paid_at', 'late_fee',
    ];

    protected $casts = [
        'period_month' => 'date',
        'paid_at'      => 'datetime',
        'amount'       => 'decimal:2',
        'late_fee'     => 'decimal:2',
    ];

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
