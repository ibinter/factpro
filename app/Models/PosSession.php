<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opening_float' => 'decimal:2',
        'closing_float' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'counted_cash' => 'decimal:2',
        'difference' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'totals_by_method' => 'array',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'z_report_generated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Sessions de caisse ouvertes. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }
}
