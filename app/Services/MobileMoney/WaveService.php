<?php

namespace App\Services\MobileMoney;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WaveService implements MobileMoneyDriver
{
    private const BASE_URL = 'https://api.wave.com/v1';

    public function initiate(string $phone, float $amount, string $currency, string $reference, string $description): array
    {
        $response = Http::timeout(30)
            ->withToken(config('services.wave.api_key'))
            ->post(self::BASE_URL.'/checkout/sessions', [
                'amount'           => (string) $amount,
                'currency'         => $currency,
                'client_reference' => $reference,
                'error_url'        => config('app.url').'/mobile-money/status/'.$reference.'?result=error',
                'success_url'      => config('app.url').'/mobile-money/status/'.$reference.'?result=success',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Wave: erreur HTTP '.$response->status());
        }

        $body = $response->json();

        return [
            'checkout_url' => data_get($body, 'wave_launch_url'),
            'reference'    => data_get($body, 'id', $reference),
            'status'       => 'pending',
            'instructions' => 'Vous allez être redirigé vers l\'application Wave pour confirmer le paiement.',
        ];
    }

    public function checkStatus(string $reference): array
    {
        $response = Http::timeout(30)
            ->withToken(config('services.wave.api_key'))
            ->get(self::BASE_URL.'/checkout/sessions/'.$reference);

        if ($response->failed()) {
            return ['status' => 'unknown', 'paid' => false];
        }

        $body   = $response->json();
        $status = data_get($body, 'payment_status', 'pending');
        $paid   = $status === 'succeeded';

        return [
            'status' => $paid ? 'paid' : $status,
            'paid'   => $paid,
            'amount' => (float) data_get($body, 'amount', 0),
        ];
    }

    public function validateWebhook(array $payload, string $signature): bool
    {
        $expected = hash_hmac('sha256', json_encode($payload), config('services.wave.secret'));

        return hash_equals($expected, $signature);
    }
}
