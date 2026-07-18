<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoReorderRule extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active'          => 'boolean',
        'auto_approve'       => 'boolean',
        'last_triggered_at'  => 'datetime',
        'trigger_threshold'  => 'integer',
        'order_quantity'     => 'integer',
        'cooldown_hours'     => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lastDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'last_document_id');
    }

    /** Returns true if this rule is still within its cooldown window. */
    public function isInCooldown(): bool
    {
        if (! $this->last_triggered_at) {
            return false;
        }

        return now()->diffInHours($this->last_triggered_at, absolute: true) < $this->cooldown_hours;
    }
}
