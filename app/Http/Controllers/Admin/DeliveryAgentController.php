<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeliveryAgentController extends Controller
{
    public function index(): Response
    {
        $agents = DeliveryAgent::withTrashed()->orderBy('name')->paginate(20);

        return Inertia::render('Admin/DeliveryAgents', [
            'agents' => $agents,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:150',
            'phone'   => 'nullable|string|max:30',
            'email'   => 'nullable|email|max:150',
            'zone'    => 'nullable|string|max:100',
            'city'    => 'nullable|string|max:100',
            'country' => 'nullable|string|max:10',
            'user_id' => 'nullable|exists:users,id',
        ]);

        DeliveryAgent::create($data);

        return redirect()->route('admin.delivery-agents.index')->with('success', 'Agent créé.');
    }

    public function update(Request $request, DeliveryAgent $deliveryAgent): RedirectResponse
    {
        $data = $request->validate([
            'name'      => 'sometimes|required|string|max:150',
            'phone'     => 'nullable|string|max:30',
            'email'     => 'nullable|email|max:150',
            'zone'      => 'nullable|string|max:100',
            'city'      => 'nullable|string|max:100',
            'country'   => 'nullable|string|max:10',
            'is_active' => 'sometimes|boolean',
            'user_id'   => 'nullable|exists:users,id',
        ]);

        $deliveryAgent->update($data);

        return redirect()->route('admin.delivery-agents.index')->with('success', 'Agent mis à jour.');
    }

    public function destroy(DeliveryAgent $deliveryAgent): RedirectResponse
    {
        $deliveryAgent->delete();

        return redirect()->route('admin.delivery-agents.index')->with('success', 'Agent désactivé.');
    }
}
