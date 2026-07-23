<?php

namespace App\Services;

use App\Models\GatewayConfig;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StripeService
{
    private const API_URL = 'https://api.stripe.com/v1/checkout/sessions';

    /**
     * Initialise une session Stripe Checkout et retourne l'URL de paiement.
     *
     * @throws RuntimeException
     */
    public function initiate(Order $order, string $returnUrl, string $webhookUrl): string
    {
        $gc     = GatewayConfig::forGateway('stripe');
        $config = $gc->config ?? [];

        // Stripe utilise des centimes (ou unités de la monnaie sans décimales pour XOF)
        $amountInCents = (int) round((float) $order->total_amount);

        $response = Http::timeout(30)
            ->withBasicAuth($config['secret_key'] ?? '', '')
            ->asForm()
            ->post(self::API_URL, [
                'mode'                                                   => 'payment',
                'success_url'                                            => $returnUrl,
                'cancel_url'                                             => $returnUrl,
                'line_items[0][price_data][currency]'                    => 'xof',
                'line_items[0][price_data][unit_amount]'                 => $amountInCents,
                'line_items[0][price_data][product_data][name]'          => 'Abonnement FactPro',
                'line_items[0][quantity]'                                => 1,
                'metadata[order_id]'                                     => $order->id,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Stripe: erreur HTTP '.$response->status());
        }

        $body = $response->json();
        $url  = data_get($body, 'url');

        if (! $url) {
            throw new RuntimeException('Stripe: url absent de la réponse');
        }

        return $url;
    }

    /**
     * Valide le webhook Stripe via HMAC-SHA256 (format t=.../v1=...).
     */
    public function validateWebhook(Request $request, GatewayConfig $config): bool
    {
        $cfg           = $config->config ?? [];
        $webhookSecret = $cfg['webhook_secret'] ?? '';
        $sigHeader     = $request->header('Stripe-Signature', '');
        $body          = $request->getContent();

        // Parser le header Stripe-Signature : t=timestamp,v1=hash,...
        $parts     = explode(',', $sigHeader);
        $timestamp = null;
        $v1hash    = null;

        foreach ($parts as $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, null);
            if ($key === 't') {
                $timestamp = $value;
            } elseif ($key === 'v1') {
                $v1hash = $value;
            }
        }

        if (! $timestamp || ! $v1hash) {
            return false;
        }

        $signedPayload = $timestamp.'.'.$body;
        $expected      = hash_hmac('sha256', $signedPayload, $webhookSecret);

        if (! hash_equals($expected, $v1hash)) {
            return false;
        }

        $payload = $request->json()->all();

        return ($payload['type'] ?? '') === 'checkout.session.completed'
            && ($payload['data']['object']['payment_status'] ?? '') === 'paid';
    }
}
