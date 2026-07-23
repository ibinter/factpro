<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model {
    protected $guarded = [];
    protected $casts = ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'active' => 'boolean'];

    public function scopeVisible($q) {
        return $q->where('active', true)
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
