<?php

namespace App\Http\Controllers;

use App\Models\GatewayConfig;
use App\Models\Order;
use App\Services\FlutterwaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class FlutterwaveController extends Controller
{
    public function __construct(private FlutterwaveService $flutterwave)
    {
    }

    public function initiate(Request $request, Order $order): SymfonyResponse|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        $gc = GatewayConfig::forGateway('flutterwave');
        abort_unless($gc->is_active, 404, 'Gateway non disponible');

        if (! $order->isPayable()) {
            return back()->with('error', 'Cette commande ne peut plus être payée.');
        }

        try {
            $url = $this->flutterwave->initiate(
                $order,
                route('billing.flutterwave.return', $order)
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
        $gc = GatewayConfig::forGateway('flutterwave');

        if (! $this->flutterwave->validateWebhook($request, $gc)) {
            return response()->json(['error' => 'invalid hash'], 400);
        }

        return response()->json(['ok' => true], 200);
    }
}
