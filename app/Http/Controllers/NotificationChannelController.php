<?php

namespace App\Http\Controllers;

use App\Models\NotificationChannel;
use App\Services\SmsService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationChannelController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $channels = NotificationChannel::where('company_id', $company->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (NotificationChannel $ch) => [
                'id' => $ch->id,
                'type' => $ch->type,
                'provider' => $ch->provider,
                'is_active' => $ch->is_active,
                'test_number' => $ch->config['test_number'] ?? null,
                'created_at' => $ch->created_at?->toDateString(),
            ]);

        return Inertia::render('NotificationChannels/Index', [
            'channels' => $channels,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => 'required|in:sms,whatsapp',
            'provider' => 'required|string|max:50',
            'config' => 'required|array',
            'config.test_number' => 'nullable|string|max:20',
        ]);

        $this->validateProviderConfig($request, $data['provider']);

        $company = $request->user()->currentCompany;

        NotificationChannel::create([
            'company_id' => $company->id,
            'type' => $data['type'],
            'provider' => $data['provider'],
            'config' => $request->input('config', []),
            'is_active' => true,
        ]);

        return redirect()->route('notification-channels.index')->with('success', 'Canal de notification créé.');
    }

    public function update(Request $request, NotificationChannel $channel): RedirectResponse
    {
        abort_unless($channel->company_id === $request->user()->current_company_id, 403);

        $data = $request->validate([
            'config' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($data['config'])) {
            $this->validateProviderConfig($request, $channel->provider);
        }

        $channel->update($data);

        return redirect()->route('notification-channels.index')->with('success', 'Canal mis à jour.');
    }

    public function destroy(Request $request, NotificationChannel $channel): RedirectResponse
    {
        abort_unless($channel->company_id === $request->user()->current_company_id, 403);

        $channel->delete();

        return redirect()->route('notification-channels.index')->with('success', 'Canal supprimé.');
    }

    public function test(Request $request, NotificationChannel $channel): RedirectResponse
    {
        abort_unless($channel->company_id === $request->user()->current_company_id, 403);

        $testNumber = $channel->config['test_number'] ?? null;

        if (empty($testNumber)) {
            return back()->with('error', 'Aucun numéro de test configuré.');
        }

        $message = 'Test FactPro : votre canal ' . strtoupper($channel->type) . ' est opérationnel.';

        $ok = match ($channel->type) {
            'sms' => app(SmsService::class)->send($testNumber, $message, $channel),
            'whatsapp' => app(WhatsAppService::class)->send($testNumber, $message, $channel),
            default => false,
        };

        return back()->with($ok ? 'success' : 'error', $ok
            ? "Message de test envoyé au {$testNumber}."
            : 'Échec du test. Vérifiez vos identifiants API.');
    }

    private function validateProviderConfig(Request $request, string $provider): void
    {
        match ($provider) {
            'africas_talking' => $request->validate([
                'config.api_key' => 'required|string',
                'config.username' => 'required|string',
            ]),
            'twilio' => $request->validate([
                'config.account_sid' => 'required|string',
                'config.auth_token' => 'required|string',
                'config.from_number' => 'required|string',
            ]),
            default => null,
        };
    }
}
