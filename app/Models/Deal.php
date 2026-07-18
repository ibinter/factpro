<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'value'    => 'float',
        'probability' => 'integer',
        'won_at'   => 'datetime',
        'lost_at'  => 'datetime',
        'expected_close_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(DealActivity::class)->orderByDesc('created_at');
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByStage(Builder $query, string $stage): Builder
    {
        return $query->where('stage', $stage);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('stage', ['won', 'lost']);
    }

    public function displayName(): string
    {
        return $this->customer?->name ?? $this->prospect_name ?? 'Sans nom';
    }

    public function convertToCustomer(): Customer
    {
        if ($this->customer_id) {
            return $this->customer;
        }

        $customer = Customer::create([
            'company_id' => $this->company_id,
            'name'       => $this->prospect_name ?? 'Prospect',
            'email'      => $this->prospect_email,
            'phone'      => $this->prospect_phone,
            'country'    => 'CI',
            'currency'   => 'XOF',
        ]);

        $this->update(['customer_id' => $customer->id]);

        return $customer;
    }
}
