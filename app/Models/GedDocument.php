<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class GedDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'ged_folder_id',
        'title',
        'category',
        'tags',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'version',
        'content_text',
        'uploaded_by',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(GedFolder::class, 'ged_folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size ?? 0;

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 2) . ' MB';
        }

        if ($bytes >= 1_024) {
            return number_format($bytes / 1_024, 2) . ' KB';
        }

        return $bytes . ' B';
    }

    public function scopeSearch(Builder $query, string $q): Builder
    {
        return $query->where(function (Builder $sub) use ($q) {
            $sub->where('title', 'LIKE', "%{$q}%")
                ->orWhere('content_text', 'LIKE', "%{$q}%")
                ->orWhereRaw("JSON_CONTAINS(tags, ?)", [json_encode($q)]);
        });
    }
}
