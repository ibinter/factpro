<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lease extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'property_id', 'customer_id', 'start_date', 'end_date', 'is_open_ended',
        'monthly_rent', 'deposit_amount', 'payment_day', 'status',
        'renewal_notice_days', 'notes', 'terminated_at', 'terminate_reason',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'terminated_at' => 'date',
        'is_open_ended' => 'boolean',
        'monthly_rent'  => 'decimal:2',
        'deposit_amount'=> 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function rentPayments(): HasMany
    {
        return $this->hasMany(RentPayment::class);
    }

    public function isExpiringSoon(int $days = 90): bool
    {
        return $this->end_date
            && $this->end_date->diffInDays(today()) <= $days
            && $this->end_date->isFuture();
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            'active'     => 'Actif',
            'expired'    => 'Expiré',
            'terminated' => 'Résilié',
        ][$this->status] ?? $this->status;
    }
}
