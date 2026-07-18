<?php

namespace App\Http\Controllers;

use App\Models\PaymentAuditLog;
use App\Models\PaymentTransaction;
use App\Models\WebhookEvent;
use App\Services\LicenseService;
use App\Services\MonerooService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Réception des webhooks de paiement (script §22).
 *
 * Flux : stocker AVANT de traiter → vérifier la signature → idempotence →
 * réconciliation montant/devise/référence → activation (webhook = seule source de vérité).
 */
class WebhookController extends Controller
{
    /** Types d'événements Moneroo reconnus comme succès / échec / annulation. */
    private const SUCCESS_EVENTS = ['payment.success', 'payment.succeeded'];
    private const FAILED_EVENTS = ['payment.failed'];
    private const CANCELLED_EVENTS = ['payment.cancelled', 'payment.canceled'];

    public function __construct(
        private MonerooService $moneroo,
        private LicenseService $licenses,
    ) {
    }

    public function moneroo(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $payload = json_decode($rawBody, true) ?: [];
        $signatureHeader = $request->header('X-Moneroo-Signature');

        $eventType = $payload['event'] ?? $payload['type'] ?? null;
        $eventId = $payload['id'] ?? data_get($payload, 'data.id') ?? hash('sha256', $rawBody);

        // a. Stockage IMMÉDIAT de l'événement, avant tout traitement
        try {
            $event = WebhookEvent::create([
                'provider' => 'moneroo',
                'event_type' => $eventType ? mb_substr($eventType, 0, 60) : null,
                'event_id' => $eventId,
                'payload' => $payload ?: ['raw' => mb_substr($rawBody, 0, 10000)],
                'signature_header' => $signatureHeader,
                'signature_valid' => false,
                'processed' => false,
            ]);
        } catch (UniqueConstraintViolationException) {
            // c. Doublon (contrainte unique provider+event_id) → déjà reçu, on acquitte
            return response()->json(['status' => 'already_received'], 200);
        }

        // b. Vérification de la signature sur le corps BRUT
        if (! $this->moneroo->verifySignature($rawBody, $signatureHeader)) {
            $event->update([
                'signature_valid' => false,
                'error_message' => 'Signature webhook invalide ou secret non configuré.',
            ]);

            Log::warning('[Webhook Moneroo] Signature invalide', ['event_id' => $eventId, 'ip' => $request->ip()]);

            return response()->json(['error' => 'invalid signature'], 400);
        }

        $event->update(['signature_valid' => true]);

        // c. Idempotence applicative : un événement identique déjà traité → 200 direct
        $alreadyProcessed = WebhookEvent::where('provider', 'moneroo')
            ->where('event_id', $eventId)
            ->where('processed', true)
            ->where('id', '!=', $event->id)
            ->exists();

        if ($alreadyProcessed) {
            $event->update(['processed' => true, 'processed_at' => now(), 'error_message' => 'Doublon — déjà traité.']);

            return response()->json(['status' => 'already_processed'], 200);
        }

        // d. Réconciliation : retrouver la transaction par référence interne (metadata) ou référence fournisseur
        $internalReference = data_get($payload, 'data.metadata.internal_reference')
            ?? data_get($payload, 'metadata.internal_reference');
        $providerReference = data_get($payload, 'data.id') ?? data_get($payload, 'data.payment_id');

        $transaction = null;

        if ($internalReference) {
            $transaction = PaymentTransaction::where('internal_reference', $internalReference)
                ->where('payment_provider', 'moneroo')
                ->first();
        }

        if (! $transaction && $providerReference) {
            $transaction = PaymentTransaction::where('provider_reference', $providerReference)
                ->where('payment_provider', 'moneroo')
                ->first();
        }

        if (! $transaction) {
            $event->update([
                'error_message' => 'Aucune transaction correspondante (référence interne/fournisseur introuvable).',
            ]);

            Log::warning('[Webhook Moneroo] Transaction introuvable', ['event_id' => $eventId]);

            // 200 : l'événement est archivé pour investigation, inutile que Moneroo réessaie
            return response()->json(['status' => 'transaction_not_found'], 200);
        }

        $order = $transaction->order;
        $event->update(['order_id' => $order?->id, 'transaction_id' => $transaction->id]);

        // Vérification montant + devise (uniquement bloquante pour un succès)
        $amountReceived = data_get($payload, 'data.amount');
        $currencyReceived = data_get($payload, 'data.currency');

        $isSuccess = in_array($eventType, self::SUCCESS_EVENTS, true);

        if ($isSuccess) {
            $amountMismatch = $amountReceived !== null
                && (int) round((float) $transaction->amount_expected) !== (int) round((float) $amountReceived);
            $currencyMismatch = $currencyReceived !== null
                && strtoupper((string) $currencyReceived) !== strtoupper((string) $transaction->currency);

            if ($amountMismatch || $currencyMismatch) {
                $message = sprintf(
                    'Écart webhook : attendu %s %s, reçu %s %s.',
                    $transaction->amount_expected,
                    $transaction->currency,
                    $amountReceived ?? '?',
                    $currencyReceived ?? '?',
                );

                DB::transaction(function () use ($transaction, $event, $message, $order) {
                    $old = ['status' => $transaction->status];

                    $transaction->update(['status' => 'under_review', 'notes' => $message]);
                    $order?->update(['status' => 'under_review']);

                    $event->update([
                        'processed' => true,
                        'processed_at' => now(),
                        'error_message' => $message,
                    ]);

                    PaymentAuditLog::record('webhook_amount_mismatch', 'transaction', $transaction->id, $old, [
                        'status' => 'under_review',
                    ], reason: $message);
                });

                return response()->json(['status' => 'under_review'], 200);
            }

            // e. Succès cohérent → paiement confirmé + activation idempotente de la licence
            DB::transaction(function () use ($transaction, $event, $order, $amountReceived, $payload) {
                $old = ['status' => $transaction->status];

                $transaction->update([
                    'status' => 'succeeded',
                    'amount_received' => $amountReceived ?? $transaction->amount_expected,
                    'paid_at' => now(),
                    'confirmed_at' => now(),
                    'metadata' => array_merge($transaction->metadata ?? [], ['moneroo_webhook' => $payload]),
                ]);

                PaymentAuditLog::record('payment_succeeded_webhook', 'transaction', $transaction->id, $old, [
                    'status' => 'succeeded',
                    'amount_received' => (float) ($amountReceived ?? $transaction->amount_expected),
                ]);

                if ($order) {
                    $this->licenses->activateFromOrder($order, $transaction); // idempotent
                }

                $event->update(['processed' => true, 'processed_at' => now()]);
            });

            return response()->json(['status' => 'processed'], 200);
        }

        // f. Échec ou annulation
        if (in_array($eventType, self::FAILED_EVENTS, true) || in_array($eventType, self::CANCELLED_EVENTS, true)) {
            $newStatus = in_array($eventType, self::CANCELLED_EVENTS, true) ? 'cancelled' : 'failed';

            DB::transaction(function () use ($transaction, $event, $newStatus, $eventType) {
                $old = ['status' => $transaction->status];

                // Ne pas rétrograder une transaction déjà aboutie
                if (! in_array($transaction->status, ['succeeded', 'manually_validated'], true)) {
                    $transaction->update(['status' => $newStatus]);

                    PaymentAuditLog::record('payment_'.$newStatus.'_webhook', 'transaction', $transaction->id, $old, [
                        'status' => $newStatus,
                    ], reason: 'Webhook Moneroo : '.$eventType);
                }

                $event->update(['processed' => true, 'processed_at' => now()]);
            });

            return response()->json(['status' => 'processed'], 200);
        }

        // Type d'événement non géré : archiver et acquitter
        $event->update([
            'processed' => true,
            'processed_at' => now(),
            'error_message' => 'Type d\'événement non géré : '.($eventType ?? 'inconnu'),
        ]);

        // g. Toujours 200 en fin de traitement
        return response()->json(['status' => 'ignored'], 200);
    }
}
