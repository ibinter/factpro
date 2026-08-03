<?php

namespace App\Http\Controllers;

use App\Models\GatewayConfig;
use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\PaymentTransaction;
use App\Services\CinetPayService;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CinetPayController extends Controller
{
    public function __construct(
        private CinetPayService $cinetpay,
        private LicenseService $licenses,
    ) {
    }

    public function initiate(Request $request, Order $order): SymfonyResponse|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $gc = GatewayConfig::forGateway('cinetpay');
        abort_unless($gc->is_active, 404, 'Gateway non disponible');

        if (! $order->isPayable()) {
            return back()->with('error', 'Cette commande ne peut plus être payée.');
        }

        try {
            $url = $this->cinetpay->initiate(
                $order,
                route('billing.cinetpay.return', $order),
                route('webhooks.cinetpay')
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return Inertia::location($url);
    }

    public function handleReturn(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status === 'paid') {
            return redirect()->route('billing.index')
                ->with('success', 'Paiement confirmé ! Votre licence est active.');
        }

        return redirect()->route('billing.index')
            ->with('success', 'Paiement en cours de confirmation… Votre licence sera activée automatiquement dès validation (généralement quelques secondes).');
    }

    public function webhook(Request $request): JsonResponse
    {
        // CinetPay envoie cpm_trans_id = l'order->id passé lors de l'initiation
        $transactionId = $request->input('cpm_trans_id') ?? $request->input('transaction_id');

        if (! $transactionId) {
            return response()->json(['error' => 'missing transaction_id'], 400);
        }

        $gc = GatewayConfig::forGateway('cinetpay');
        $result = $this->cinetpay->verify($transactionId, $gc);
        $status = data_get($result, 'data.status');

        if (! in_array($status, ['ACCEPTED', 'CONFIRMED'], true)) {
            return response()->json(['status' => 'not_confirmed', 'cinetpay_status' => $status ?? 'unknown'], 200);
        }

        $order = Order::find($transactionId);

        if (! $order) {
            Log::warning('[Webhook CinetPay] Order introuvable', ['transaction_id' => $transactionId]);
            return response()->json(['status' => 'order_not_found'], 200);
        }

        $transaction = PaymentTransaction::firstOrCreate(
            ['order_id' => $order->id, 'payment_provider' => 'cinetpay'],
            [
                'user_id'            => $order->user_id,
                'provider_reference' => $transactionId,
                'status'             => 'pending',
                'currency'           => $order->currency,
                'amount_expected'    => $order->total_amount,
                'initiated_at'       => now(),
            ]
        );

        // Idempotence : déjà traité → acquitter sans ré-activer
        if (in_array($transaction->status, ['succeeded', 'manually_validated'], true)) {
            return response()->json(['status' => 'already_processed'], 200);
        }

        $amountReceived = data_get($result, 'data.amount');

        DB::transaction(function () use ($transaction, $order, $result, $amountReceived) {
            $old = ['status' => $transaction->status];

            $transaction->update([
                'status'          => 'succeeded',
                'amount_received' => $amountReceived ?? $transaction->amount_expected,
                'paid_at'         => now(),
                'confirmed_at'    => now(),
                'metadata'        => array_merge($transaction->metadata ?? [], ['cinetpay_webhook' => $result]),
            ]);

            PaymentAuditLog::record('payment_succeeded_webhook', 'transaction', $transaction->id, $old, [
                'status'          => 'succeeded',
                'amount_received' => (float) ($amountReceived ?? $transaction->amount_expected),
            ]);

            $this->licenses->activateFromOrder($order, $transaction);
        });

        return response()->json(['status' => 'processed'], 200);
    }
}
