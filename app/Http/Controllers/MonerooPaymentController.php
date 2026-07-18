<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MonerooService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Paiement électronique Moneroo côté client (script §4.1).
 *
 * IMPORTANT : le retour navigateur ne vaut JAMAIS confirmation de paiement.
 * Seul le webhook signé (WebhookController@moneroo) active la licence.
 */
class MonerooPaymentController extends Controller
{
    public function __construct(private MonerooService $moneroo)
    {
    }

    /** Lance le paiement : crée la transaction puis redirige vers le checkout Moneroo. */
    public function initiate(Request $request, Order $order): SymfonyResponse|RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if (! $order->isPayable()) {
            return back()->with('error', 'Cette commande ne peut plus être payée.');
        }

        if (! config('factpro.moneroo.secret_key')) {
            return back()->with('error', 'Paiement en ligne indisponible pour le moment.');
        }

        try {
            $result = $this->moneroo->initializePayment($order);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Redirection externe vers la page de paiement Moneroo (Inertia)
        return Inertia::location($result['checkout_url']);
    }

    /**
     * Page de retour après le checkout Moneroo.
     *
     * RÈGLE ABSOLUE : aucune activation de licence ici — l'état affiché reflète
     * uniquement ce que le webhook a déjà confirmé (ou non).
     */
    public function handleReturn(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        if ($order->status === 'paid') {
            return redirect()->route('billing.index')
                ->with('success', 'Paiement confirmé ! Votre licence est active.');
        }

        return redirect()->route('billing.index')
            ->with('success', 'Paiement en cours de confirmation… Votre licence sera activée automatiquement dès validation par l\'opérateur (généralement quelques secondes).');
    }
}
