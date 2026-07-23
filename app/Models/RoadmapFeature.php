<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RoadmapFeature extends Model {
    protected $guarded = [];
    protected $casts   = ['delivered_at' => 'datetime'];
    public function votes() { return $this->hasMany(RoadmapVote::class, 'feature_id'); }
    public function hasVotedBy(int $userId): bool {
        return $this->votes()->where('user_id', $userId)->exists();
    }
}
