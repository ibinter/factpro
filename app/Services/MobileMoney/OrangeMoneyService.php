<?php

namespace App\Services\MobileMoney;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class OrangeMoneyService implements MobileMoneyDriver
{
    private const TOKEN_URL   = 'https://api.orange.com/oauth/v3/token';
    private const PAYMENT_URL = 'https://api.orange.com/orange-money-webpay/%s/v1/webpayment';

    private function getAccessToken(): string
    {
        $response = Http::timeout(30)
            ->asForm()
            ->withBasicAuth(
                config('services.orange_money.client_id'),
                config('services.orange_money.client_secret')
            )
            ->post(self::TOKEN_URL, ['grant_type' => 'client_credentials']);

        if ($response->failed()) {
            throw new RuntimeException('Orange Money: impossible d\'obtenir le token OAuth2');
        }

        $token = data_get($response->json(), 'access_token');
        if (! $token) {
            throw new RuntimeException('Orange Money: access_token absent de la réponse');
        }

        return $token;
    }

    public function initiate(string $phone, float $amount, string $currency, string $reference, string $description): array
    {
        // Détecter le pays depuis le préfixe ou utiliser CI par défaut
        $country = $this->detectCountry($phone);
        $token   = $this->getAccessToken();
        $url     = sprintf(self::PAYMENT_URL, strtolower($country));

        $response = Http::timeout(30)
            ->withToken($token)
            ->post($url, [
                'merchant_key' => config('services.orange_money.merchant_key'),
                'currency'     => $currency,
                'order_id'     => $reference,
                'amount'       => (int) $amount,
                'return_url'   => config('app.url').'/mobile-money/status/'.$reference.'?result=success',
                'cancel_url'   => config('app.url').'/mobile-money/status/'.$reference.'?result=cancel',
                'notif_url'    => config('app.url').'/webhooks/mobile-money/orange_money',
                'lang'         => 'fr',
                'reference'    => $reference,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Orange Money: erreur HTTP '.$response->status());
        }

        $body = $response->json();

        return [
            'checkout_url' => data_get($body, 'payment_url'),
            'reference'    => $reference,
            'status'       => 'pending',
            'instructions' => 'Vous allez recevoir un USSD sur votre téléphone Orange pour confirmer le paiement.',
        ];
    }

    public function checkStatus(string $reference): array
    {
        $token   = $this->getAccessToken();
        $country = 'ci';
        $url     = sprintf(self::PAYMENT_URL, $country).'/'.$reference;

        $response = Http::timeout(30)
            ->withToken($token)
            ->get($url);

        if ($response->failed()) {
            return ['status' => 'unknown', 'paid' => false];
        }

        $body   = $response->json();
        $status = data_get($body, 'status', 'pending');
        $paid   = strtolower($status) === 'success';

        return [
            'status' => $paid ? 'paid' : $status,
            'paid'   => $paid,
            'amount' => (float) data_get($body, 'amount', 0),
        ];
    }

    public function validateWebhook(array $payload, string $signature): bool
    {
        $secret   = config('services.orange_money.client_secret');
        $expected = hash_hmac('sha256', json_encode($payload), $secret);

        return hash_equals($expected, $signature);
    }

    private function detectCountry(string $phone): string
    {
        // Détection simple selon le code pays international
        if (str_starts_with($phone, '+225') || str_starts_with($phone, '225')) {
            return 'CI';
        }
        if (str_starts_with($phone, '+221') || str_starts_with($phone, '221')) {
            return 'SN';
        }
        if (str_starts_with($phone, '+237') || str_starts_with($phone, '237')) {
            return 'CM';
        }

        return 'CI';
    }
}
