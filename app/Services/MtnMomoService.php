<?php

namespace App\Services;

use App\Models\GatewayConfig;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MtnMomoService
{
    private function baseUrl(string $environment): string
    {
        return $environment === 'production'
            ? 'https://proxy.momoapi.mtn.com'
            : 'https://sandbox.momodeveloper.mtn.com';
    }

    /**
     * Obtient un token d'accès MTN MoMo via Basic auth.
     *
     * @throws RuntimeException
     */
    private function getAccessToken(array $config): string
    {
        $baseUrl     = $this->baseUrl($config['environment'] ?? 'sandbox');
        $credentials = base64_encode(($config['api_user'] ?? '').':'.($config['api_key'] ?? ''));

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization'              => 'Basic '.$credentials,
                'Ocp-Apim-Subscription-Key'  => $config['subscription_key'] ?? '',
            ])
            ->post($baseUrl.'/collection/token/');

        if ($response->failed()) {
            throw new RuntimeException('MTN MoMo: erreur token HTTP '.$response->status());
        }

        $token = data_get($response->json(), 'access_token');
        if (! $token) {
            throw new RuntimeException('MTN MoMo: access_token absent de la réponse token');
        }

        return $token;
    }

    /**
     * Initie une demande de paiement MTN MoMo.
     *
     * @throws RuntimeException
     */
    public function initiate(Order $order, string $returnUrl, string $webhookUrl): string
    {
        $gc     = GatewayConfig::forGateway('mtn_momo');
        $config = $gc->config ?? [];

        $token       = $this->getAccessToken($config);
        $baseUrl     = $this->baseUrl($config['environment'] ?? 'sandbox');
        $referenceId = (string) Str::uuid();

        $payload = [
            'amount'     => (string) (int) round((float) $order->total_amount),
            'currency'   => 'XOF',
            'externalId' => 'order-'.$order->id,
            'payer'      => [
                'partyIdType' => 'MSISDN',
                'partyId'     => '00000000',
            ],
            'payerMessage' => 'Abonnement FactPro',
            'payeeNote'    => 'FactPro #'.$order->order_number,
        ];

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization'              => 'Bearer '.$token,
                'X-Target-Environment'       => $config['environment'] ?? 'sandbox',
                'Ocp-Apim-Subscription-Key'  => $config['subscription_key'] ?? '',
                'X-Reference-Id'             => $referenceId,
                'Content-Type'               => 'application/json',
            ])
            ->post($baseUrl.'/collection/v1_0/requesttopay', $payload);

        if ($response->failed()) {
            throw new RuntimeException('MTN MoMo: erreur requesttopay HTTP '.$response->status());
        }

        // MTN MoMo n'a pas de redirect URL — on retourne l'URL de la page d'attente interne
        return $returnUrl;
    }

    /**
     * Valide le webhook MTN MoMo.
     */
    public function validateWebhook(Request $request, GatewayConfig $config): bool
    {
        $payload = $request->json()->all();

        return ($payload['status'] ?? '') === 'SUCCESSFUL';
    }
}
