<?php

namespace App\Services;

use App\Models\GatewayConfig;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayDunyaService
{
    private const API_URL     = 'https://app.paydunya.com/api/v1/checkout-invoice/create';
    private const CHECKOUT_URL = 'https://app.paydunya.com/checkout/invoice/';

    /**
     * Initialise un paiement PayDunya et retourne l'URL de paiement.
     *
     * @throws RuntimeException
     */
    public function initiate(Order $order, string $returnUrl, string $webhookUrl): string
    {
        $gc     = GatewayConfig::forGateway('paydunya');
        $config = $gc->config ?? [];

        $amount = (int) round((float) $order->total_amount);

        $payload = [
            'invoice' => [
                'items' => [
                    'item_0' => [
                        'name'        => 'Abonnement FactPro',
                        'quantity'    => 1,
                        'unit_price'  => $amount,
                        'total_price' => $amount,
                        'description' => '#'.$order->order_number,
                    ],
                ],
                'total_amount' => $amount,
                'description'  => 'Abonnement FactPro',
            ],
            'store' => [
                'name' => 'IBIG FactPro',
            ],
            'actions' => [
                'cancel_url'   => $returnUrl,
                'return_url'   => $returnUrl,
                'callback_url' => $webhookUrl,
            ],
        ];

        $response = Http::timeout(30)
            ->withHeaders([
                'PAYDUNYA-MASTER-KEY'  => $config['master_key'] ?? '',
                'PAYDUNYA-PRIVATE-KEY' => $config['private_key'] ?? '',
                'PAYDUNYA-TOKEN'       => $config['token'] ?? '',
                'PAYDUNYA-PUBLIC-KEY'  => $config['public_key'] ?? '',
            ])
            ->post(self::API_URL, $payload);

        if ($response->failed()) {
            throw new RuntimeException('PayDunya: erreur HTTP '.$response->status());
        }

        $body = $response->json();
        $code = data_get($body, 'response_code');

        if ($code !== '00') {
            throw new RuntimeException('PayDunya: '.($body['description'] ?? 'Erreur inconnue'));
        }

        $token = data_get($body, 'token');
        if (! $token) {
            throw new RuntimeException('PayDunya: token absent de la réponse');
        }

        return self::CHECKOUT_URL.$token;
    }

    /**
     * Valide le webhook PayDunya via SHA512.
     */
    public function validateWebhook(Request $request, GatewayConfig $config): bool
    {
        $cfg     = $config->config ?? [];
        $payload = $request->json()->all();

        if (($payload['response_code'] ?? '') !== '00' || ($payload['status'] ?? '') !== 'completed') {
            return false;
        }

        $txId     = $payload['transaction_id'] ?? '';
        $expected = hash('sha512', ($cfg['master_key'] ?? '').$txId);
        $received = $payload['hash'] ?? '';

        return hash_equals($expected, $received);
    }
}
