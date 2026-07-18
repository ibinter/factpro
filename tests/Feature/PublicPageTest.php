<?php

use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

beforeEach(function () {
    seedPlans();
});

it('affiche la landing Welcome sur / sans authentification', function () {
    get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Welcome'));
});

it('affiche la page tarifs Public/Pricing sans authentification', function () {
    get('/pricing')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Public/Pricing')
            ->has('plans', 4)
        );
});

it('expose les plans en JSON via /pricing-data', function () {
    $response = get('/pricing-data')->assertOk();

    $data = $response->json('plans');

    expect($data)->toHaveCount(4);

    foreach ($data as $plan) {
        expect($plan)->toHaveKeys([
            'code', 'name', 'price_monthly', 'price_yearly', 'eur', 'usd', 'limits', 'features', 'highlight',
        ]);
        expect($plan['price_monthly'])->toBeGreaterThan(0);
        expect($plan['eur'])->toBeGreaterThan(0);
        expect($plan['usd'])->toBeGreaterThan(0);
    }
});

it('met le plan Pro en avant (highlight)', function () {
    $data = get('/pricing-data')->json('plans');

    $pro = collect($data)->firstWhere('code', 'pro');

    expect($pro['highlight'])->toBeTrue();
});

it('rend les pages publiques accessibles sans redirection vers login', function () {
    // 200 (et non 302 vers /login) prouve l'accès public sans authentification.
    get('/')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Welcome'));
    get('/pricing')->assertOk()->assertInertia(fn (Assert $page) => $page->component('Public/Pricing'));
    get('/pricing-data')->assertOk()->assertHeader('content-type', 'application/json');
});
