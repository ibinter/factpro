<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PosTable extends Model
{
    protected $guarded = [];

    protected $casts = [
        'order_data' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function currentSession(): BelongsTo
    {
        return $this->belongsTo(PosSession::class, 'current_pos_session_id');
    }
}
