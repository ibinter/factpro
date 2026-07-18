<?php

namespace App\Services\MobileMoney;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MoovMoneyService implements MobileMoneyDriver
{
    private const BASE_URL = 'https://api.moov-africa.bj/flooz/v1';

    public function initiate(string $phone, float $amount, string $currency, string $reference, string $description): array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer '.config('services.moov_money.api_key'),
                'Content-Type'  => 'application/json',
            ])
            ->post(self::BASE_URL.'/payment/request', [
                'merchant_id'  => config('services.moov_money.merchant_id'),
                'phone'        => ltrim($phone, '+'),
                'amount'       => (int) $amount,
                'currency'     => $currency,
                'order_id'     => $reference,
                'description'  => $description,
                'callback_url' => config('app.url').'/webhooks/mobile-money/moov_money',
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Moov Money: erreur HTTP '.$response->status());
        }

        $body   = $response->json();
        $status = data_get($body, 'status', 'error');

        if (! in_array($status, ['pending', 'success', '200', 0], true)) {
            throw new RuntimeException('Moov Money: '.(data_get($body, 'message') ?? 'Erreur inconnue'));
        }

        return [
            'reference'    => $reference,
            'status'       => 'pending',
            'instructions' => 'Vous allez recevoir une notification USSD Flooz sur votre téléphone Moov. Entrez votre code secret pour valider le paiement.',
        ];
    }

    public function checkStatus(string $reference): array
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer '.config('services.moov_money.api_key'),
            ])
            ->get(self::BASE_URL.'/payment/status/'.$reference);

        if ($response->failed()) {
            return ['status' => 'unknown', 'paid' => false];
        }

        $body   = $response->json();
        $status = data_get($body, 'status', 'pending');
        $paid   = in_array($status, ['success', 'SUCCESSFUL', 'paid'], true);

        return [
            'status' => $paid ? 'paid' : $status,
            'paid'   => $paid,
            'amount' => (float) data_get($body, 'amount', 0),
        ];
    }

    public function validateWebhook(array $payload, string $signature): bool
    {
        $secret   = config('services.moov_money.api_key');
        $expected = hash_hmac('sha256', json_encode($payload), $secret);

        return hash_equals($expected, $signature);
    }
}
