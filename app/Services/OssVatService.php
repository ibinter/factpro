<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OssVatService
{
    const EU_VAT_RATES = [
        'DE' => ['standard' => 19,   'reduced' => 7,    'super_reduced' => null],
        'FR' => ['standard' => 20,   'reduced' => 10,   'super_reduced' => 5.5],
        'IT' => ['standard' => 22,   'reduced' => 10,   'super_reduced' => 4],
        'ES' => ['standard' => 21,   'reduced' => 10,   'super_reduced' => 4],
        'BE' => ['standard' => 21,   'reduced' => 12,   'super_reduced' => 6],
        'NL' => ['standard' => 21,   'reduced' => 9,    'super_reduced' => null],
        'PT' => ['standard' => 23,   'reduced' => 13,   'super_reduced' => 6],
        'AT' => ['standard' => 20,   'reduced' => 13,   'super_reduced' => 10],
        'PL' => ['standard' => 23,   'reduced' => 8,    'super_reduced' => 5],
        'SE' => ['standard' => 25,   'reduced' => 12,   'super_reduced' => 6],
        'DK' => ['standard' => 25,   'reduced' => 0,    'super_reduced' => null],
        'FI' => ['standard' => 24,   'reduced' => 14,   'super_reduced' => 10],
        'IE' => ['standard' => 23,   'reduced' => 13.5, 'super_reduced' => 9],
        'LU' => ['standard' => 17,   'reduced' => 8,    'super_reduced' => 3],
        'CH' => ['standard' => 8.1,  'reduced' => 2.6,  'super_reduced' => 2.6],
    ];

    /**
     * VAT number regex patterns by country.
     */
    const VAT_PATTERNS = [
        'AT' => '/^ATU\d{8}$/',
        'BE' => '/^BE0\d{9}$/',
        'BG' => '/^BG\d{9,10}$/',
        'CH' => '/^CHE-\d{3}\.\d{3}\.\d{3}(MWST|TVA|IVA)?$/',
        'CY' => '/^CY\d{8}[A-Z]$/',
        'CZ' => '/^CZ\d{8,10}$/',
        'DE' => '/^DE\d{9}$/',
        'DK' => '/^DK\d{8}$/',
        'EE' => '/^EE\d{9}$/',
        'ES' => '/^ES[A-Z0-9]\d{7}[A-Z0-9]$/',
        'FI' => '/^FI\d{8}$/',
        'FR' => '/^FR[A-Z0-9]{2}\d{9}$/',
        'GB' => '/^GB(\d{9}|\d{12}|GD\d{3}|HA\d{3})$/',
        'GR' => '/^EL\d{9}$/',
        'HR' => '/^HR\d{11}$/',
        'HU' => '/^HU\d{8}$/',
        'IE' => '/^IE\d[A-Z0-9+*]\d{5}[A-Z]{1,2}$/',
        'IT' => '/^IT\d{11}$/',
        'LT' => '/^LT(\d{9}|\d{12})$/',
        'LU' => '/^LU\d{8}$/',
        'LV' => '/^LV\d{11}$/',
        'MT' => '/^MT\d{8}$/',
        'NL' => '/^NL\d{9}B\d{2}$/',
        'PL' => '/^PL\d{10}$/',
        'PT' => '/^PT\d{9}$/',
        'RO' => '/^RO\d{2,10}$/',
        'SE' => '/^SE\d{12}$/',
        'SI' => '/^SI\d{8}$/',
        'SK' => '/^SK\d{10}$/',
    ];

    /**
     * Returns the VAT rate for a country and product type.
     */
    public function getVatRate(string $countryCode, string $productType = 'standard'): float
    {
        $rates = self::EU_VAT_RATES[$countryCode] ?? null;

        if ($rates === null) {
            return 0.0;
        }

        $rate = $rates[$productType] ?? $rates['standard'];

        return (float) ($rate ?? 0.0);
    }

    /**
     * Calculates OSS declaration grouped by customer country for a quarter.
     */
    public function calculateOssDeclaration(int $companyId, int $quarter, int $year): array
    {
        $startMonth = ($quarter - 1) * 3 + 1;
        $from = Carbon::createFromDate($year, $startMonth, 1)->startOfMonth();
        $to   = Carbon::createFromDate($year, $startMonth + 2, 1)->endOfMonth();

        $euCountries = array_keys(self::EU_VAT_RATES);

        // Fetch invoices with customer country in EU
        $docs = DB::table('documents')
            ->leftJoin('customers', 'customers.id', '=', 'documents.customer_id')
            ->where('documents.company_id', $companyId)
            ->whereNotNull('documents.finalized_at')
            ->whereNotIn('documents.status', ['draft', 'cancelled'])
            ->whereIn('documents.type', ['invoice', 'deposit_invoice', 'balance_invoice', 'credit_note'])
            ->whereBetween('documents.issue_date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('customers.country', $euCountries)
            ->get([
                'documents.subtotal',
                'documents.tax_amount',
                'documents.type',
                'customers.country',
            ]);

        $byCountry = [];

        foreach ($docs as $doc) {
            $sign    = $doc->type === 'credit_note' ? -1 : 1;
            $country = strtoupper($doc->country ?? '');

            if (! isset(self::EU_VAT_RATES[$country])) {
                continue;
            }

            $vatRate = (float) self::EU_VAT_RATES[$country]['standard'];
            $baseHt  = $sign * (float) $doc->subtotal;

            if (! isset($byCountry[$country])) {
                $byCountry[$country] = [
                    'country'    => $country,
                    'base_ht'    => 0.0,
                    'vat_rate'   => $vatRate,
                    'vat_amount' => 0.0,
                ];
            }

            $byCountry[$country]['base_ht']    += $baseHt;
            $byCountry[$country]['vat_amount'] += round($baseHt * $vatRate / 100, 2);
        }

        $result = [];
        $totalVat = 0.0;

        foreach ($byCountry as $entry) {
            $entry['base_ht']    = round($entry['base_ht'], 2);
            $entry['vat_amount'] = round($entry['vat_amount'], 2);
            $totalVat           += $entry['vat_amount'];
            $result[]            = $entry;
        }

        return [
            'by_country' => $result,
            'total_vat'  => round($totalVat, 2),
            'period'     => ['quarter' => $quarter, 'year' => $year],
        ];
    }

    /**
     * Returns true if total EU sales for the year are below the 10 000 € threshold.
     */
    public function isBelowThreshold(int $companyId, int $year): bool
    {
        $euCountries = array_keys(self::EU_VAT_RATES);

        $from = Carbon::createFromDate($year, 1, 1)->startOfYear();
        $to   = Carbon::createFromDate($year, 12, 31)->endOfYear();

        $total = DB::table('documents')
            ->leftJoin('customers', 'customers.id', '=', 'documents.customer_id')
            ->where('documents.company_id', $companyId)
            ->whereNotNull('documents.finalized_at')
            ->whereNotIn('documents.status', ['draft', 'cancelled'])
            ->whereIn('documents.type', ['invoice', 'deposit_invoice', 'balance_invoice', 'credit_note'])
            ->whereBetween('documents.issue_date', [$from->toDateString(), $to->toDateString()])
            ->whereIn('customers.country', $euCountries)
            ->sum('documents.subtotal');

        return (float) $total < 10000.0;
    }

    /**
     * Validates an EU VAT number format using regex.
     */
    public function validateVatNumber(string $vatNumber, string $countryCode): bool
    {
        $countryCode = strtoupper($countryCode);
        $pattern     = self::VAT_PATTERNS[$countryCode] ?? null;

        if ($pattern === null) {
            // Unknown country: basic check — starts with country code and has digits
            return (bool) preg_match('/^' . $countryCode . '\w{5,}$/', $vatNumber);
        }

        return (bool) preg_match($pattern, strtoupper($vatNumber));
    }
}
