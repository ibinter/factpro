<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Service de conversion multi-devises (cahier IBIG §3 DEV / §14).
 *
 * Convention de stockage : `rate` = nombre d'unités de `currency` pour
 * 1 unité de `base_currency` (1 base = rate * currency).
 *
 * Le rafraîchissement interroge une API publique sans clé
 * (https://open.er-api.com) ; en cas d'échec ou d'absence d'internet, il
 * bascule sur des taux de repli dérivés du pivot fixe réglementaire
 * (1 EUR = 655,957 XOF). Aucune exception ne remonte de refresh().
 */
class ExchangeRateService
{
    private const API_URL = 'https://open.er-api.com/v6/latest/';

    /**
     * Rafraîchit les taux pour la devise de base donnée.
     * Retourne le nombre de couples de taux enregistrés (api ou fallback).
     */
    public function refresh(string $base = 'XOF'): int
    {
        $base = strtoupper($base);
        $allowed = array_map('strtoupper', config('factpro.currencies', []));

        try {
            $response = Http::timeout(10)->get(self::API_URL.$base);

            if ($response->successful()) {
                $body = $response->json();

                if (($body['result'] ?? null) === 'success' && ! empty($body['rates'])) {
                    $now = Carbon::now();
                    $count = 0;

                    foreach ($body['rates'] as $currency => $rate) {
                        $currency = strtoupper($currency);

                        if (! in_array($currency, $allowed, true) || ! is_numeric($rate)) {
                            continue;
                        }

                        $this->store($base, $currency, (float) $rate, $now, 'api');
                        $count++;
                    }

                    if ($count > 0) {
                        return $count;
                    }
                }
            }
        } catch (Throwable $e) {
            // On ignore et on bascule sur le repli.
        }

        return $this->seedFallback($base);
    }

    /**
     * Alimente les taux de repli dérivés du pivot fixe XOF (config
     * exchange_rates_xof). Source 'fallback'.
     */
    public function seedFallback(string $base = 'XOF'): int
    {
        $base = strtoupper($base);
        $allowed = array_map('strtoupper', config('factpro.currencies', []));

        // pivot[c] = nombre de XOF pour 1 unité de c (ex : EUR => 655.957)
        $pivot = config('factpro.exchange_rates_xof', ['XOF' => 1.0]);
        $pivot = array_change_key_case($pivot, CASE_UPPER);

        $baseXof = $pivot[$base] ?? null;
        if ($baseXof === null || (float) $baseXof == 0.0) {
            // Base inconnue du pivot : on repart de XOF.
            $base = 'XOF';
            $baseXof = 1.0;
        }

        $now = Carbon::now();
        $count = 0;

        foreach ($pivot as $currency => $xofPerUnit) {
            if (! in_array($currency, $allowed, true) || (float) $xofPerUnit == 0.0) {
                continue;
            }

            // 1 base = (XOF/base) / (XOF/currency) unités de currency
            $rate = (float) $baseXof / (float) $xofPerUnit;

            $this->store($base, $currency, $rate, $now, 'fallback');
            $count++;
        }

        return $count;
    }

    /**
     * Taux de conversion 1 unité `$from` -> `$to`.
     * Direct, inverse, ou triangulation via XOF. null si introuvable.
     */
    public function rate(string $from, string $to): ?float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return 1.0;
        }

        if (($direct = $this->directOrInverse($from, $to)) !== null) {
            return $direct;
        }

        // Triangulation via XOF
        $a = $this->directOrInverse($from, 'XOF');
        $b = $this->directOrInverse('XOF', $to);

        if ($a !== null && $b !== null) {
            return $a * $b;
        }

        return null;
    }

    /**
     * Convertit un montant de `$from` vers `$to`, arrondi à 2 décimales.
     */
    public function convert(float $amount, string $from, string $to): ?float
    {
        $rate = $this->rate($from, $to);

        return $rate === null ? null : round($amount * $rate, 2);
    }

    /**
     * Date du dernier rafraîchissement enregistré, ou null si aucun taux.
     */
    public function freshness(): ?Carbon
    {
        $latest = ExchangeRate::max('fetched_at');

        return $latest ? Carbon::parse($latest) : null;
    }

    /**
     * Cherche un taux direct (base=$from, currency=$to) puis son inverse.
     */
    private function directOrInverse(string $from, string $to): ?float
    {
        if ($from === $to) {
            return 1.0;
        }

        $direct = ExchangeRate::where('base_currency', $from)
            ->where('currency', $to)
            ->value('rate');

        if ($direct !== null) {
            return (float) $direct;
        }

        $inverse = ExchangeRate::where('base_currency', $to)
            ->where('currency', $from)
            ->value('rate');

        if ($inverse !== null && (float) $inverse != 0.0) {
            return 1.0 / (float) $inverse;
        }

        return null;
    }

    private function store(string $base, string $currency, float $rate, Carbon $at, string $source): void
    {
        ExchangeRate::updateOrCreate(
            ['base_currency' => $base, 'currency' => $currency],
            ['rate' => $rate, 'fetched_at' => $at, 'source' => $source],
        );
    }
}
