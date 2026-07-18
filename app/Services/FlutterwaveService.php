<?php

namespace App\Services;

use App\Models\GatewayConfig;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FlutterwaveService
{
    private const BASE_URL = 'https://api.flutterwave.com/v3';

    /**
     * Initialise un paiement Flutterwave et retourne l'URL de paiement.
     *
     * @throws RuntimeException
     */
    public function initiate(Order $order, string $returnUrl): string
    {
        $gc = GatewayConfig::forGateway('flutterwave');
        $config = $gc->config ?? [];
        $secretKey = $config['secret_key'] ?? '';

        $response = Http::withToken($secretKey)
            ->timeout(30)
            ->post(self::BASE_URL.'/payments', [
                'tx_ref'       => 'FP-'.$order->id,
                'amount'       => (int) round((float) $order->total_amount),
                'currency'     => $order->currency,
                'redirect_url' => $returnUrl,
                'customer'     => [
                    'email' => $order->user?->email ?? '',
                    'name'  => $order->user?->name ?? 'Client',
                ],
                'customizations' => [
                    'title'       => 'FactPro',
                    'description' => 'Abonnement FactPro #'.$order->order_number,
                    'logo'        => '',
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Flutterwave: erreur HTTP '.$response->status());
        }

        $body = $response->json();
        $url = data_get($body, 'data.link');

        if (! $url) {
            throw new RuntimeException('Flutterwave: link absent de la réponse');
        }

        return $url;
    }

    /**
     * Vérifie une transaction via tx_ref.
     */
    public function verify(string $txRef, GatewayConfig $config): array
    {
        $secretKey = ($config->config ?? [])['secret_key'] ?? '';

        $response = Http::withToken($secretKey)
            ->timeout(30)
            ->get(self::BASE_URL.'/transactions', ['tx_ref' => $txRef]);

        return $response->json() ?? [];
    }

    /**
     * Valide un webhook Flutterwave via le header verif-hash.
     */
    public function validateWebhook(Request $request, GatewayConfig $config): bool
    {
        $secretHash = ($config->config ?? [])['secret_hash'] ?? '';

        if (empty($secretHash)) {
            return false;
        }

        $incomingHash = $request->header('verif-hash');

        return $incomingHash !== null && hash_equals($secretHash, $incomingHash);
    }
}
