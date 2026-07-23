<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GedFolder extends Model
{
    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
        'color',
        'sort_order',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(GedFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(GedFolder::class, 'parent_id')->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GedDocument::class, 'ged_folder_id');
    }
}
