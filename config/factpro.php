<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IBIG FactPro — Configuration applicative
    |--------------------------------------------------------------------------
    */

    'verify_base_url' => env('VERIFY_BASE_URL', env('APP_URL', 'http://localhost').'/verify'),

    'trial' => [
        'duration_days' => (int) env('TRIAL_DURATION_DAYS', 7),
        'watermark_text' => 'VERSION ESSAI FACTPRO',
    ],

    'license' => [
        'grace_period_days' => (int) env('LICENSE_GRACE_PERIOD_DAYS', 7),
        'provisional_max_days' => (int) env('LICENSE_PROVISIONAL_MAX_DAYS', 7),
    ],

    'moneroo' => [
        'public_key' => env('MONEROO_PUBLIC_KEY'),
        'secret_key' => env('MONEROO_SECRET_KEY'),
        'webhook_secret' => env('MONEROO_WEBHOOK_SECRET'),
        'mode' => env('MONEROO_MODE', 'sandbox'),
        'base_url' => 'https://api.moneroo.io/v1',
    ],

    'proofs' => [
        'disk' => env('PROOF_STORAGE_DISK', 'local'),
        'max_size_mb' => (int) env('PROOF_MAX_SIZE_MB', 10),
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'],
    ],

    'fraud' => [
        'alert_email' => env('FRAUD_ALERT_EMAIL', 'admin@ibigsoft.com'),
        'amount_tolerance_percent' => (int) env('FRAUD_AMOUNT_TOLERANCE_PERCENT', 5),
    ],

    'currencies' => [
        // Zone franc CFA (parités fixes à l'EUR)
        'XOF', 'XAF',
        // Afrique — autres devises
        'NGN', 'GHS', 'KES', 'TZS', 'UGX', 'RWF', 'ETB', 'EGP', 'MAD', 'DZD',
        'TND', 'LYD', 'CDF', 'GNF', 'MGA', 'MUR', 'SCR', 'ZAR', 'BWP', 'NAD',
        'ZMW', 'MWK', 'MZN', 'AOA', 'DJF', 'SOS', 'SDG', 'GMD', 'SLL', 'LRD',
        'CVE', 'KMF', 'BIF',
        // Devises majeures internationales
        'EUR', 'USD', 'GBP', 'CHF', 'CAD', 'AUD', 'JPY', 'CNY', 'INR', 'AED',
        'SAR', 'TRY', 'BRL', 'MXN', 'RUB', 'SGD', 'HKD', 'SEK', 'NOK', 'DKK',
        'PLN', 'QAR', 'KWD',
    ],

    // Taux indicatifs de repli si pas d'API de change (1 EUR = 655.957 XOF taux fixe réglementaire)
    'exchange_rates_xof' => [
        'XOF' => 1,
        'EUR' => 655.957,
        'USD' => 590.0,
    ],
];
