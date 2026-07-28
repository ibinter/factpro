<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DocumentComment extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['document_id', 'user_id', 'body'];

    protected static function booted(): void
    {
        static::creating(function (DocumentComment $model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
