<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealActivity extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(Deal $deal, User $user, string $type, string $content, array $meta = []): static
    {
        return static::create([
            'deal_id'  => $deal->id,
            'user_id'  => $user->id,
            'type'     => $type,
            'content'  => $content,
            'metadata' => $meta ?: null,
        ]);
    }
}
