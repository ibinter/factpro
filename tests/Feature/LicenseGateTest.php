<?php

it('redirects a user with an expired trial from the dashboard to the plans page', function () {
    $user = createUserWithCompanyAndTrial();

    $user->licenses()->first()->update([
        'status' => 'expired',
        'ends_at' => now()->subDay(),
        'trial_ends_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('billing.plans'));

    // Autres routes métier également bloquées
    $this->actingAs($user)
        ->get('/documents')
        ->assertRedirect(route('billing.plans'));
});

it('blocks a trial license whose end date is in the past even if the status was not updated', function () {
    $user = createUserWithCompanyAndTrial();

    $user->licenses()->first()->update([
        'ends_at' => now()->subDay(),
        'trial_ends_at' => now()->subDay(),
    ]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertRedirect(route('billing.plans'));
});

it('keeps billing pages accessible with an expired license (so the user can pay)', function () {
    $user = createUserWithCompanyAndTrial();

    $user->licenses()->first()->update([
        'status' => 'expired',
        'ends_at' => now()->subDay(),
        'trial_ends_at' => now()->subDay(),
    ]);

    $this->actingAs($user)->get(route('billing.plans'))->assertOk();
    $this->actingAs($user)->get(route('billing.index'))->assertOk();
});

it('lets a user with a usable trial reach the dashboard', function () {
    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)->get('/dashboard')->assertOk();
});
