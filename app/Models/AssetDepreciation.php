<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciation extends Model
{
    protected $fillable = [
        'asset_id', 'year', 'depreciation_amount', 'accumulated_depreciation', 'net_book_value', 'rate',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
