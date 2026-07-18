<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Code prépayé revendeur — activation instantanée de licence (cahier §Voucher).
 */
class PrepaidVoucher extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'face_value' => 'decimal:2',
        'reseller_price' => 'decimal:2',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function usedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }

    public function usedByCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'used_by_company_id');
    }

    public function activatedLicense(): BelongsTo
    {
        return $this->belongsTo(License::class, 'activated_license_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Codes disponibles et non expirés. */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where(fn ($s) => $s->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    /** Ce code est-il utilisable (disponible et dans sa fenêtre de validité) ? */
    public function isUsable(): bool
    {
        return $this->status === 'available'
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
