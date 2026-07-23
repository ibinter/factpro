<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerScore extends Model
{
    protected $fillable = [
        'customer_id', 'company_id', 'payment_risk_score', 'churn_score',
        'avg_payment_days', 'late_payments_count', 'total_invoices', 'total_revenue',
        'last_order_date', 'days_since_last_order', 'avg_order_frequency_days',
        'risk_label', 'churn_label', 'factors', 'computed_at',
    ];

    protected $casts = [
        'factors'         => 'array',
        'last_order_date' => 'date',
        'computed_at'     => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getRiskColorAttribute(): string
    {
        return match ($this->risk_label) {
            'élevé'  => 'red',
            'modéré' => 'orange',
            default  => 'green',
        };
    }

    public function getChurnColorAttribute(): string
    {
        return match ($this->churn_label) {
            'churné'   => 'red',
            'à risque' => 'orange',
            default    => 'green',
        };
    }
}
