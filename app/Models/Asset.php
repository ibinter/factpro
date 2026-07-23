<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'category', 'reference', 'description',
        'purchase_price', 'residual_value', 'purchase_date', 'start_date',
        'duration_years', 'depreciation_method', 'status', 'disposal_date',
        'disposal_price', 'supplier', 'location', 'serial_number', 'currency', 'meta',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'start_date'    => 'date',
        'disposal_date' => 'date',
        'meta'          => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class)->orderBy('year');
    }

    public function getCurrentNetBookValueAttribute(): float
    {
        $dep = $this->depreciations->where('year', '<=', now()->year)->last();
        return $dep ? (float) $dep->net_book_value : (float) $this->purchase_price;
    }

    public function computeDepreciationSchedule(): array
    {
        $schedule    = [];
        $base        = (float) $this->purchase_price - (float) $this->residual_value;
        $nbv         = (float) $this->purchase_price;
        $years       = (int) $this->duration_years;
        $accumulated = 0;

        for ($y = 0; $y < $years; $y++) {
            $year = (int) $this->start_date->format('Y') + $y;
            $rate = null;

            if ($this->depreciation_method === 'declining') {
                $rate      = (2 / $years);
                $annualDep = round($nbv * $rate, 2);
                if ($y === $years - 1) {
                    $annualDep = round($nbv - (float) $this->residual_value, 2);
                }
            } else {
                $rate      = 1 / $years;
                $annualDep = round($base / $years, 2);
            }

            $accumulated += $annualDep;
            $nbv         -= $annualDep;

            $schedule[] = [
                'year'                     => $year,
                'rate'                     => round($rate ?? (1 / $years), 4),
                'depreciation_amount'      => $annualDep,
                'accumulated_depreciation' => round($accumulated, 2),
                'net_book_value'           => max(round($nbv, 2), (float) $this->residual_value),
            ];
        }

        return $schedule;
    }
}
