<?php

use App\Models\License;
use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\PaymentMethodConfig;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helpers locaux (préfixe createAdminConsole…)
|--------------------------------------------------------------------------
*/

/** Crée une licence payante rattachée à un utilisateur + société. */
function createAdminConsoleLicense(User $user, string $status = 'active', array $attributes = []): License
{
    seedPlans();
    $plan = Plan::where('code', 'pro')->firstOrFail();

    return License::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'license_key' => 'FP-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)),
        'type' => 'paid',
        'status' => $status,
        'starts_at' => now()->subMonth(),
        'ends_at' => now()->addMonth(),
        'activation_source' => 'manual',
        ...$attributes,
    ]);
}

/** Crée un moyen de paiement manuel de base. */
function createAdminConsolePaymentMethod(array $attributes = []): PaymentMethodConfig
{
    return PaymentMethodConfig::create([
        'type' => 'mobile_money',
        'label' => 'Orange Money CI',
        'country' => 'CI',
        'currency' => 'XOF',
        'is_active' => true,
        ...$attributes,
    ]);
}

beforeEach(function () {
    seedPlans();
    $this->admin = User::factory()->create(['is_superadmin' => true]);
    $this->client = createUserWithCompany();
});

/*
|--------------------------------------------------------------------------
| Accès
|--------------------------------------------------------------------------
*/

it('interdit la console à un utilisateur non-superadmin', function () {
    foreach (['admin.dashboard', 'admin.licenses', 'admin.methods', 'admin.plans'] as $name) {
        $this->actingAs($this->client)->get(route($name))->assertForbidden();
    }
});

it('affiche le tableau de bord à un superadmin', function () {
    $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
});

it('affiche les licences, moyens de paiement et forfaits à un superadmin', function () {
    $this->actingAs($this->admin)->get(route('admin.licenses'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.methods'))->assertOk();
    $this->actingAs($this->admin)->get(route('admin.plans'))->assertOk();
});

/*
|--------------------------------------------------------------------------
| Licences
|--------------------------------------------------------------------------
*/

it('prolonge une licence de 3 mois et journalise l\'action', function () {
    $license = createAdminConsoleLicense($this->client, 'active', ['ends_at' => now()->addDays(10)]);
    $expected = $license->ends_at->copy()->addMonths(3);

    $this->actingAs($this->admin)
        ->from(route('admin.licenses'))
        ->post(route('admin.licenses.extend', $license), [
            'months' => 3,
            'reason' => 'Renouvellement réglé par virement',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($license->fresh()->ends_at->toDateString())->toBe($expected->toDateString());

    $log = PaymentAuditLog::where('action', 'license_extended')
        ->where('entity_id', $license->id)
        ->first();
    expect($log)->not->toBeNull()
        ->and($log->reason)->toBe('Renouvellement réglé par virement')
        ->and($log->admin_id)->toBe($this->admin->id);
});

it('refuse une suspension sans motif (422)', function () {
    $license = createAdminConsoleLicense($this->client, 'active');

    $this->actingAs($this->admin)
        ->from(route('admin.licenses'))
        ->post(route('admin.licenses.suspend', $license), [])
        ->assertSessionHasErrors('reason');

    expect($license->fresh()->status)->toBe('active');
});

it('suspend une licence avec motif et journalise', function () {
    $license = createAdminConsoleLicense($this->client, 'active');

    $this->actingAs($this->admin)
        ->post(route('admin.licenses.suspend', $license), ['reason' => 'Impayé'])
        ->assertSessionHas('success');

    expect($license->fresh()->status)->toBe('suspended');
    expect(PaymentAuditLog::where('action', 'license_suspended')->where('entity_id', $license->id)->exists())->toBeTrue();
});

it('réactive une licence suspendue encore valide', function () {
    $license = createAdminConsoleLicense($this->client, 'suspended', ['ends_at' => now()->addMonth()]);

    $this->actingAs($this->admin)
        ->post(route('admin.licenses.reactivate', $license), ['reason' => 'Régularisation'])
        ->assertSessionHas('success');

    expect($license->fresh()->status)->toBe('active');
});

it('révoque une licence et la marque revoked', function () {
    $license = createAdminConsoleLicense($this->client, 'active');

    $this->actingAs($this->admin)
        ->post(route('admin.licenses.revoke', $license), [
            'reason' => 'Fraude avérée',
            'confirmation' => true,
        ])
        ->assertSessionHas('success');

    expect($license->fresh()->status)->toBe('revoked');
    expect(PaymentAuditLog::where('action', 'license_revoked')->where('entity_id', $license->id)->exists())->toBeTrue();
});

it('interdit toute action sur une licence déjà révoquée', function () {
    $license = createAdminConsoleLicense($this->client, 'revoked');

    $this->actingAs($this->admin)
        ->post(route('admin.licenses.suspend', $license), ['reason' => 'Test'])
        ->assertSessionHasErrors('reason');

    expect($license->fresh()->status)->toBe('revoked');
});

/*
|--------------------------------------------------------------------------
| Moyens de paiement (CRUD)
|--------------------------------------------------------------------------
*/

it('crée un moyen de paiement', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.methods.store'), [
            'type' => 'bank_national',
            'label' => 'Banque Atlantique CI',
            'country' => 'CI',
            'currency' => 'XOF',
            'account_number' => 'CI0123456789',
            'is_active' => true,
        ])
        ->assertSessionHas('success');

    expect(PaymentMethodConfig::where('label', 'Banque Atlantique CI')->exists())->toBeTrue();
});

it('refuse un moyen de paiement de type invalide (422)', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.methods.store'), [
            'type' => 'crypto',
            'label' => 'Bitcoin',
            'currency' => 'XOF',
        ])
        ->assertSessionHasErrors('type');
});

it('met à jour un moyen de paiement', function () {
    $method = createAdminConsolePaymentMethod();

    $this->actingAs($this->admin)
        ->put(route('admin.methods.update', $method), [
            'type' => $method->type,
            'label' => 'Orange Money CI (mis à jour)',
            'currency' => 'XOF',
            'is_active' => true,
        ])
        ->assertSessionHas('success');

    expect($method->fresh()->label)->toBe('Orange Money CI (mis à jour)');
});

it('bascule l\'activation d\'un moyen de paiement', function () {
    $method = createAdminConsolePaymentMethod(['is_active' => true]);

    $this->actingAs($this->admin)
        ->post(route('admin.methods.toggle', $method))
        ->assertSessionHas('success');

    expect($method->fresh()->is_active)->toBeFalse();
});

it('supprime un moyen de paiement', function () {
    $method = createAdminConsolePaymentMethod();

    $this->actingAs($this->admin)
        ->delete(route('admin.methods.destroy', $method))
        ->assertSessionHas('success');

    expect(PaymentMethodConfig::find($method->id))->toBeNull();
});

/*
|--------------------------------------------------------------------------
| Forfaits
|--------------------------------------------------------------------------
*/

it('met à jour le tarif d\'un forfait et journalise', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('admin.plans.update', $plan), [
            'price_monthly' => 12345,
            'trial_days' => 14,
            'is_active' => true,
            'short_description' => 'Nouvelle offre Pro',
        ])
        ->assertSessionHas('success');

    expect((float) $plan->fresh()->price_monthly)->toBe(12345.00)
        ->and((int) $plan->fresh()->trial_days)->toBe(14);

    expect(PaymentAuditLog::where('action', 'plan_updated')->where('entity_id', $plan->id)->exists())->toBeTrue();
});

it('refuse un prix mensuel négatif (422)', function () {
    $plan = Plan::where('code', 'pro')->firstOrFail();

    $this->actingAs($this->admin)
        ->put(route('admin.plans.update', $plan), [
            'price_monthly' => -100,
            'trial_days' => 7,
        ])
        ->assertSessionHasErrors('price_monthly');
});
