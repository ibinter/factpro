<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentArchive extends Model
{
    protected $guarded = [];

    protected $casts = [
        'archived_at'     => 'datetime',
        'last_verified_at' => 'datetime',
        'is_verified'     => 'boolean',
        'pdf_size'        => 'integer',
        'archive_version' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
