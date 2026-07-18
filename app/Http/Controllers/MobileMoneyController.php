<?php

namespace App\Http\Controllers;

use App\Models\PaymentAuditLog;
use App\Models\PaymentTransaction;
use App\Services\LicenseService;
use App\Services\MobileMoney\MobileMoneyManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MobileMoneyController extends Controller
{
    public function __construct(private MobileMoneyManager $manager)
    {
    }

    /**
     * Page de sélection de l'opérateur Mobile Money.
     */
    public function index(): Response
    {
        return Inertia::render('MobileMoney/Checkout', [
            'drivers' => MobileMoneyManager::DRIVERS,
        ]);
    }

    /**
     * Lance un paiement Mobile Money.
     */
    public function initiate(Request $request): JsonResponse|SymfonyResponse
    {
        $data = $request->validate([
            'driver'      => 'required|string|in:'.implode(',', MobileMoneyManager::DRIVERS),
            'phone'       => 'required|string|max:20',
            'amount'      => 'required|numeric|min:1',
            'currency'    => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
            'document_id' => 'nullable|integer',
        ]);

        $reference   = 'MM-'.strtoupper(Str::random(12));
        $description = $data['description'] ?? 'Paiement FactPro';

        try {
            $result = $this->manager->pay(
                $data['driver'],
                $data['phone'],
                (float) $data['amount'],
                $data['currency'],
                $reference,
                $description,
            );
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json($result);
    }

    /**
     * Vérifie le statut d'un paiement.
     */
    public function status(Request $request, string $reference): JsonResponse
    {
        $driver = $request->query('driver');

        if (! $driver || ! in_array($driver, MobileMoneyManager::DRIVERS, true)) {
            return response()->json(['error' => 'Driver requis'], 422);
        }

        try {
            $status = $this->manager->driver($driver)->checkStatus($reference);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return response()->json($status);
    }

    /**
     * Réception d'un webhook d'un opérateur Mobile Money.
     * Valide la signature, retrouve la transaction, met à jour le statut et active la licence.
     */
    public function webhook(Request $request, string $driver): JsonResponse
    {
        if (! in_array($driver, MobileMoneyManager::DRIVERS, true)) {
            return response()->json(['error' => 'Driver inconnu'], 404);
        }

        $payload   = $request->all();
        $signature = $request->header('X-Signature')
            ?? $request->header('X-Wave-Signature')
            ?? $request->header('X-MTN-Signature')
            ?? '';

        try {
            $valid = $this->manager->driver($driver)->validateWebhook($payload, $signature);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        if (! $valid) {
            return response()->json(['error' => 'Signature invalide'], 400);
        }

        // Extraire la référence interne (passée comme external_id / client_reference lors de l'initiation)
        $reference = $payload['external_id'] ?? $payload['client_reference'] ?? $payload['externalId'] ?? null;
        if (! $reference) {
            return response()->json(['status' => 'ignored', 'reason' => 'no_reference'], 200);
        }

        return DB::transaction(function () use ($payload, $reference, $driver) {
            // Retrouver la transaction (avec verrou anti-doublon)
            $transaction = PaymentTransaction::where('internal_reference', $reference)
                ->orWhere('provider_reference', $reference)
                ->lockForUpdate()
                ->first();

            if (! $transaction) {
                return response()->json(['status' => 'ignored', 'reason' => 'transaction_not_found'], 200);
            }

            // Idempotence : ne pas retraiter une transaction déjà finalisée
            if (in_array($transaction->status, ['succeeded', 'manually_validated'])) {
                return response()->json(['status' => 'already_processed'], 200);
            }

            $rawStatus = strtoupper($payload['status'] ?? '');
            $isPaid    = in_array($rawStatus, ['SUCCESSFUL', 'SUCCESS', 'COMPLETED', 'APPROVED', 'SUCCEEDED']);
            $isFailed  = in_array($rawStatus, ['FAILED', 'REJECTED', 'CANCELLED', 'EXPIRED']);

            if ($isPaid) {
                $receivedAmount = (float) ($payload['amount'] ?? 0);
                $expectedAmount = (float) $transaction->amount_expected;

                // Détection d'écart de montant → mise en revue
                if ($receivedAmount > 0 && abs($receivedAmount - $expectedAmount) > 1) {
                    $transaction->update([
                        'status'   => 'under_review',
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'received_amount' => $receivedAmount,
                            'raw_payload'     => $payload,
                            'fraud_flags'     => ['amount_mismatch'],
                            'webhook_driver'  => $driver,
                        ]),
                    ]);

                    PaymentAuditLog::record('webhook_amount_mismatch', 'transaction', $transaction->id, null, [
                        'expected' => $expectedAmount,
                        'received' => $receivedAmount,
                        'driver'   => $driver,
                    ]);

                    return response()->json(['status' => 'flagged'], 200);
                }

                // Mise à jour de la transaction
                $transaction->update([
                    'status'             => 'succeeded',
                    'amount_received'    => $receivedAmount ?: $expectedAmount,
                    'provider_reference' => $payload['id'] ?? $payload['financialTransactionId'] ?? $transaction->provider_reference,
                    'confirmed_at'       => now(),
                    'metadata'           => array_merge($transaction->metadata ?? [], [
                        'raw_payload'    => $payload,
                        'webhook_driver' => $driver,
                    ]),
                ]);

                // Mise à jour de la commande et activation de la licence
                $order = $transaction->order;
                if ($order) {
                    $order->update(['status' => 'paid', 'paid_at' => now()]);

                    app(LicenseService::class)->activateFromOrder($order, $transaction);

                    PaymentAuditLog::record('webhook_license_activated', 'transaction', $transaction->id, null, [
                        'driver'   => $driver,
                        'order_id' => $order->id,
                    ]);
                }
            } elseif ($isFailed) {
                $transaction->update([
                    'status'   => 'failed',
                    'metadata' => array_merge($transaction->metadata ?? [], [
                        'raw_payload'    => $payload,
                        'webhook_driver' => $driver,
                    ]),
                ]);

                if ($transaction->order) {
                    $transaction->order->update(['status' => 'rejected']);
                }
            }

            return response()->json(['status' => 'processed'], 200);
        });
    }

    /**
     * Détecte automatiquement le driver depuis un numéro et un pays.
     */
    public function detectDriver(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'   => 'required|string|max:20',
            'country' => 'required|string|size:2',
        ]);

        $driver = $this->manager->detectDriver($data['phone'], strtoupper($data['country']));

        return response()->json(['driver' => $driver]);
    }
}
