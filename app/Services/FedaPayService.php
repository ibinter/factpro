<?php

namespace App\Services;

use App\Models\GatewayConfig;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FedaPayService
{
    private const BASE_URL = 'https://api.fedapay.com/v1';

    /**
     * Initialise une transaction FedaPay et retourne l'URL de checkout.
     *
     * @throws RuntimeException
     */
    public function initiate(Order $order, string $returnUrl): string
    {
        $gc = GatewayConfig::forGateway('fedapay');
        $config = $gc->config ?? [];
        $secretKey = $config['secret_key'] ?? '';

        // 1. Créer la transaction
        $response = Http::withToken($secretKey)
            ->timeout(30)
            ->post(self::BASE_URL.'/transactions', [
                'description' => 'Abonnement FactPro #'.$order->order_number,
                'amount'      => (int) round((float) $order->total_amount),
                'currency'    => ['iso' => $order->currency],
                'callback_url' => $returnUrl,
                'customer'    => [
                    'email'     => $order->user?->email ?? '',
                    'lastname'  => $order->user?->name ?? 'Client',
                    'firstname' => '',
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('FedaPay: erreur HTTP '.$response->status());
        }

        $body = $response->json();
        $transactionId = data_get($body, 'v1/transaction.id');

        if (! $transactionId) {
            throw new RuntimeException('FedaPay: ID transaction absent de la réponse');
        }

        // 2. Générer le token de paiement
        $tokenResponse = Http::withToken($secretKey)
            ->timeout(30)
            ->post(self::BASE_URL."/transactions/{$transactionId}/token");

        if ($tokenResponse->failed()) {
            throw new RuntimeException('FedaPay: impossible de générer le token de paiement');
        }

        $tokenBody = $tokenResponse->json();
        $url = data_get($tokenBody, 'url');

        if (! $url) {
            throw new RuntimeException('FedaPay: URL checkout absente de la réponse');
        }

        return $url;
    }

    /**
     * Vérifie une transaction FedaPay.
     */
    public function verify(string $transactionId, GatewayConfig $config): array
    {
        $secretKey = ($config->config ?? [])['secret_key'] ?? '';

        $response = Http::withToken($secretKey)
            ->timeout(30)
            ->get(self::BASE_URL."/transactions/{$transactionId}");

        return $response->json() ?? [];
    }
}
