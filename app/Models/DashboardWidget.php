<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardWidget extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'widget_type',
        'position_x',
        'position_y',
        'width',
        'height',
        'config',
        'is_visible',
    ];

    protected $casts = [
        'config'     => 'array',
        'is_visible' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
