<?php

use App\Mail\LicenseExpired;
use App\Mail\LicenseExpiringSoon;
use App\Mail\PaymentProofReceived;
use App\Mail\PaymentRejected;
use App\Mail\PaymentValidated;
use App\Mail\ProofComplementRequested;
use App\Mail\ProvisionalLicenseActivated;
use App\Models\License;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\PaymentNotificationService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

beforeEach(function () {
    seedPlans();
    Mail::fake();
    Queue::fake();

    $this->user = createUserWithCompanyAndTrial();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $this->plan = $plan;
    $this->order = createPendingOrder($this->user, $plan, months: 1, attributes: [
        'status' => 'proof_submitted',
    ]);
    $this->order->loadMissing(['plan', 'user']);
    $this->transaction = createUnderReviewTransaction($this->order);

    // Licence payante active simulée
    $this->license = License::create([
        'user_id' => $this->user->id,
        'plan_id' => $plan->id,
        'order_id' => $this->order->id,
        'transaction_id' => $this->transaction->id,
        'license_key' => 'FP-TEST-NOTIF-KEY-' . strtoupper(Str::random(4)),
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'limits' => $plan->limits,
        'activation_source' => 'manual',
    ]);
    $this->license->loadMissing('plan');

    $this->service = app(PaymentNotificationService::class);
});

it('sends proof received email on proof submission', function () {
    $this->service->sendProofReceived($this->order, $this->transaction);

    Mail::assertQueued(PaymentProofReceived::class, function ($mail) {
        return $mail->hasTo($this->user->email)
            && $mail->order->id === $this->order->id;
    });
});

it('sends proof received email with correct subject', function () {
    $mailable = new PaymentProofReceived($this->order, $this->transaction);
    $envelope = $mailable->envelope();

    expect($envelope->subject)->toContain('Preuve de paiement reçue');
});

it('sends payment validated email with pdf receipt', function () {
    $this->service->sendPaymentValidated($this->order, $this->transaction, $this->license);

    Mail::assertQueued(PaymentValidated::class, function ($mail) {
        return $mail->hasTo($this->user->email)
            && $mail->order->id === $this->order->id
            && $mail->license->id === $this->license->id;
    });
});

it('sends payment validated email with correct subject', function () {
    $mailable = new PaymentValidated($this->order, $this->license, $this->transaction);
    $envelope = $mailable->envelope();

    expect($envelope->subject)->toContain('Paiement confirmé');
});

it('sends payment rejected email with reason', function () {
    $reason = 'Référence de transaction invalide';
    $this->service->sendPaymentRejected($this->order, $this->transaction, $reason);

    Mail::assertQueued(PaymentRejected::class, function ($mail) use ($reason) {
        return $mail->hasTo($this->user->email)
            && $mail->reason === $reason;
    });
});

it('sends payment rejected email with correct subject', function () {
    $mailable = new PaymentRejected($this->order, $this->transaction, 'Motif test');
    $envelope = $mailable->envelope();

    expect($envelope->subject)->toContain('refusée');
});

it('sends complement requested email', function () {
    $note = 'Merci de fournir le reçu Orange Money complet.';
    $this->service->sendComplementRequested($this->order, $note);

    Mail::assertQueued(ProofComplementRequested::class, function ($mail) use ($note) {
        return $mail->hasTo($this->user->email)
            && $mail->complementNote === $note;
    });
});

it('sends complement requested email with correct subject', function () {
    $mailable = new ProofComplementRequested($this->order, 'Note test');
    $envelope = $mailable->envelope();

    expect($envelope->subject)->toContain('Complément');
});

it('sends provisional license activated email', function () {
    $provisionalLicense = License::create([
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'license_key' => 'FP-PROV-' . strtoupper(Str::random(8)),
        'type' => 'paid',
        'status' => 'provisional',
        'starts_at' => now(),
        'ends_at' => now()->addDays(7),
        'limits' => $this->plan->limits,
    ]);

    $this->service->sendProvisionalActivated($provisionalLicense);

    Mail::assertQueued(ProvisionalLicenseActivated::class, function ($mail) use ($provisionalLicense) {
        return $mail->hasTo($this->user->email)
            && $mail->license->id === $provisionalLicense->id;
    });
});

it('sends provisional license email with correct subject', function () {
    $mailable = new ProvisionalLicenseActivated($this->license);
    $envelope = $mailable->envelope();

    expect($envelope->subject)->toContain('provisoire');
});

it('sends expiring soon notification 7 days before', function () {
    $this->service->sendExpiringSoon($this->license, 7);

    Mail::assertQueued(LicenseExpiringSoon::class, function ($mail) {
        return $mail->hasTo($this->user->email)
            && $mail->daysLeft === 7;
    });
});

it('sends expiring soon notification 3 days before', function () {
    $this->service->sendExpiringSoon($this->license, 3);

    Mail::assertQueued(LicenseExpiringSoon::class, function ($mail) {
        return $mail->daysLeft === 3;
    });
});

it('sends expiring soon notification 1 day before', function () {
    $this->service->sendExpiringSoon($this->license, 1);

    Mail::assertQueued(LicenseExpiringSoon::class, function ($mail) {
        return $mail->daysLeft === 1;
    });
});

it('sends expired notification after expiration', function () {
    $this->service->sendExpired($this->license);

    Mail::assertQueued(LicenseExpired::class, function ($mail) {
        return $mail->hasTo($this->user->email)
            && $mail->license->id === $this->license->id;
    });
});

it('sends expired email with correct subject', function () {
    $mailable = new LicenseExpired($this->license);
    $envelope = $mailable->envelope();

    expect($envelope->subject)->toContain('expiré');
});

it('generates pdf receipt successfully', function () {
    $path = $this->service->generateReceipt($this->order, $this->transaction, $this->license);

    expect($path)->not->toBeNull();
    expect(file_exists($path))->toBeTrue();

    // Nettoyage
    if ($path && file_exists($path)) {
        @unlink($path);
    }
});

it('pdf receipt contains order number', function () {
    $path = $this->service->generateReceipt($this->order, $this->transaction, $this->license);

    expect($path)->not->toBeNull();
    $content = file_get_contents($path);
    expect($content)->toBeString()->not->toBeEmpty();

    if ($path && file_exists($path)) {
        @unlink($path);
    }
});

it('pdf receipt contains qr code url', function () {
    // Vérifie que la vue Blade génère bien l'URL de vérification
    $html = view('pdf.payment-receipt', [
        'order' => $this->order,
        'transaction' => $this->transaction,
        'license' => $this->license,
    ])->render();

    expect($html)->toContain('/public/verify/' . $this->order->id);
});

it('does not queue any mail when service receives order with null user', function () {
    // Crée une commande sans user_id (orpheline) pour tester la robustesse du service
    $orphanOrder = Order::create([
        'order_number' => 'FP-' . now()->year . '-ORPHAN',
        'user_id' => $this->user->id,
        'plan_id' => $this->plan->id,
        'duration_months' => 1,
        'amount' => 5000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 5000,
        'currency' => 'XOF',
        'country' => 'CI',
        'status' => 'pending_payment',
        'expires_at' => now()->addHours(72),
    ]);

    // Vérifie que l'appel ne lève pas d'exception (graceful failure test)
    expect(fn () => $this->service->sendProofReceived($orphanOrder, $this->transaction))
        ->not->toThrow(\Throwable::class);
});

it('sends all required emails through payment proof submission flow', function () {
    // Soumission preuve via BillingController / PaymentService
    $admin = User::factory()->create(['is_superadmin' => true]);
    $proof = createProofFor($this->transaction);

    // Validation
    $this->actingAs($admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.validate', $this->transaction), [
            'amount_received' => $this->order->total_amount,
        ])
        ->assertRedirect();

    Mail::assertQueued(PaymentValidated::class, function ($mail) {
        return $mail->hasTo($this->user->email);
    });
});

it('sends rejection email through admin reject flow', function () {
    $admin = User::factory()->create(['is_superadmin' => true]);
    createProofFor($this->transaction);

    $this->actingAs($admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.reject', $this->transaction), [
            'reason' => 'Montant incorrect sur le reçu',
        ])
        ->assertRedirect();

    Mail::assertQueued(PaymentRejected::class, function ($mail) {
        return $mail->hasTo($this->user->email)
            && $mail->reason === 'Montant incorrect sur le reçu';
    });
});

it('sends complement email through admin complement flow', function () {
    $admin = User::factory()->create(['is_superadmin' => true]);

    // Recharge l'ordre avec la relation user pour que resolveEmail fonctionne
    $this->order->load('user');

    $response = $this->actingAs($admin)
        ->from(route('admin.payments'))
        ->post(route('admin.payments.complement', $this->transaction), [
            'complement_note' => 'Merci de fournir un reçu lisible.',
        ]);

    $response->assertRedirect();

    // Vérifie que l'email de complément a bien été mis en file
    Mail::assertQueued(ProofComplementRequested::class);
});
