<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model {
    protected $guarded = [];
    protected $casts = ['published_at' => 'datetime'];

    protected static function boot() {
        parent::boot();
        static::creating(function ($post) {
            if (!$post->slug) {
                $post->slug = Str::slug($post->title).'-'.uniqid();
            }
        });
    }

    public function scopePublished($q) {
        return $q->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function getCategoryLabelAttribute(): string {
        return match($this->category) {
            'actualites'  => 'Actualités',
            'tutoriels'   => 'Tutoriels',
            'produit'     => 'Produit',
            'entreprise'  => 'Entreprise',
            default       => ucfirst($this->category),
        };
    }
}
