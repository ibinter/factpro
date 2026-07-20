<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\DeliveryOrder;
use App\Services\DeliveryPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryAdminController extends Controller
{
    /** Liste toutes les livraisons avec filtres. */
    public function index(Request $request): Response
    {
        $query = DeliveryOrder::with(['order.user', 'agent'])
            ->orderByDesc('created_at');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($city = $request->get('city')) {
            $query->where('delivery_city', 'like', "%{$city}%");
        }

        if ($agentId = $request->get('agent_id')) {
            $query->where('delivery_agent_id', $agentId);
        }

        $deliveries = $query->paginate(20)->withQueryString();

        $agents = DeliveryAgent::active()->orderBy('name')->get(['id', 'name', 'city', 'zone']);

        return Inertia::render('Admin/DeliveryBoard', [
            'deliveries' => $deliveries,
            'agents'     => $agents,
            'filters'    => $request->only(['status', 'city', 'agent_id']),
        ]);
    }

    /** Assigne un agent à une livraison. */
    public function assign(Request $request, DeliveryOrder $delivery): RedirectResponse
    {
        $data = $request->validate([
            'delivery_agent_id' => 'required|exists:delivery_agents,id',
        ]);

        $delivery->update([
            'delivery_agent_id' => $data['delivery_agent_id'],
            'status'            => 'assigned',
            'assigned_at'       => now(),
        ]);

        return redirect()->route('admin.deliveries.index')->with('success', 'Agent assigné avec succès.');
    }

    /** Confirme la réception du paiement (admin ou agent). */
    public function confirmPayment(Request $request, DeliveryOrder $delivery): RedirectResponse
    {
        $data = $request->validate([
            'amount_received' => 'required|numeric|min:0',
            'agent_notes'     => 'nullable|string|max:500',
        ]);

        if ($delivery->status === 'payment_received') {
            return redirect()->route('admin.deliveries.index')
                ->with('error', 'Ce paiement a déjà été confirmé.');
        }

        app(DeliveryPaymentService::class)->confirmPaymentReceived(
            $delivery,
            (float) $data['amount_received'],
            $request->user()->id,
            $data['agent_notes'] ?? '',
        );

        return redirect()->route('admin.deliveries.index')->with('success', 'Paiement confirmé. La licence a été activée.');
    }
}
