<?php

namespace App\Services\MobileMoney;

class MobileMoneyManager
{
    public const DRIVERS = ['wave', 'orange_money', 'mtn_momo', 'moov_money'];

    /**
     * Préfixes de détection automatique par opérateur et pays.
     * Clé : driver → pays → liste de préfixes locaux (sans indicatif).
     */
    private const PREFIXES = [
        'wave' => [
            'SN' => ['70', '75', '76', '77'],
            'CI' => ['01'],
        ],
        'orange_money' => [
            'SN' => ['77'],
            'CI' => ['07', '08'],
            'CM' => ['69'],
            'ML' => ['70', '71'],
            'BF' => ['70', '71', '76'],
        ],
        'mtn_momo' => [
            'CI' => ['05', '25'],
            'CM' => ['67', '68'],
            'GH' => ['24', '54', '55'],
            'NG' => ['803', '806', '813'],
        ],
        'moov_money' => [
            'CI' => ['01'],
            'BJ' => ['95', '96', '97'],
            'TG' => ['90', '91', '92', '93'],
            'BF' => ['60', '61', '62'],
        ],
    ];

    public function driver(string $name): MobileMoneyDriver
    {
        return match ($name) {
            'wave'        => new WaveService(),
            'orange_money' => new OrangeMoneyService(),
            'mtn_momo'    => new MtnMomoService(),
            'moov_money'  => new MoovMoneyService(),
            default       => throw new \InvalidArgumentException("Driver inconnu: $name"),
        };
    }

    /**
     * Détecte le meilleur driver selon le préfixe local du numéro et le pays.
     */
    public function detectDriver(string $phone, string $country): ?string
    {
        // Normaliser le numéro : enlever l'indicatif pays et les espaces
        $local = $this->extractLocalNumber($phone, $country);

        foreach (self::PREFIXES as $driver => $countries) {
            if (! isset($countries[$country])) {
                continue;
            }
            foreach ($countries[$country] as $prefix) {
                if (str_starts_with($local, $prefix)) {
                    return $driver;
                }
            }
        }

        return null;
    }

    /**
     * Initie un paiement sur le driver choisi.
     *
     * @return array{checkout_url?: string, reference: string, status: string, instructions?: string}
     */
    public function pay(string $driver, string $phone, float $amount, string $currency, string $reference, string $description): array
    {
        return $this->driver($driver)->initiate($phone, $amount, $currency, $reference, $description);
    }

    /**
     * Extrait le numéro local depuis un numéro international.
     */
    private function extractLocalNumber(string $phone, string $country): string
    {
        $dialCodes = [
            'CI' => '225',
            'SN' => '221',
            'CM' => '237',
            'GH' => '233',
            'NG' => '234',
            'BJ' => '229',
            'TG' => '228',
            'BF' => '226',
            'ML' => '223',
        ];

        $phone = preg_replace('/\s+/', '', $phone);
        $phone = ltrim($phone, '+');

        $dial = $dialCodes[$country] ?? null;
        if ($dial && str_starts_with($phone, $dial)) {
            $phone = substr($phone, strlen($dial));
        }

        return $phone;
    }
}
