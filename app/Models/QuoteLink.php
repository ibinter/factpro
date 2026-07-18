<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class QuoteLink extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expires_at'          => 'datetime',
        'viewed_at'           => 'datetime',
        'signed_at'           => 'datetime',
        'declined_at'         => 'datetime',
        'notification_sent_at'=> 'datetime',
        'allow_comments'      => 'boolean',
        'allow_decline'       => 'boolean',
        'require_signature'   => 'boolean',
    ];

    /** Masque la signature dans toArray() (peut être volumineuse & sensible). */
    protected $hidden = ['client_signature_data', 'password'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function publicUrl(): string
    {
        return config('app.url').'/q/'.$this->token;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return ! $this->isExpired() && ! $this->signed_at && ! $this->declined_at;
    }

    public function checkPassword(string $pwd): bool
    {
        return Hash::check($pwd, $this->password);
    }
}
