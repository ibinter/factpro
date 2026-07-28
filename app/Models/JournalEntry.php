<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'date',
        'reference',
        'description',
        'type',
        'document_id',
        'is_locked',
        'locked_at',
        'total_debit',
        'total_credit',
    ];

    protected $casts = [
        'date'      => 'date',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'total_debit'  => 'decimal:2',
        'total_credit' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }
}
