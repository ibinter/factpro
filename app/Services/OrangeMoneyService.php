<?php

namespace App\Services;

use App\Models\GatewayConfig;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OrangeMoneyService
{
    private const TOKEN_URL   = 'https://api.orange.com/oauth/v3/token';
    private const PAYMENT_URL = 'https://api.orange.com/orange-money-webpay/ci/v1/webpayment';

    /**
     * Obtient un token d'accès Orange Money via Basic auth.
     *
     * @throws RuntimeException
     */
    private function getAccessToken(array $config): string
    {
        $credentials = base64_encode(($config['client_id'] ?? '').':'.($config['client_secret'] ?? ''));

        $response = Http::timeout(30)
            ->withHeaders(['Authorization' => 'Basic '.$credentials])
            ->asForm()
            ->post(self::TOKEN_URL, ['grant_type' => 'client_credentials']);

        if ($response->failed()) {
            throw new RuntimeException('Orange Money: erreur token HTTP '.$response->status());
        }

        $token = data_get($response->json(), 'access_token');
        if (! $token) {
            throw new RuntimeException('Orange Money: access_token absent de la réponse token');
        }

        return $token;
    }

    /**
     * Initialise un paiement Orange Money WebPay CI et retourne l'URL de paiement.
     *
     * @throws RuntimeException
     */
    public function initiate(Order $order, string $returnUrl, string $webhookUrl): string
    {
        $gc     = GatewayConfig::forGateway('orange_money');
        $config = $gc->config ?? [];

        $token = $this->getAccessToken($config);

        $payload = [
            'merchant_key' => $config['merchant_key'] ?? '',
            'currency'     => 'OUV',
            'order_id'     => $order->order_number,
            'amount'       => (int) round((float) $order->total_amount),
            'return_url'   => $returnUrl,
            'cancel_url'   => $returnUrl,
            'notif_url'    => $webhookUrl,
            'lang'         => 'fr',
            'reference'    => 'FactPro #'.$order->order_number,
        ];

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type'  => 'application/json',
            ])
            ->post(self::PAYMENT_URL, $payload);

        if ($response->failed()) {
            throw new RuntimeException('Orange Money: erreur HTTP '.$response->status());
        }

        $body = $response->json();
        $url  = data_get($body, 'payment_url');

        if (! $url) {
            throw new RuntimeException('Orange Money: payment_url absent de la réponse');
        }

        return $url;
    }

    /**
     * Valide le webhook Orange Money.
     */
    public function validateWebhook(Request $request, GatewayConfig $config): bool
    {
        $cfg     = $config->config ?? [];
        $payload = $request->json()->all();

        if (($payload['status'] ?? '') !== 'SUCCESS') {
            return false;
        }

        $orderId     = $payload['order_id'] ?? '';
        $amount      = $payload['amount'] ?? '';
        $currency    = $payload['currency'] ?? '';
        $notifToken  = $payload['notif_token'] ?? '';
        $merchantKey = $cfg['merchant_key'] ?? '';
        $notifKey    = $cfg['notif_key'] ?? '';

        $expected = md5($merchantKey.$orderId.$amount.$currency.$notifKey);

        return hash_equals($expected, $notifToken);
    }
}
