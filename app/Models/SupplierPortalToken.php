<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierPortalToken extends Model
{
    protected $fillable = [
        'company_id', 'document_id', 'supplier_name', 'supplier_email',
        'token', 'expires_at', 'viewed_at', 'responded_at',
        'quoted_price', 'delivery_days', 'supplier_notes', 'status',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'viewed_at'    => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
