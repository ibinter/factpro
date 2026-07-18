<?php

use App\Models\License;
use App\Models\Plan;
use App\Models\User;
use App\Services\LicenseService;

beforeEach(function () {
    seedPlans();
    $this->service = app(LicenseService::class);
});

it('starts a trial only once (idempotent)', function () {
    $user = User::factory()->create();

    $first = $this->service->startTrial($user);
    $second = $this->service->startTrial($user);

    expect($second->id)->toBe($first->id)
        ->and($user->licenses()->count())->toBe(1)
        ->and($first->status)->toBe('trial')
        ->and($first->plan->code)->toBe('pro');
});

it('activates an N-month paid license from a validated order', function () {
    $user = User::factory()->create();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 3);
    $transaction = createUnderReviewTransaction($order);

    $license = $this->service->activateFromOrder($order, $transaction);

    expect($license->type)->toBe('paid')
        ->and($license->status)->toBe('active')
        ->and($license->plan_id)->toBe($plan->id)
        ->and($license->ends_at->toDateString())->toBe(now()->addMonths(3)->toDateString())
        ->and($license->isUsable())->toBeTrue();

    expect($order->fresh()->status)->toBe('paid')
        ->and($order->fresh()->paid_at)->not->toBeNull();
});

it('never activates twice for the same transaction (idempotent)', function () {
    $user = User::factory()->create();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 1);
    $transaction = createUnderReviewTransaction($order);

    $first = $this->service->activateFromOrder($order, $transaction);
    $second = $this->service->activateFromOrder($order, $transaction);

    expect($second->id)->toBe($first->id)
        ->and(License::where('transaction_id', $transaction->id)->count())->toBe(1)
        ->and(License::where('user_id', $user->id)->count())->toBe(1);
});

it('extends ends_at on renewal of the same active plan instead of creating a second license', function () {
    $user = User::factory()->create();
    $plan = Plan::where('code', 'pro')->firstOrFail();

    // Première activation : 1 mois
    $order1 = createPendingOrder($user, $plan, months: 1);
    $license = $this->service->activateFromOrder($order1, createUnderReviewTransaction($order1));
    $initialEndsAt = $license->ends_at->copy();

    // Renouvellement même plan : 2 mois
    $order2 = createPendingOrder($user, $plan, months: 2);
    $renewed = $this->service->activateFromOrder($order2, createUnderReviewTransaction($order2));

    expect($renewed->id)->toBe($license->id)
        ->and(License::where('user_id', $user->id)->count())->toBe(1)
        ->and($renewed->ends_at->toDateTimeString())
        ->toBe($initialEndsAt->copy()->addMonths(2)->toDateTimeString());
});

it('terminates the trial when the first paid license is activated', function () {
    $user = User::factory()->create();
    $trial = $this->service->startTrial($user);

    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan, months: 1);
    $paid = $this->service->activateFromOrder($order, createUnderReviewTransaction($order));

    expect($trial->fresh()->status)->toBe('terminated')
        ->and($paid->id)->not->toBe($trial->id)
        ->and($paid->status)->toBe('active');
});

it('detects reached, unreached and unlimited limits', function () {
    $user = User::factory()->create();
    $plan = Plan::where('code', 'starter')->firstOrFail();

    License::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'license_key' => 'FP-TEST-AAAA-BBBB-CCCC',
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'limits' => ['customers' => 2, 'documents_per_month' => 'unlimited'],
        'activation_source' => 'manual',
    ]);

    // Limite atteinte (2/2) puis dépassée (3/2)
    expect($this->service->limitReached($user, 'customers', 2))->toBeTrue()
        ->and($this->service->limitReached($user, 'customers', 3))->toBeTrue()
        // Sous la limite
        ->and($this->service->limitReached($user, 'customers', 1))->toBeFalse()
        // 'unlimited' → jamais atteinte
        ->and($this->service->limitReached($user, 'documents_per_month', 999999))->toBeFalse();
});

it('treats a user without any license as over the limit', function () {
    $user = User::factory()->create();

    expect($this->service->limitReached($user, 'customers', 0))->toBeTrue();
});
