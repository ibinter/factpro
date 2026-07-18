<?php

namespace App\Services;

use App\Models\GatewayConfig;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CinetPayService
{
    private const API_URL = 'https://api-checkout.cinetpay.com/v2/payment';

    /**
     * Initialise un paiement CinetPay et retourne l'URL de paiement.
     *
     * @throws RuntimeException
     */
    public function initiate(Order $order, string $returnUrl, string $notifyUrl): string
    {
        $gc = GatewayConfig::forGateway('cinetpay');
        $config = $gc->config ?? [];

        $payload = [
            'apikey'         => $config['api_key'] ?? '',
            'site_id'        => $config['site_id'] ?? '',
            'transaction_id' => (string) $order->id,
            'amount'         => (int) round((float) $order->total_amount),
            'currency'       => $order->currency,
            'description'    => 'Abonnement FactPro #'.$order->order_number,
            'return_url'     => $returnUrl,
            'notify_url'     => $notifyUrl,
            'customer_name'  => $order->user?->name ?? 'Client',
            'customer_email' => $order->user?->email ?? '',
            'customer_phone_number' => '',
            'customer_address' => '',
            'customer_city'  => '',
            'customer_country' => $order->user?->country ?? 'CI',
            'customer_state' => '',
            'customer_zip_code' => '',
            'channels'       => 'ALL',
            'lang'           => 'fr',
        ];

        $response = Http::timeout(30)->post(self::API_URL, $payload);

        if ($response->failed()) {
            throw new RuntimeException('CinetPay: erreur HTTP '.$response->status());
        }

        $body = $response->json();
        $code = data_get($body, 'code');

        if ($code !== '201') {
            throw new RuntimeException('CinetPay: '.(data_get($body, 'message') ?? 'Erreur inconnue'));
        }

        $url = data_get($body, 'data.payment_url');
        if (! $url) {
            throw new RuntimeException('CinetPay: payment_url absent de la réponse');
        }

        return $url;
    }

    /**
     * Vérifie le statut d'une transaction CinetPay.
     */
    public function verify(string $transactionId, GatewayConfig $config): array
    {
        $cfg = $config->config ?? [];

        $response = Http::timeout(30)->post(self::API_URL.'/check', [
            'apikey'         => $cfg['api_key'] ?? '',
            'site_id'        => $cfg['site_id'] ?? '',
            'transaction_id' => $transactionId,
        ]);

        return $response->json() ?? [];
    }

    /**
     * Valide le webhook CinetPay (vérifie via l'API).
     * CinetPay envoie cif_code dans le body ; on revalide via l'API verify.
     */
    public function validateWebhook(Request $request, GatewayConfig $config): bool
    {
        $transactionId = $request->input('cpm_trans_id') ?? $request->input('transaction_id');

        if (! $transactionId) {
            return false;
        }

        $result = $this->verify($transactionId, $config);
        $code = data_get($result, 'data.status');

        return in_array($code, ['ACCEPTED', 'CONFIRMED'], true);
    }
}
