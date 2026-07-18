<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForecastSnapshot extends Model
{
    protected $guarded = [];

    protected $casts = [
        'snapshot_date' => 'date',
        'actual_revenue' => 'decimal:2',
        'forecasted_revenue' => 'decimal:2',
        'accuracy_pct' => 'decimal:2',
        'period_month' => 'integer',
        'period_year' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
