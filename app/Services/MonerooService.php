<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Intégration Moneroo — paiement électronique automatique (script §4.1 et §22).
 *
 * Les clés API restent strictement côté serveur : rien n'est jamais exposé au JS.
 */
class MonerooService
{
    public function __construct(private PaymentService $payments)
    {
    }

    /**
     * Initialise un paiement Moneroo pour une commande payable.
     *
     * Crée la PaymentTransaction locale AVANT l'appel HTTP (traçabilité),
     * puis appelle POST {base_url}/payments/initialize.
     *
     * @return array{checkout_url: string, transaction: PaymentTransaction}
     *
     * @throws RuntimeException si l'initialisation échoue côté Moneroo.
     */
    public function initializePayment(Order $order): array
    {
        $config = config('factpro.moneroo');

        if (empty($config['secret_key'])) {
            throw new RuntimeException('Moneroo n\'est pas configuré (clé secrète absente).');
        }

        $user = $order->user;
        $plan = $order->plan;

        // 1. Transaction interne d'abord (référence interne = pivot de réconciliation)
        $transaction = PaymentTransaction::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'payment_provider' => 'moneroo',
            'internal_reference' => $this->payments->internalReference(),
            'amount_expected' => $order->total_amount,
            'currency' => $order->currency,
            'status' => 'initiated',
            'initiated_at' => now(),
        ]);

        PaymentAuditLog::record('payment_launched', 'transaction', $transaction->id, null, [
            'provider' => 'moneroo',
            'order' => $order->order_number,
            'amount' => (float) $order->total_amount,
            'currency' => $order->currency,
        ]);

        // 2. Découpage du nom pour le payload client Moneroo
        $nameParts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
        $firstName = $nameParts[0] ?? 'Client';
        $lastName = $nameParts[1] ?? 'FactPro';

        $payload = [
            'amount' => (int) round((float) $order->total_amount),
            'currency' => $order->currency,
            'description' => sprintf('Abonnement %s %d mois', $plan?->name ?? 'FactPro', $order->duration_months),
            'customer' => [
                'email' => $user->email,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ],
            'return_url' => route('billing.moneroo.return', $order),
            'metadata' => [
                'order_id' => (string) $order->id,
                'transaction_id' => (string) $transaction->id,
                'internal_reference' => $transaction->internal_reference,
            ],
        ];

        // 3. Appel HTTP serveur → serveur
        try {
            $response = Http::withToken($config['secret_key'])
                ->acceptJson()
                ->timeout(30)
                ->post(rtrim($config['base_url'], '/').'/payments/initialize', $payload);
        } catch (\Throwable $e) {
            $this->failTransaction($transaction, 'Erreur réseau Moneroo : '.$e->getMessage());

            throw new RuntimeException('Impossible de contacter le service de paiement. Réessayez dans quelques minutes.', 0, $e);
        }

        $body = $response->json();
        $checkoutUrl = data_get($body, 'data.checkout_url');
        $providerId = data_get($body, 'data.id');

        if ($response->failed() || ! $checkoutUrl || ! $providerId) {
            $this->failTransaction($transaction, sprintf(
                'Initialisation Moneroo refusée (HTTP %d) : %s',
                $response->status(),
                mb_substr((string) $response->body(), 0, 500),
            ));

            throw new RuntimeException('Le service de paiement a refusé l\'initialisation. Réessayez ou choisissez un moyen manuel.');
        }

        // 4. Succès : référence fournisseur + passage en pending
        $transaction->update([
            'provider_reference' => $providerId,
            'status' => 'pending',
            'metadata' => ['moneroo_initialize_response' => $body],
        ]);

        $order->update(['status' => 'payment_initiated']);

        PaymentAuditLog::record('payment_initialized', 'transaction', $transaction->id, ['status' => 'initiated'], [
            'status' => 'pending',
            'provider_reference' => $providerId,
        ]);

        return [
            'checkout_url' => $checkoutUrl,
            'transaction' => $transaction->fresh(),
        ];
    }

    /**
     * Vérifie la signature HMAC SHA-256 d'un webhook Moneroo (corps brut).
     * Secret absent → toujours false (on ne traite jamais un webhook non vérifiable).
     */
    public function verifySignature(string $rawBody, ?string $signatureHeader): bool
    {
        $secret = (string) config('factpro.moneroo.webhook_secret');

        if ($secret === '' || $signatureHeader === null || $signatureHeader === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, trim($signatureHeader));
    }

    private function failTransaction(PaymentTransaction $transaction, string $message): void
    {
        Log::error('[Moneroo] '.$message, ['transaction_id' => $transaction->id]);

        $transaction->update(['status' => 'failed', 'notes' => mb_substr($message, 0, 1000)]);

        PaymentAuditLog::record('payment_init_failed', 'transaction', $transaction->id, ['status' => 'initiated'], [
            'status' => 'failed',
        ], reason: mb_substr($message, 0, 500));
    }
}
