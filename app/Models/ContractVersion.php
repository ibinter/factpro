<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ContractVersion extends Model
{
    protected $fillable = [
        'contract_id',
        'version_number',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'change_notes',
        'created_by',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(CommercialContract::class, 'contract_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }
}
