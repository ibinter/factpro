<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAuditLog extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public static function record(Document $document, string $event, ?User $user = null, array $meta = []): void
    {
        static::create([
            'company_id' => $document->company_id,
            'document_id' => $document->id,
            'user_id' => $user?->id,
            'event' => $event,
            'meta' => empty($meta) ? null : $meta,
        ]);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
