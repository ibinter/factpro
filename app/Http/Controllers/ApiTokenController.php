<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestion des clés API personnelles (cahier §20) — page /api-tokens.
 * La création de clés est réservée aux forfaits BUSINESS et ENTERPRISE.
 */
class ApiTokenController extends Controller
{
    public function __construct(private LicenseService $licenses)
    {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('ApiTokens/Index', [
            'hasAccess' => $this->hasApiAccess($request),
            'planCode' => $this->licenses->currentFor($user)?->plan?->code,
            'tokens' => $user->tokens()
                ->orderByDesc('created_at')
                ->get()
                ->map(fn ($token) => [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->diffForHumans(),
                    'created_at' => $token->created_at->format('d/m/Y H:i'),
                ]),
            'plainToken' => $request->session()->get('plain_token'),
            'apiBaseUrl' => url('/api/v1'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! $this->hasApiAccess($request)) {
            return back()->with('error', "L'API REST est disponible à partir du forfait BUSINESS.");
        }

        $data = $request->validate([
            'name' => 'required|string|max:64',
            'abilities' => 'nullable|array|min:1',
            'abilities.*' => 'in:read,write',
        ]);

        $abilities = array_values(array_unique($data['abilities'] ?? ['read']));

        $token = $request->user()->createToken($data['name'], $abilities);

        return redirect()->route('api-tokens.index')
            ->with('plain_token', $token->plainTextToken)
            ->with('success', 'Clé API « '.$data['name'].' » créée. Copiez-la maintenant : elle ne sera plus jamais affichée.');
    }

    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        $request->user()->tokens()->where('id', $tokenId)->delete();

        return redirect()->route('profile.index')->with('success', 'Clé API révoquée. Elle est immédiatement inutilisable.');
    }

    private function hasApiAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && $license->isUsable()
            && in_array($license->plan?->code, ['business', 'enterprise'], true);
    }
}
