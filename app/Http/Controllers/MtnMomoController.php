<?php

namespace App\Http\Controllers;

use App\Models\GatewayConfig;
use App\Models\Order;
use App\Services\MtnMomoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class MtnMomoController extends Controller
{
    public function __construct(private MtnMomoService $mtnMomo)
    {
    }

    public function initiate(Request $request, Order $order): SymfonyResponse|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $gc = GatewayConfig::forGateway('mtn_momo');
        abort_unless($gc->is_active, 404, 'Gateway non disponible');

        if (! $order->isPayable()) {
            return back()->with('error', 'Cette commande ne peut plus être payée.');
        }

        try {
            $this->mtnMomo->initiate(
                $order,
                route('billing.mtn-momo.pending', $order),
                route('webhooks.mtn-momo')
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // MTN MoMo ne redirige pas — on envoie vers la page d'attente interne
        return redirect()->route('billing.mtn-momo.pending', $order);
    }

    public function handleReturn(Request $request, Order $order): SymfonyResponse|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status === 'paid') {
            return redirect()->route('billing.index')
                ->with('success', 'Paiement confirmé ! Votre licence est active.');
        }

        // Page d'attente — le paiement mobile peut prendre quelques secondes
        return Inertia::render('Billing/MtnMomoPending', [
            'order' => $order->only(['id', 'order_number', 'total_amount', 'currency', 'status']),
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $gc = GatewayConfig::forGateway('mtn_momo');

        $valid = $this->mtnMomo->validateWebhook($request, $gc);

        if (! $valid) {
            return response()->json(['error' => 'invalid signature or status'], 400);
        }

        return response()->json(['ok' => true], 200);
    }
}
