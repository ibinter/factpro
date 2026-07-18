<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusIncident extends Model
{
    protected $guarded = [];

    protected $casts = [
        'affected_components' => 'array',
        'started_at'          => 'datetime',
        'resolved_at'         => 'datetime',
        'is_public'           => 'boolean',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['investigating', 'identified', 'monitoring']);
    }
}
