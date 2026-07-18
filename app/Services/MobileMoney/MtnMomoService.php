<?php

namespace App\Services\MobileMoney;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MtnMomoService implements MobileMoneyDriver
{
    private function baseUrl(): string
    {
        $env = config('services.mtn_momo.environment', 'sandbox');

        return $env === 'production'
            ? 'https://proxy.momoapi.mtn.com'
            : 'https://sandbox.momodeveloper.mtn.com';
    }

    private function getAccessToken(): string
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Ocp-Apim-Subscription-Key' => config('services.mtn_momo.subscription_key'),
            ])
            ->withBasicAuth(
                config('services.mtn_momo.api_user'),
                config('services.mtn_momo.api_key')
            )
            ->post($this->baseUrl().'/collection/token/');

        if ($response->failed()) {
            throw new RuntimeException('MTN MoMo: impossible d\'obtenir le token');
        }

        $token = data_get($response->json(), 'access_token');
        if (! $token) {
            throw new RuntimeException('MTN MoMo: access_token absent de la réponse');
        }

        return $token;
    }

    public function initiate(string $phone, float $amount, string $currency, string $reference, string $description): array
    {
        $token = $this->getAccessToken();

        $response = Http::timeout(30)
            ->withHeaders([
                'X-Reference-Id'            => $reference,
                'X-Target-Environment'      => config('services.mtn_momo.environment', 'sandbox'),
                'Ocp-Apim-Subscription-Key' => config('services.mtn_momo.subscription_key'),
                'Authorization'             => 'Bearer '.$token,
                'Content-Type'              => 'application/json',
            ])
            ->post($this->baseUrl().'/collection/v1_0/requesttopay', [
                'amount'     => (string) $amount,
                'currency'   => $currency,
                'externalId' => $reference,
                'payer'      => [
                    'partyIdType' => 'MSISDN',
                    'partyId'     => ltrim($phone, '+'),
                ],
                'payerMessage' => $description,
                'payeeNote'    => $description,
            ]);

        // MTN retourne 202 Accepted pour une demande de paiement réussie
        if ($response->status() !== 202) {
            throw new RuntimeException('MTN MoMo: erreur HTTP '.$response->status());
        }

        return [
            'reference'    => $reference,
            'status'       => 'pending',
            'instructions' => 'Vous allez recevoir une notification USSD sur votre téléphone MTN pour approuver le paiement. Entrez votre PIN pour confirmer.',
        ];
    }

    public function checkStatus(string $reference): array
    {
        $token = $this->getAccessToken();

        $response = Http::timeout(30)
            ->withHeaders([
                'X-Target-Environment'      => config('services.mtn_momo.environment', 'sandbox'),
                'Ocp-Apim-Subscription-Key' => config('services.mtn_momo.subscription_key'),
                'Authorization'             => 'Bearer '.$token,
            ])
            ->get($this->baseUrl().'/collection/v1_0/requesttopay/'.$reference);

        if ($response->failed()) {
            return ['status' => 'unknown', 'paid' => false];
        }

        $body   = $response->json();
        $status = data_get($body, 'status', 'PENDING');
        $paid   = $status === 'SUCCESSFUL';

        return [
            'status' => $paid ? 'paid' : strtolower($status),
            'paid'   => $paid,
            'amount' => (float) data_get($body, 'amount', 0),
        ];
    }

    public function validateWebhook(array $payload, string $signature): bool
    {
        $secret   = config('services.mtn_momo.api_key');
        $expected = hash_hmac('sha256', json_encode($payload), $secret);

        return hash_equals($expected, $signature);
    }
}
