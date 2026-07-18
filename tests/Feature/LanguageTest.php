<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('switches language to english', function () {
    $user = User::factory()->create(['language' => 'fr']);

    $response = $this->actingAs($user)
        ->post(route('language.switch'), ['locale' => 'en']);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'language' => 'en']);
});

it('switches language to arabic', function () {
    $user = User::factory()->create(['language' => 'fr']);

    $response = $this->actingAs($user)
        ->post(route('language.switch'), ['locale' => 'ar']);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', ['id' => $user->id, 'language' => 'ar']);
});

it('persists language to user profile', function () {
    $user = User::factory()->create(['language' => 'fr']);

    $this->actingAs($user)
        ->post(route('language.switch'), ['locale' => 'en']);

    expect($user->fresh()->language)->toBe('en');
});

it('shares translations with inertia', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertInertia(fn ($page) => $page->has('translations')
        ->has('translations.ui')
        ->has('translations.documents')
    );
});

it('shares locale with inertia', function () {
    $user = User::factory()->create(['language' => 'en']);
    // Set session locale
    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('profile.edit'))
        ->assertInertia(fn ($page) => $page->where('locale', 'en'));
});

it('defaults to french for guests', function () {
    // Without session or auth user, locale should default to fr
    // Test via SetLocale middleware behavior
    $response = $this->get('/');
    // Application locale defaults to fr
    $this->assertEquals('fr', app()->getLocale());
});

it('returns arabic translations in ar locale', function () {
    $user = User::factory()->create(['language' => 'ar']);

    $response = $this->actingAs($user)
        ->withSession(['locale' => 'ar'])
        ->get(route('profile.edit'));

    $response->assertInertia(fn ($page) => $page
        ->where('locale', 'ar')
        ->where('translations.ui.save', 'حفظ')
        ->where('translations.documents.invoice', 'فاتورة')
    );
});

it('english translations are complete', function () {
    $user = User::factory()->create(['language' => 'en']);

    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('profile.edit'))
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'en')
            ->where('translations.ui.save', 'Save')
            ->where('translations.ui.dashboard', 'Dashboard')
            ->where('translations.documents.invoice', 'Invoice')
            ->where('translations.documents.thank_you', 'Thank you for your business')
        );
});

it('rejects invalid locale', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('language.switch'), ['locale' => 'de']);

    $response->assertSessionHasErrors('locale');
});
