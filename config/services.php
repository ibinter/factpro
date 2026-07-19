<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY', ''),
    ],

    'vapid' => [
        'public_key'  => env('VAPID_PUBLIC_KEY', ''),
        'private_key' => env('VAPID_PRIVATE_KEY', ''),
    ],

    // Mobile Money — Phase 17
    'wave' => [
        'api_key'     => env('WAVE_API_KEY'),
        'secret'      => env('WAVE_SECRET'),
        'merchant_id' => env('WAVE_MERCHANT_ID'),
    ],

    'orange_money' => [
        'client_id'     => env('ORANGE_MONEY_CLIENT_ID'),
        'client_secret' => env('ORANGE_MONEY_CLIENT_SECRET'),
        'merchant_key'  => env('ORANGE_MONEY_MERCHANT_KEY'),
    ],

    'mtn_momo' => [
        'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY'),
        'api_user'         => env('MTN_MOMO_API_USER'),
        'api_key'          => env('MTN_MOMO_API_KEY'),
        'environment'      => env('MTN_MOMO_ENV', 'sandbox'),
    ],

    'moov_money' => [
        'api_key'     => env('MOOV_MONEY_API_KEY'),
        'merchant_id' => env('MOOV_MONEY_MERCHANT_ID'),
    ],

    'cod' => [
        'enabled' => env('COD_ENABLED', false),
        'zones'   => env('COD_ZONES', 'Abidjan,Dakar'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
    ],

];
