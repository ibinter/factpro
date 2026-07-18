<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    /**
     * GET /push/vapid-public-key — retourne la clé publique VAPID (sans auth).
     */
    public function publicKey(): JsonResponse
    {
        return response()->json([
            'public_key' => config('services.vapid.public_key'),
        ]);
    }

    /**
     * POST /push/subscribe — enregistre ou met à jour une subscription.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint'                      => 'required|string|max:2048',
            'keys.p256dh'                   => 'required|string',
            'keys.auth'                     => 'required|string',
        ]);

        $user = Auth::user();

        PushSubscription::updateOrCreate(
            [
                'user_id'  => $user->id,
                'endpoint' => $data['endpoint'],
            ],
            [
                'company_id'    => $user->currentCompany?->id ?? null,
                'public_key'    => $data['keys']['p256dh'],
                'auth_token'    => $data['keys']['auth'],
                'user_agent'    => $request->userAgent(),
                'subscribed_at' => now(),
                'is_active'     => true,
            ]
        );

        return response()->json(['message' => 'Subscription enregistrée.'], 201);
    }

    /**
     * DELETE /push/unsubscribe — désactive la subscription de l'utilisateur courant.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('user_id', Auth::id())
            ->where('endpoint', $data['endpoint'])
            ->update(['is_active' => false]);

        return response()->json(['message' => 'Subscription désactivée.']);
    }
}
