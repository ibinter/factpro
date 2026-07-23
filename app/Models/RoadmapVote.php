<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RoadmapVote extends Model {
    protected $guarded = [];
    public function feature() { return $this->belongsTo(RoadmapFeature::class, 'feature_id'); }
    public function user()    { return $this->belongsTo(User::class); }
}
