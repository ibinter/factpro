<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateMarketplace extends Model
{
    protected $table = 'template_marketplace';

    protected $guarded = [];

    protected $casts = [
        'preview_data' => 'array',
        'tags' => 'array',
        'is_public' => 'boolean',
        'is_approved' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function averageRating(): float
    {
        return $this->rating_count > 0
            ? round($this->rating_sum / $this->rating_count, 1)
            : 0;
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true)->where('is_approved', true);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function toTemplateConfig(): array
    {
        $base = config('pdf_templates.'.$this->base_template, []);

        return array_merge($base, [
            'name' => $this->name,
            'description' => $this->description ?? '',
            'primary' => $this->primary_color,
            'secondary' => $this->secondary_color,
            'accent' => $this->accent_color,
            'custom_css' => $this->custom_css,
            'marketplace_id' => $this->id,
            'base_template' => $this->base_template,
        ]);
    }
}
