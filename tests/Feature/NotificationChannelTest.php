<?php

use App\Models\NotificationChannel;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
});

/* ── Helpers locaux ── */

function atConfig(array $overrides = []): array
{
    return array_merge([
        'api_key' => 'test-api-key-123',
        'username' => 'sandbox',
        'test_number' => '22500000000',
    ], $overrides);
}

function twilioConfig(array $overrides = []): array
{
    return array_merge([
        'account_sid' => 'ACtest1234567890',
        'auth_token' => 'auth_token_secret',
        'from_number' => '14155238886',
        'test_number' => '22500000000',
    ], $overrides);
}

/* ── Tests ── */

it('creates an sms channel (africas_talking)', function () {
    $response = $this->actingAs($this->user)
        ->post(route('notification-channels.store'), [
            'type' => 'sms',
            'provider' => 'africas_talking',
            'config' => atConfig(),
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $channel = NotificationChannel::where('company_id', $this->company->id)->first();
    expect($channel)->not->toBeNull()
        ->and($channel->type)->toBe('sms')
        ->and($channel->provider)->toBe('africas_talking')
        ->and($channel->is_active)->toBeTrue();
});

it('creates a whatsapp channel (twilio)', function () {
    $response = $this->actingAs($this->user)
        ->post(route('notification-channels.store'), [
            'type' => 'whatsapp',
            'provider' => 'twilio',
            'config' => twilioConfig(),
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $channel = NotificationChannel::where('company_id', $this->company->id)->first();
    expect($channel)->not->toBeNull()
        ->and($channel->type)->toBe('whatsapp')
        ->and($channel->provider)->toBe('twilio');
});

it('encrypts the api credentials at rest', function () {
    $this->actingAs($this->user)
        ->post(route('notification-channels.store'), [
            'type' => 'sms',
            'provider' => 'africas_talking',
            'config' => atConfig(['api_key' => 'super-secret-key']),
        ]);

    $channel = NotificationChannel::where('company_id', $this->company->id)->firstOrFail();

    // La valeur brute en DB ne doit PAS contenir la clé en clair
    $raw = \Illuminate\Support\Facades\DB::table('notification_channels')
        ->where('id', $channel->id)
        ->value('config');

    expect($raw)->not->toContain('super-secret-key');

    // Via le model (déchiffrement automatique) on retrouve la valeur
    $fresh = NotificationChannel::find($channel->id);
    expect($fresh->config['api_key'])->toBe('super-secret-key');
});

it('isolates channels between companies', function () {
    $other = createUserWithCompanyAndTrial();

    NotificationChannel::create([
        'company_id' => $other->currentCompany->id,
        'type' => 'sms',
        'provider' => 'africas_talking',
        'config' => atConfig(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('notification-channels.index'));

    $response->assertOk();
    $channels = $response->original->getData()['page']['props']['channels'] ?? [];
    expect(collect($channels)->pluck('id')->all())->not->toContain(
        NotificationChannel::where('company_id', $other->currentCompany->id)->value('id')
    );
});

it('sends a test sms via fake http', function () {
    Http::fake([
        'api.africastalking.com/*' => Http::response(['SMSMessageData' => ['Recipients' => []]], 201),
    ]);

    $channel = NotificationChannel::create([
        'company_id' => $this->company->id,
        'type' => 'sms',
        'provider' => 'africas_talking',
        'config' => atConfig(['test_number' => '22500000001']),
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('notification-channels.test', $channel));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    Http::assertSent(fn ($req) => str_contains($req->url(), 'africastalking.com'));
});

it('deletes a channel', function () {
    $channel = NotificationChannel::create([
        'company_id' => $this->company->id,
        'type' => 'sms',
        'provider' => 'africas_talking',
        'config' => atConfig(),
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->delete(route('notification-channels.destroy', $channel));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(NotificationChannel::find($channel->id))->toBeNull();
});

it('validates provider-specific config fields', function () {
    // Africa's Talking : api_key requis
    $this->actingAs($this->user)
        ->post(route('notification-channels.store'), [
            'type' => 'sms',
            'provider' => 'africas_talking',
            'config' => ['username' => 'sandbox'], // api_key manquant
        ])
        ->assertSessionHasErrors('config.api_key');

    // Twilio : account_sid requis
    $this->actingAs($this->user)
        ->post(route('notification-channels.store'), [
            'type' => 'whatsapp',
            'provider' => 'twilio',
            'config' => ['auth_token' => 'tok', 'from_number' => '123'], // account_sid manquant
        ])
        ->assertSessionHasErrors('config.account_sid');
});

it('forbids accessing another company channel', function () {
    $other = createUserWithCompanyAndTrial();

    $channel = NotificationChannel::create([
        'company_id' => $other->currentCompany->id,
        'type' => 'sms',
        'provider' => 'africas_talking',
        'config' => atConfig(),
        'is_active' => true,
    ]);

    $this->actingAs($this->user)
        ->delete(route('notification-channels.destroy', $channel))
        ->assertForbidden();
});
