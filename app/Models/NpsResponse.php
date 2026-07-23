<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpsResponse extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
