<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GdprRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'received_at'  => 'datetime',
        'deadline_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->deadline_at->isPast() && $this->completed_at === null;
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->completed_at) {
            return 0;
        }
        return max(0, (int) now()->diffInDays($this->deadline_at, false));
    }
}
