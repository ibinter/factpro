<?php

namespace App\Http\Controllers;

use App\Models\GatewayConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestion des passerelles de paiement africaines (Phase 8).
 * Accessible uniquement aux superadmins.
 */
class GatewayConfigController extends Controller
{
    private const DEFAULTS = [
        [
            'gateway'              => 'moneroo',
            'supported_countries'  => ['CI', 'SN', 'BF', 'ML', 'GN', 'BJ', 'TG', 'CM'],
            'supported_currencies' => ['XOF', 'XAF'],
        ],
        [
            'gateway'              => 'cinetpay',
            'supported_countries'  => ['CI', 'SN', 'BF', 'ML', 'GN', 'CM'],
            'supported_currencies' => ['XOF', 'XAF'],
        ],
        [
            'gateway'              => 'fedapay',
            'supported_countries'  => ['BJ', 'TG', 'SN', 'CI'],
            'supported_currencies' => ['XOF'],
        ],
        [
            'gateway'              => 'flutterwave',
            'supported_countries'  => ['CI', 'SN', 'GH', 'NG', 'CM', 'UG', 'KE', 'TZ'],
            'supported_currencies' => ['XOF', 'XAF', 'GHS', 'NGN', 'KES', 'USD'],
        ],
    ];

    public function index(): Response
    {
        // Initialise les configs manquantes avec is_active=false
        foreach (self::DEFAULTS as $defaults) {
            GatewayConfig::firstOrCreate(
                ['gateway' => $defaults['gateway']],
                array_merge($defaults, ['is_active' => false, 'config' => []])
            );
        }

        $order = ['moneroo' => 0, 'cinetpay' => 1, 'fedapay' => 2, 'flutterwave' => 3];

        $gateways = GatewayConfig::all()
            ->sortBy(fn ($g) => $order[$g->gateway] ?? 99)
            ->map(fn (GatewayConfig $g) => [
                'id'                   => $g->id,
                'gateway'              => $g->gateway,
                'is_active'            => $g->is_active,
                'supported_countries'  => $g->supported_countries ?? [],
                'supported_currencies' => $g->supported_currencies ?? [],
                // On expose la config sans les secrets (le front les reçoit pour édition)
                'config'               => $g->config ?? [],
            ]);

        return Inertia::render('Admin/Gateways', ['gateways' => $gateways]);
    }

    public function update(Request $request, GatewayConfig $gateway): RedirectResponse
    {
        $request->validate([
            'is_active' => ['boolean'],
            'config'    => ['nullable', 'array'],
        ]);

        $gateway->update([
            'is_active' => $request->boolean('is_active'),
            'config'    => array_merge($gateway->config ?? [], $request->input('config', [])),
        ]);

        return redirect()->route('gateway-config.index')->with('success', 'Passerelle « '.$gateway->gateway.' » mise à jour.');
    }
}
