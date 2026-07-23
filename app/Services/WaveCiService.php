<?php

namespace App\Services;

use App\Models\GatewayConfig;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WaveCiService
{
    private const API_URL = 'https://api.wave.com/v1/checkout/sessions';

    /**
     * Initialise un paiement Wave CI et retourne l'URL de paiement.
     *
     * @throws RuntimeException
     */
    public function initiate(Order $order, string $returnUrl, string $webhookUrl): string
    {
        $gc = GatewayConfig::forGateway('wave_ci');
        $config = $gc->config ?? [];

        $payload = [
            'amount'           => (string) (int) round((float) $order->total_amount),
            'currency'         => 'XOF',
            'error_url'        => $returnUrl,
            'success_url'      => $returnUrl,
            'client_reference' => 'order-'.$order->id,
        ];

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer '.($config['api_key'] ?? ''),
                'Content-Type'  => 'application/json',
            ])
            ->post(self::API_URL, $payload);

        if ($response->failed()) {
            throw new RuntimeException('Wave CI: erreur HTTP '.$response->status());
        }

        $body = $response->json();
        $url = data_get($body, 'wave_launch_url');

        if (! $url) {
            throw new RuntimeException('Wave CI: wave_launch_url absent de la réponse');
        }

        return $url;
    }

    /**
     * Valide le webhook Wave CI via HMAC-SHA256.
     */
    public function validateWebhook(Request $request, GatewayConfig $config): bool
    {
        $cfg = $config->config ?? [];
        $secretKey = $cfg['secret_key'] ?? '';

        $signature = $request->header('X-Wave-Signature');
        if (! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secretKey);

        if (! hash_equals($expected, $signature)) {
            return false;
        }

        $payload = $request->json()->all();

        return ($payload['checkout_status'] ?? '') === 'complete';
    }
}
