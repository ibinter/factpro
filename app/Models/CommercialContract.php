<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommercialContract extends Model
{
    use SoftDeletes;

    protected $table = 'contracts';

    protected $fillable = [
        'company_id',
        'customer_id',
        'title',
        'type',
        'start_date',
        'end_date',
        'auto_renew',
        'alert_days_before',
        'amount',
        'currency',
        'status',
        'current_version',
        'notes',
        'signatories',
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'signatories'  => 'array',
        'auto_renew'   => 'boolean',
        'amount'       => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ContractVersion::class, 'contract_id');
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date !== null
            && $this->end_date->isPast()
            && $this->status !== 'terminated';
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if ($this->end_date === null) {
            return null;
        }

        return (int) now()->diffInDays($this->end_date, false);
    }

    public function getNeedsAlertAttribute(): bool
    {
        $days = $this->days_until_expiry;

        return $days !== null && $days <= $this->alert_days_before && $days >= 0;
    }
}
