<?php

use App\Models\Document;
use App\Notifications\DocumentFinalized;
use App\Notifications\PaymentReceived;
use App\Services\DocumentService;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
    $this->documents = app(DocumentService::class);
});

// Helper local : crée une facture simple
function makeInvoice(\App\Models\Company $company, \App\Models\User $user): Document
{
    return app(DocumentService::class)->create($company, $user, [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['description' => 'Service', 'quantity' => 1, 'unit_price' => 5000],
    ]);
}

it('stores a database notification when document is finalized', function () {
    Notification::fake();

    $invoice = makeInvoice($this->company, $this->user);

    $this->actingAs($this->user)
        ->post(route('documents.finalize', $invoice));

    Notification::assertSentTo($this->user, DocumentFinalized::class);
});

it('stores a database notification when payment is received', function () {
    Notification::fake();

    $invoice = makeInvoice($this->company, $this->user);
    $this->documents->finalize($invoice);
    $invoice->update(['status' => 'sent']);

    $this->actingAs($this->user)
        ->post(route('documents.payments', $invoice), [
            'amount' => 1000,
            'method' => 'cash',
            'paid_at' => now()->toDateString(),
        ]);

    Notification::assertSentTo($this->user, PaymentReceived::class);
});

it('returns unread count', function () {
    // Crée une notification manuellement
    $this->user->notifications()->create([
        'id' => \Illuminate\Support\Str::uuid(),
        'type' => DocumentFinalized::class,
        'data' => json_encode(['title' => 'Test', 'message' => 'Test msg', 'icon' => '📄']),
        'read_at' => null,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('notifications.unread-count'));

    $response->assertOk()->assertJson(['count' => 1]);
});

it('marks notification as read', function () {
    $notifId = (string) \Illuminate\Support\Str::uuid();
    $this->user->notifications()->create([
        'id' => $notifId,
        'type' => DocumentFinalized::class,
        'data' => json_encode(['title' => 'Test', 'icon' => '📄']),
        'read_at' => null,
    ]);

    $this->actingAs($this->user)
        ->postJson(route('notifications.read', $notifId));

    $this->assertNotNull(
        DatabaseNotification::find($notifId)?->read_at
    );
});

it('marks all notifications as read', function () {
    foreach (range(1, 3) as $i) {
        $this->user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => DocumentFinalized::class,
            'data' => json_encode(['title' => "Notif {$i}", 'icon' => '📄']),
            'read_at' => null,
        ]);
    }

    $this->actingAs($this->user)
        ->postJson(route('notifications.read-all'));

    expect($this->user->fresh()->unreadNotifications()->count())->toBe(0);
});

it('deletes a notification', function () {
    $notifId = (string) \Illuminate\Support\Str::uuid();
    $this->user->notifications()->create([
        'id' => $notifId,
        'type' => DocumentFinalized::class,
        'data' => json_encode(['title' => 'Test', 'icon' => '📄']),
        'read_at' => null,
    ]);

    $this->actingAs($this->user)
        ->deleteJson(route('notifications.destroy', $notifId));

    expect(DatabaseNotification::find($notifId))->toBeNull();
});

it('clears all read notifications', function () {
    foreach (range(1, 2) as $i) {
        $this->user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => DocumentFinalized::class,
            'data' => json_encode(['title' => "Notif {$i}", 'icon' => '📄']),
            'read_at' => now(),
        ]);
    }
    // Une non lue doit survivre
    $unreadId = (string) \Illuminate\Support\Str::uuid();
    $this->user->notifications()->create([
        'id' => $unreadId,
        'type' => DocumentFinalized::class,
        'data' => json_encode(['title' => 'Non lue', 'icon' => '📄']),
        'read_at' => null,
    ]);

    $this->actingAs($this->user)
        ->deleteJson(route('notifications.clear'));

    expect($this->user->notifications()->whereNotNull('read_at')->count())->toBe(0);
    expect(DatabaseNotification::find($unreadId))->not->toBeNull();
});

it('paginates notifications', function () {
    foreach (range(1, 25) as $i) {
        $this->user->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => DocumentFinalized::class,
            'data' => json_encode(['title' => "Notif {$i}", 'icon' => '📄']),
            'read_at' => null,
        ]);
    }

    $response = $this->actingAs($this->user)
        ->get(route('notifications.index'));

    $response->assertOk();
    // 25 créées, page 1 = 20
    $response->assertInertia(fn ($page) => $page
        ->component('Notifications/Index')
        ->has('notifications.data', 20)
    );
});

it('filters unread notifications', function () {
    $this->user->notifications()->create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => DocumentFinalized::class,
        'data' => json_encode(['title' => 'Lue', 'icon' => '📄']),
        'read_at' => now(),
    ]);
    $this->user->notifications()->create([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => DocumentFinalized::class,
        'data' => json_encode(['title' => 'Non lue', 'icon' => '📄']),
        'read_at' => null,
    ]);

    $response = $this->actingAs($this->user)
        ->get(route('notifications.index', ['filter' => 'unread']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Notifications/Index')
        ->has('notifications.data', 1)
    );
});
