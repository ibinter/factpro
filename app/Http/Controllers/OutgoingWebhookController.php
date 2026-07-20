<?php

namespace App\Http\Controllers;

use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Services\LicenseService;
use App\Services\OutgoingWebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OutgoingWebhookController extends Controller
{
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    private const EVENTS_LIST = [
        'document.created',
        'invoice.finalized',
        'invoice.payment_received',
        'document.sent',
        'webhook.test',
    ];

    public function __construct(private LicenseService $licenses)
    {
    }

    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    private function ensureOwnership(Request $request, WebhookEndpoint $endpoint): void
    {
        abort_if(
            (int) $endpoint->company_id !== (int) $request->user()->current_company_id,
            403
        );
    }

    public function index(Request $request): Response
    {
        $hasAccess = $this->hasAccess($request);
        $company = $request->user()->currentCompany;

        if (! $hasAccess) {
            return Inertia::render('Webhooks/Index', [
                'hasAccess' => false,
                'endpoints' => [],
                'events_list' => self::EVENTS_LIST,
                'deliveries' => [],
            ]);
        }

        $endpoints = WebhookEndpoint::where('company_id', $company->id)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->get();

        $endpointIds = $endpoints->pluck('id');

        $deliveries = WebhookDelivery::whereIn('webhook_endpoint_id', $endpointIds)
            ->orderByDesc('id')
            ->limit(10)
            ->get(['id', 'webhook_endpoint_id', 'event', 'response_status', 'delivered_at', 'failed_at', 'attempt', 'created_at']);

        return Inertia::render('Webhooks/Index', [
            'hasAccess' => true,
            'endpoints' => $endpoints,
            'events_list' => self::EVENTS_LIST,
            'deliveries' => $deliveries,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->hasAccess($request), 403);

        $data = $request->validate([
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:'.implode(',', self::EVENTS_LIST),
        ]);

        $company = $request->user()->currentCompany;

        WebhookEndpoint::create([
            'company_id' => $company->id,
            'url' => $data['url'],
            'secret' => Str::random(40),
            'events' => $data['events'],
            'is_active' => true,
        ]);

        return redirect()->route('webhooks.index')->with('success', 'Endpoint webhook créé.');
    }

    public function update(Request $request, WebhookEndpoint $endpoint): RedirectResponse
    {
        abort_unless($this->hasAccess($request), 403);
        $this->ensureOwnership($request, $endpoint);

        $data = $request->validate([
            'url' => 'sometimes|url|max:500',
            'events' => 'sometimes|array|min:1',
            'events.*' => 'string|in:'.implode(',', self::EVENTS_LIST),
            'is_active' => 'sometimes|boolean',
        ]);

        $endpoint->update($data);

        return redirect()->route('webhooks.index')->with('success', 'Endpoint webhook mis à jour.');
    }

    public function destroy(Request $request, WebhookEndpoint $endpoint): RedirectResponse
    {
        abort_unless($this->hasAccess($request), 403);
        $this->ensureOwnership($request, $endpoint);

        $endpoint->delete();

        return redirect()->route('webhooks.index')->with('success', 'Endpoint webhook supprimé.');
    }

    public function test(Request $request, WebhookEndpoint $endpoint): RedirectResponse
    {
        abort_unless($this->hasAccess($request), 403);
        $this->ensureOwnership($request, $endpoint);

        $company = $request->user()->currentCompany;

        app(OutgoingWebhookService::class)->dispatch($company, 'webhook.test', [
            'event' => 'webhook.test',
            'company_id' => $company->id,
            'timestamp' => now()->toIso8601String(),
        ]);

        return redirect()->route('webhooks.index')->with('success', 'Ping test envoyé.');
    }
}
