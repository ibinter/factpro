<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    public const STATUSES = ['pending', 'in_progress', 'completed', 'invoiced'];

    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'billing_amount' => 'decimal:2',
        'invoiced_at' => 'datetime',
        'completion_pct' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
