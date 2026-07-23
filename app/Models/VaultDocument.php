<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VaultDocument extends Model
{
    protected $fillable = [
        'company_id',
        'document_type',
        'source_id',
        'source_type',
        'title',
        'file_path',
        'file_hash',
        'archive_hash',
        'file_size',
        'mime_type',
        'archived_at',
        'retention_until',
        'retention_years',
        'is_sealed',
        'metadata',
        'seal_certificate',
    ];

    protected $casts = [
        'metadata'    => 'array',
        'archived_at' => 'datetime',
        'is_sealed'   => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getIntegrityStatusAttribute(): string
    {
        $absolutePath = Storage::path($this->file_path);

        if (! file_exists($absolutePath)) {
            return 'missing';
        }

        $currentHash = hash_file('sha256', $absolutePath);

        return $currentHash === $this->file_hash ? 'valid' : 'tampered';
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        return (int) now()->diffInDays($this->retention_until, false);
    }

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Compute the archive_hash from file hash, metadata, and timestamp.
     */
    public static function sealDocument(string $filePath, array $metadata): string
    {
        $fileHash  = hash_file('sha256', $filePath);
        $timestamp = now()->toISOString();
        return hash('sha256', $fileHash . json_encode($metadata) . $timestamp);
    }
}
