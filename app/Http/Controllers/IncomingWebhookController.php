<?php

namespace App\Http\Controllers;

use App\Models\IncomingWebhook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IncomingWebhookController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()->current_company_id;

        $webhooks = IncomingWebhook::forCompany($companyId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'name' => $w->name,
                'source' => $w->source,
                'allowed_actions' => $w->allowed_actions,
                'is_active' => $w->is_active,
                'last_called_at' => $w->last_called_at?->toIsoString(),
                'calls_count' => $w->calls_count,
                // Token masqué (visible seulement lors de la création)
                'token_preview' => '••••••••••••' . substr($w->secret_token, -4),
            ]);

        return Inertia::render('IncomingWebhooks/Index', [
            'webhooks' => $webhooks,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'source' => 'required|in:zapier,make,custom',
            'allowed_actions' => 'nullable|array',
            'allowed_actions.*' => 'string|in:create_customer,create_document,register_payment',
        ]);

        $token = IncomingWebhook::generateToken();

        $webhook = IncomingWebhook::create([
            'company_id' => $request->user()->current_company_id,
            'name' => $data['name'],
            'source' => $data['source'],
            'allowed_actions' => $data['allowed_actions'] ?? ['create_customer', 'create_document', 'register_payment'],
            'secret_token' => $token,
        ]);

        return back()->with([
            'new_token' => $token,
            'new_webhook_id' => $webhook->id,
        ]);
    }

    public function destroy(Request $request, IncomingWebhook $webhook): RedirectResponse
    {
        abort_unless($webhook->company_id === $request->user()->current_company_id, 403);

        $webhook->delete();

        return back();
    }

    public function regenerate(Request $request, IncomingWebhook $webhook): RedirectResponse
    {
        abort_unless($webhook->company_id === $request->user()->current_company_id, 403);

        $token = IncomingWebhook::generateToken();
        $webhook->update(['secret_token' => $token]);

        return back()->with([
            'new_token' => $token,
            'new_webhook_id' => $webhook->id,
        ]);
    }
}
