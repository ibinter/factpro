<?php

use App\Models\Order;
use App\Models\PaymentProof;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
});

// ─────────────────────────────────────────────────────────
// Pages de base
// ─────────────────────────────────────────────────────────

it('billing index page loads for authenticated user', function () {
    $this->actingAs($this->user)
        ->get(route('billing.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Billing/Index'));
});

it('plans page shows available plans', function () {
    $this->actingAs($this->user)
        ->get(route('billing.plans'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Billing/Plans')
            ->has('plans')
        );
});

it('checkout page loads with valid plan', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    $this->actingAs($this->user)
        ->get(route('billing.checkout', $order))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Billing/Checkout'));
});

// ─────────────────────────────────────────────────────────
// Soumission de preuves
// ─────────────────────────────────────────────────────────

it('can initiate mobile money payment with valid proof', function () {
    Storage::fake('local');

    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    $response = $this->actingAs($this->user)->post(route('billing.proof', $order), [
        'provider'           => 'wave',
        'sender_name'        => 'Awa Traoré',
        'sender_number'      => '+2250700000001',
        'provider_reference' => 'WV-REF-ABC123',
        'amount_declared'    => 10000,
        'proof'              => UploadedFile::fake()->image('recu_wave.jpg'),
    ]);

    $response->assertRedirect(route('billing.proof-status', $order->id));
    expect($order->fresh()->status)->toBe('proof_submitted');

    $tx = PaymentTransaction::where('order_id', $order->id)->firstOrFail();
    expect($tx->payment_provider)->toBe('wave');
});

it('can initiate bank transfer with valid proof', function () {
    Storage::fake('local');

    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    $response = $this->actingAs($this->user)->post(route('billing.proof', $order), [
        'provider'           => 'bank_transfer_national',
        'sender_name'        => 'Jean-Paul Kouassi',
        'provider_reference' => 'VIR-202507-001',
        'amount_declared'    => 10000,
        'proof'              => UploadedFile::fake()->create('virement.pdf', 200, 'application/pdf'),
    ]);

    $response->assertRedirect(route('billing.proof-status', $order->id));
    expect($order->fresh()->status)->toBe('proof_submitted');
});

it('can initiate international transfer with valid proof', function () {
    Storage::fake('local');

    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    $response = $this->actingAs($this->user)->post(route('billing.proof', $order), [
        'provider'           => 'international_transfer',
        'sender_name'        => 'Marie Dupont',
        'sender_country'     => 'France',
        'sender_city'        => 'Paris',
        'transfer_service'   => 'Western Union',
        'provider_reference' => 'WU-12345678',
        'amount_declared'    => 16,
        'proof'              => UploadedFile::fake()->image('wu_recu.jpg'),
    ]);

    $response->assertRedirect(route('billing.proof-status', $order->id));
    expect($order->fresh()->status)->toBe('proof_submitted');

    $tx = PaymentTransaction::where('order_id', $order->id)->firstOrFail();
    expect($tx->payment_provider)->toBe('international_transfer');
});

it('can initiate cash payment without proof file', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    $response = $this->actingAs($this->user)->post(route('billing.proof', $order), [
        'provider'        => 'cash',
        'sender_name'     => 'Kofi Mensah',
        'amount_declared' => 10000,
        'comment'         => 'Je passerai lundi à 10h',
    ]);

    $response->assertRedirect(route('billing.proof-status', $order->id));
    expect($order->fresh()->status)->toBe('proof_submitted');
});

// ─────────────────────────────────────────────────────────
// Page de suivi
// ─────────────────────────────────────────────────────────

it('proof status page shows pending status', function () {
    Storage::fake('local');

    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    $this->actingAs($this->user)->post(route('billing.proof', $order), [
        'provider'           => 'orange_money',
        'sender_name'        => 'Test User',
        'sender_number'      => '+2250700000002',
        'provider_reference' => 'OM-TESTREF',
        'amount_declared'    => 10000,
        'proof'              => UploadedFile::fake()->image('proof.jpg'),
    ]);

    $this->actingAs($this->user)
        ->get(route('billing.proof-status', $order->id))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Billing/ProofStatus')
            ->where('order.status', 'proof_submitted')
        );
});

// ─────────────────────────────────────────────────────────
// Téléchargement reçu
// ─────────────────────────────────────────────────────────

it('receipt download returns content for paid order', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1, attributes: [
        'status'  => 'paid',
        'paid_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->get(route('billing.receipt.download', $order->id))
        ->assertOk();
});

it('receipt download is denied for unpaid order', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    $this->actingAs($this->user)
        ->get(route('billing.receipt.download', $order->id))
        ->assertRedirect(route('billing.index'));
});

// ─────────────────────────────────────────────────────────
// Validation upload preuve
// ─────────────────────────────────────────────────────────

it('proof upload rejects executable files', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    $this->actingAs($this->user)->post(route('billing.proof', $order), [
        'provider'           => 'wave',
        'sender_name'        => 'Test',
        'provider_reference' => 'REF-001',
        'amount_declared'    => 10000,
        'proof'              => UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream'),
    ])->assertSessionHasErrors('proof');
});

it('proof upload rejects oversized files', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    // 11 MB > 10 MB limit
    $this->actingAs($this->user)->post(route('billing.proof', $order), [
        'provider'           => 'wave',
        'sender_name'        => 'Test',
        'provider_reference' => 'REF-002',
        'amount_declared'    => 10000,
        'proof'              => UploadedFile::fake()->create('big.jpg', 11 * 1024, 'image/jpeg'),
    ])->assertSessionHasErrors('proof');
});

it('proof upload rejects invalid mime types', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($this->user, $plan, months: 1);

    $this->actingAs($this->user)->post(route('billing.proof', $order), [
        'provider'           => 'wave',
        'sender_name'        => 'Test',
        'provider_reference' => 'REF-003',
        'amount_declared'    => 10000,
        'proof'              => UploadedFile::fake()->create('doc.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
    ])->assertSessionHasErrors('proof');
});
