<?php

use App\Models\License;
use App\Models\Plan;
use App\Models\TemplateMarketplace;
use App\Services\LicenseService;

/** Crée un user avec une licence active sur le plan donné. */
function createUserWithPlan(string $planCode): \App\Models\User
{
    seedPlans();
    $user = createUserWithCompany();
    $plan = Plan::where('code', $planCode)->firstOrFail();

    License::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'license_key' => app(LicenseService::class)->generateKey(),
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addMonth(),
        'limits' => $plan->limits,
        'activation_source' => 'manual',
    ]);

    return $user->fresh();
}

/** Crée un template marketplace pour la company d'un user. */
function createMarketplaceTemplate(\App\Models\User $user, array $overrides = []): TemplateMarketplace
{
    return TemplateMarketplace::create(array_merge([
        'company_id' => $user->current_company_id,
        'user_id' => $user->id,
        'base_template' => 'corporate-01',
        'name' => 'Mon Template Test',
        'primary_color' => '#112233',
        'secondary_color' => '#445566',
        'accent_color' => '#778899',
        'is_public' => false,
        'is_approved' => false,
    ], $overrides));
}

it('creates a marketplace template', function () {
    $user = createUserWithPlan('business');

    $response = $this->actingAs($user)->post(route('templates.marketplace.store'), [
        'base_template' => 'corporate-01',
        'name' => 'Mon Super Template',
        'primary_color' => '#002D5B',
        'secondary_color' => '#0062CC',
        'accent_color' => '#F0C040',
        'is_public' => true,
    ]);

    $response->assertRedirect();

    $template = TemplateMarketplace::where('company_id', $user->current_company_id)->first();
    expect($template)->not->toBeNull();
    expect($template->name)->toBe('Mon Super Template');
    expect($template->is_approved)->toBeFalse(); // en attente de modération
});

it('requires business plan to share template', function () {
    $user = createUserWithCompanyAndTrial(); // plan pro (essai)

    $response = $this->actingAs($user)->post(route('templates.marketplace.store'), [
        'base_template' => 'corporate-01',
        'name' => 'Template Pro',
        'primary_color' => '#002D5B',
        'secondary_color' => '#0062CC',
        'accent_color' => '#F0C040',
    ]);

    $response->assertStatus(403);
    expect(TemplateMarketplace::count())->toBe(0);
});

it('downloads and increments counter', function () {
    $user = createUserWithCompanyAndTrial();
    $owner = createUserWithPlan('business');

    $template = createMarketplaceTemplate($owner, [
        'is_public' => true,
        'is_approved' => true,
        'downloads_count' => 5,
    ]);

    $this->actingAs($user)->post(route('templates.marketplace.download', $template));

    expect($template->fresh()->downloads_count)->toBe(6);
});

it('rates a template', function () {
    $user = createUserWithCompanyAndTrial();
    $owner = createUserWithPlan('business');

    $template = createMarketplaceTemplate($owner, [
        'is_public' => true,
        'is_approved' => true,
        'rating_sum' => 8,
        'rating_count' => 2,
    ]);

    $this->actingAs($user)->post(route('templates.marketplace.rate', $template), [
        'rating' => 5,
    ]);

    $template->refresh();
    expect($template->rating_sum)->toBe(13);
    expect($template->rating_count)->toBe(3);
    expect($template->averageRating())->toBe(round(13 / 3, 1));
});

it('only shows approved public templates in community', function () {
    $owner = createUserWithPlan('business');

    $publicApproved = createMarketplaceTemplate($owner, ['is_public' => true, 'is_approved' => true, 'name' => 'Approuvé']);
    $publicPending = createMarketplaceTemplate($owner, ['is_public' => true, 'is_approved' => false, 'name' => 'En attente']);
    $private = createMarketplaceTemplate($owner, ['is_public' => false, 'is_approved' => false, 'name' => 'Privé']);

    $user = createUserWithCompanyAndTrial();
    $response = $this->actingAs($user)->get(route('templates.marketplace.index'));
    $response->assertOk();

    $community = collect($response->original->getData()['page']['props']['community']);
    expect($community->pluck('name')->toArray())->toContain('Approuvé');
    expect($community->pluck('name')->toArray())->not->toContain('En attente');
    expect($community->pluck('name')->toArray())->not->toContain('Privé');
});

it('superadmin can approve template', function () {
    $owner = createUserWithPlan('business');
    $template = createMarketplaceTemplate($owner, ['is_public' => true, 'is_approved' => false]);

    $superadmin = createUserWithCompanyAndTrial();
    $superadmin->forceFill(['is_superadmin' => true])->save();

    $this->actingAs($superadmin)->post(route('templates.marketplace.approve', $template));

    expect($template->fresh()->is_approved)->toBeTrue();
});

it('owner can update their template', function () {
    $user = createUserWithPlan('business');
    $template = createMarketplaceTemplate($user);

    $this->actingAs($user)->put(route('templates.marketplace.update', $template), [
        'name' => 'Nom modifié',
        'primary_color' => '#AABBCC',
        'secondary_color' => '#112233',
        'accent_color' => '#445566',
    ]);

    expect($template->fresh()->name)->toBe('Nom modifié');
    expect($template->fresh()->primary_color)->toBe('#AABBCC');
});

it('isolates my-templates between companies', function () {
    $user1 = createUserWithPlan('business');
    $user2 = createUserWithPlan('business');

    $t1 = createMarketplaceTemplate($user1, ['name' => 'Template de user1']);
    $t2 = createMarketplaceTemplate($user2, ['name' => 'Template de user2']);

    $response = $this->actingAs($user1)->get(route('templates.marketplace.mine'));
    $response->assertOk();

    $mine = collect($response->original->getData()['page']['props']['mine']);
    expect($mine->pluck('name')->toArray())->toContain('Template de user1');
    expect($mine->pluck('name')->toArray())->not->toContain('Template de user2');
});
