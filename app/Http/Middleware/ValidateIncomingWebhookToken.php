<?php

namespace App\Http\Middleware;

use App\Models\IncomingWebhook;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateIncomingWebhookToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (! $token) {
            return response()->json(['message' => 'Token manquant.'], 401);
        }

        $webhook = IncomingWebhook::where('secret_token', $token)
            ->where('is_active', true)
            ->first();

        if (! $webhook) {
            return response()->json(['message' => 'Token invalide ou webhook inactif.'], 401);
        }

        // Vérifier que l'action est autorisée
        $action = $this->resolveAction($request);
        if ($action && $webhook->allowed_actions && ! in_array($action, $webhook->allowed_actions)) {
            return response()->json(['message' => "Action '{$action}' non autorisée pour ce webhook."], 403);
        }

        // Mettre à jour les stats d'utilisation
        $webhook->increment('calls_count');
        $webhook->update(['last_called_at' => now()]);

        $request->incomingWebhook = $webhook;

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }

    private function resolveAction(Request $request): ?string
    {
        $path = $request->path();
        $method = $request->method();

        // Customers
        if (str_contains($path, '/customers') && $method === 'POST') {
            return 'create_customer';
        }

        // Documents
        if (str_contains($path, '/documents') && $method === 'POST') {
            return 'create_document';
        }

        // Payments
        if (str_contains($path, '/payments') && $method === 'POST') {
            return 'register_payment';
        }

        // Triggers (lecture — pas de restriction par action)
        if (str_contains($path, '/triggers')) {
            return null;
        }

        return null;
    }
}
