<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\PlanSeeder;

beforeEach(function () {
    $this->seed(PlanSeeder::class);
});

$payload = [
    'name' => 'Awa Traoré',
    'company_name' => 'Awa Digital SARL',
    'phone' => '+225 07 00 00 00 00',
    'country' => 'ci',
    'email' => 'awa@example.com',
    'password' => 'password',
    'password_confirmation' => 'password',
];

it('creates the user, his company (owner pivot) and a 7-day pro trial license', function () use ($payload) {
    $response = $this->post('/register', $payload);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();

    $user = User::where('email', 'awa@example.com')->firstOrFail();

    // Société créée, rattachée en owner, définie comme société courante
    $company = Company::where('owner_id', $user->id)->firstOrFail();
    expect($company->name)->toBe('Awa Digital SARL')
        ->and($company->currency)->toBe('XOF')
        ->and($user->current_company_id)->toBe($company->id);

    $pivotRole = $company->users()->where('users.id', $user->id)->first()->pivot->role;
    expect($pivotRole)->toBe('owner');

    // Licence d'essai : status trial, plan pro, fin dans 7 jours
    $license = $user->licenses()->first();
    expect($license)->not->toBeNull()
        ->and($license->type)->toBe('trial')
        ->and($license->status)->toBe('trial')
        ->and($license->plan->code)->toBe('pro')
        ->and($license->ends_at->isSameDay(now()->addDays(7)))->toBeTrue()
        ->and($license->trial_ends_at->isSameDay(now()->addDays(7)))->toBeTrue()
        ->and($license->isUsable())->toBeTrue();
});

it('grants access to the dashboard right after registration', function () use ($payload) {
    $this->post('/register', $payload);

    $this->get('/dashboard')->assertOk();
});
