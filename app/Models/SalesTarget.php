<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTarget extends Model
{
    protected $guarded = [];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'period_month' => 'integer',
        'period_year' => 'integer',
        'target_invoices' => 'integer',
        'target_customers' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }
}
