<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    private function createTwoFactorUser(): User
    {
        return User::factory()->create();
    }

    /**
     * Active la 2FA sur l'utilisateur et retourne [$user, $secret].
     *
     * @return array{0: User, 1: string}
     */
    private function createTwoFactorEnabledUser(): array
    {
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();
        $codes = $service->generateRecoveryCodes();

        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => $service->encryptSecret($secret),
            'two_factor_recovery_codes' => $service->encryptRecoveryCodes($codes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return [$user, $secret];
    }

    private function createTwoFactorOtp(string $secret): string
    {
        return (new Google2FA())->getCurrentOtp($secret);
    }

    public function test_user_can_enable_two_factor_and_confirm(): void
    {
        $user = $this->createTwoFactorUser();

        $store = $this->actingAs($user)->postJson(route('two-factor.store'));
        $store->assertOk()
            ->assertJsonStructure(['qr', 'secret']);

        $secret = $store->json('secret');

        $confirm = $this->actingAs($user)->postJson(route('two-factor.confirm'), [
            'code' => $this->createTwoFactorOtp($secret),
        ]);

        $confirm->assertOk()
            ->assertJsonCount(8, 'recovery_codes');

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_confirm_with_invalid_code_returns_422(): void
    {
        $user = $this->createTwoFactorUser();

        $this->actingAs($user)->postJson(route('two-factor.store'))->assertOk();

        $this->actingAs($user)
            ->postJson(route('two-factor.confirm'), ['code' => '000000'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('code');

        $user->refresh();
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_login_with_two_factor_redirects_to_challenge(): void
    {
        [$user] = $this->createTwoFactorEnabledUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('two-factor.challenge'));
        $this->assertGuest();
    }

    public function test_challenge_with_valid_code_authenticates(): void
    {
        [$user, $secret] = $this->createTwoFactorEnabledUser();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response = $this->post(route('two-factor.challenge.store'), [
            'code' => $this->createTwoFactorOtp($secret),
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user);
    }

    public function test_challenge_with_recovery_code_authenticates_and_consumes_it(): void
    {
        $service = app(TwoFactorService::class);
        $secret = $service->generateSecret();
        $codes = $service->generateRecoveryCodes();
        $recoveryCode = $codes[0];

        $user = User::factory()->create();
        $user->forceFill([
            'two_factor_secret' => $service->encryptSecret($secret),
            'two_factor_recovery_codes' => $service->encryptRecoveryCodes($codes),
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Première connexion : le code de récupération fonctionne.
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->post(route('two-factor.challenge.store'), [
            'recovery_code' => $recoveryCode,
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);

        // Vérifie la consommation en base.
        $user->refresh();
        $remaining = json_decode(
            Crypt::decryptString($user->two_factor_recovery_codes),
            true
        );
        $this->assertNotContains($recoveryCode, $remaining);
        $this->assertCount(7, $remaining);

        // Déconnexion puis réutilisation : doit échouer.
        $this->post('/logout');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->post(route('two-factor.challenge.store'), [
            'recovery_code' => $recoveryCode,
        ])->assertSessionHasErrors('code');

        $this->assertGuest();
    }

    public function test_disable_with_wrong_password_returns_422(): void
    {
        [$user] = $this->createTwoFactorEnabledUser();

        $this->actingAs($user)
            ->deleteJson(route('two-factor.destroy'), [
                'current_password' => 'wrong-password',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('current_password');

        $user->refresh();
        $this->assertTrue($user->hasTwoFactorEnabled());
    }

    public function test_disable_with_correct_password_clears_columns(): void
    {
        [$user] = $this->createTwoFactorEnabledUser();

        $this->actingAs($user)
            ->deleteJson(route('two-factor.destroy'), [
                'current_password' => 'password',
            ])
            ->assertOk();

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->assertFalse($user->hasTwoFactorEnabled());
    }

    public function test_recovery_codes_can_be_regenerated_with_password(): void
    {
        [$user] = $this->createTwoFactorEnabledUser();

        $original = app(TwoFactorService::class)
            ->decryptRecoveryCodes($user->two_factor_recovery_codes);

        $response = $this->actingAs($user)->postJson(
            route('two-factor.recovery-codes'),
            ['current_password' => 'password']
        );

        $response->assertOk()->assertJsonCount(8, 'recovery_codes');

        $user->refresh();
        $new = app(TwoFactorService::class)
            ->decryptRecoveryCodes($user->two_factor_recovery_codes);

        $this->assertNotEquals($original, $new);
    }

    public function test_user_without_two_factor_logs_in_directly(): void
    {
        $user = $this->createTwoFactorUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
