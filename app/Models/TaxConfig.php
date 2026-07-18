<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxConfig extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tva_rates' => 'array',
        'has_tps' => 'boolean',
        'has_oca' => 'boolean',
        'has_timbre' => 'boolean',
        'tps_rate' => 'decimal:2',
        'oca_rate' => 'decimal:2',
        'timbre_amount' => 'decimal:2',
    ];

    public const REGIMES = [
        'ohada_ci' => [
            'country' => 'CI',
            'name' => 'Côte d\'Ivoire (OHADA)',
            'tva' => [18, 0],
            'tps' => true,
            'tps_rate' => 1,
            'oca' => true,
            'oca_rate' => 0.5,
        ],
        'ohada_sn' => [
            'country' => 'SN',
            'name' => 'Sénégal (DGI/OHADA)',
            'tva' => [18, 0],
            'tps' => false,
        ],
        'ohada_cm' => [
            'country' => 'CM',
            'name' => 'Cameroun (OHADA)',
            'tva' => [19.25, 0],
        ],
        'ohada_bj' => [
            'country' => 'BJ',
            'name' => 'Bénin (OHADA)',
            'tva' => [18, 0],
        ],
        'maroc' => [
            'country' => 'MA',
            'name' => 'Maroc',
            'tva' => [20, 14, 10, 7, 0],
        ],
        'senegal_dgi' => [
            'country' => 'SN',
            'name' => 'Sénégal (DGI)',
            'tva' => [18, 0],
        ],
        'standard_eu' => [
            'country' => 'FR',
            'name' => 'France (TVA standard)',
            'tva' => [20, 10, 5.5, 2.1, 0],
        ],
        'algerie' => [
            'country' => 'DZ',
            'name' => 'Algérie (DGI)',
            'tva' => [19, 9, 0],
            'tps' => false,
            'oca' => false,
            'tap_rate' => 2,
        ],
        'cote_ivoire_dgi' => [
            'country' => 'CI',
            'name' => 'Côte d\'Ivoire (DGI trimestriel)',
            'tva' => [18, 9, 0],
            'tps' => true,
            'tps_rate' => 1,
            'oca' => true,
            'oca_rate' => 0.5,
        ],
        'custom' => [
            'country' => '',
            'name' => 'Personnalisé',
            'tva' => [],
        ],
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Returns the default field values for a given regime.
     */
    public static function defaultsForRegime(string $regime): array
    {
        $def = self::REGIMES[$regime] ?? self::REGIMES['custom'];

        $tvaRates = array_map(fn ($rate) => [
            'rate' => $rate,
            'label' => $rate === 0 ? 'Exonéré' : 'TVA '.$rate.'%',
        ], $def['tva'] ?? []);

        return [
            'country' => $def['country'] ?? '',
            'tax_regime' => $regime,
            'tva_rates' => $tvaRates,
            'has_tps' => $def['tps'] ?? false,
            'tps_rate' => $def['tps_rate'] ?? 1.00,
            'has_oca' => $def['oca'] ?? false,
            'oca_rate' => $def['oca_rate'] ?? 0.50,
        ];
    }
}
