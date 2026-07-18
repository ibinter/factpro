<?php

use App\Models\CryptoWallet;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Models\User;
use App\Services\ManualPaymentMethodService;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Helpers locaux
|--------------------------------------------------------------------------
*/

function createCryptoWallet(array $attributes = []): CryptoWallet
{
    return CryptoWallet::create([
        'currency'               => 'USDT',
        'network'                => 'TRC20',
        'wallet_address'         => 'TXxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'label'                  => 'USDT TRC20 (Tron)',
        'confirmations_required' => 1,
        'is_active'              => true,
        'display_order'          => 0,
        ...$attributes,
    ]);
}

/*
|--------------------------------------------------------------------------
| Migration / Table
|--------------------------------------------------------------------------
*/

it('crypto wallets table is created', function () {
    expect(\Schema::hasTable('crypto_wallets'))->toBeTrue();
});

it('crypto wallets model saves and retrieves correctly', function () {
    $wallet = createCryptoWallet();
    expect($wallet->currency)->toBe('USDT');
    expect($wallet->network)->toBe('TRC20');
    expect($wallet->display_label)->toBe('USDT TRC20 (Tron)');
});

it('active scope filters inactive wallets', function () {
    createCryptoWallet(['is_active' => true]);
    createCryptoWallet(['currency' => 'BTC', 'network' => 'Bitcoin', 'is_active' => false]);

    expect(CryptoWallet::active()->count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Seeder
|--------------------------------------------------------------------------
*/

it('crypto wallets seeded correctly', function () {
    app(ManualPaymentMethodService::class)->seedCryptoWallets();

    expect(CryptoWallet::count())->toBe(5);
    expect(CryptoWallet::where('currency', 'USDT')->where('network', 'TRC20')->exists())->toBeTrue();
    expect(CryptoWallet::where('currency', 'BTC')->exists())->toBeTrue();
    expect(CryptoWallet::where('currency', 'ETH')->exists())->toBeTrue();
    expect(CryptoWallet::where('currency', 'BNB')->exists())->toBeTrue();
});

it('seedCryptoWallets is idempotent', function () {
    $service = app(ManualPaymentMethodService::class);
    $service->seedCryptoWallets();
    $service->seedCryptoWallets();

    expect(CryptoWallet::count())->toBe(5);
});

/*
|--------------------------------------------------------------------------
| Checkout — wallets actifs passés à la vue
|--------------------------------------------------------------------------
*/

it('active wallets returned in checkout', function () {
    seedPlans();
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan);

    createCryptoWallet(['is_active' => true]);
    createCryptoWallet(['currency' => 'BTC', 'network' => 'Bitcoin', 'is_active' => false]);

    $response = $this->actingAs($user)->get(route('billing.checkout', $order));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Billing/Checkout')
        ->has('activeWallets', 1)
    );
});

/*
|--------------------------------------------------------------------------
| Soumission de transaction crypto
|--------------------------------------------------------------------------
*/

it('can submit crypto transaction declaration', function () {
    seedPlans();
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan);
    $wallet = createCryptoWallet();

    $txHash = '0x'.Str::random(64);

    $response = $this->actingAs($user)->post(route('billing.initiate.crypto'), [
        'order_id'        => $order->id,
        'wallet_id'       => $wallet->id,
        'tx_hash'         => $txHash,
        'declared_amount' => 15.50,
        'tx_date'         => now()->format('Y-m-d\TH:i'),
    ]);

    $response->assertRedirect(route('billing.proof-status', $order));
    $response->assertSessionHas('success');

    $transaction = PaymentTransaction::where('provider_reference', $txHash)->first();
    expect($transaction)->not->toBeNull();
    expect($transaction->payment_provider)->toBe('crypto');
    expect($order->fresh()->status)->toBe('proof_submitted');
});

it('rejects duplicate tx hash', function () {
    seedPlans();
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan);
    $wallet = createCryptoWallet();

    $txHash = '0x'.Str::random(64);

    // Première soumission
    $this->actingAs($user)->post(route('billing.initiate.crypto'), [
        'order_id'        => $order->id,
        'wallet_id'       => $wallet->id,
        'tx_hash'         => $txHash,
        'declared_amount' => 15.50,
        'tx_date'         => now()->format('Y-m-d\TH:i'),
    ]);

    // Seconde soumission avec le même hash (autre commande)
    $order2 = createPendingOrder($user, $plan);
    $response = $this->actingAs($user)->post(route('billing.initiate.crypto'), [
        'order_id'        => $order2->id,
        'wallet_id'       => $wallet->id,
        'tx_hash'         => $txHash,
        'declared_amount' => 15.50,
        'tx_date'         => now()->format('Y-m-d\TH:i'),
    ]);

    $response->assertSessionHasErrors('tx_hash');
});

it('crypto payment requires valid wallet id', function () {
    seedPlans();
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan);

    $response = $this->actingAs($user)->post(route('billing.initiate.crypto'), [
        'order_id'        => $order->id,
        'wallet_id'       => 9999,
        'tx_hash'         => '0x'.Str::random(64),
        'declared_amount' => 15.50,
        'tx_date'         => now()->format('Y-m-d\TH:i'),
    ]);

    $response->assertSessionHasErrors('wallet_id');
});

it('crypto form validates tx hash required', function () {
    seedPlans();
    $user = createUserWithCompany();
    $plan = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan);
    $wallet = createCryptoWallet();

    $response = $this->actingAs($user)->post(route('billing.initiate.crypto'), [
        'order_id'        => $order->id,
        'wallet_id'       => $wallet->id,
        'tx_hash'         => '',
        'declared_amount' => 15.50,
        'tx_date'         => now()->format('Y-m-d\TH:i'),
    ]);

    $response->assertSessionHasErrors('tx_hash');
});

it('crypto initiate requires auth', function () {
    $response = $this->post(route('billing.initiate.crypto'), []);
    $response->assertRedirect(route('login'));
});

it('cannot submit crypto for another user order', function () {
    seedPlans();
    $user  = createUserWithCompany();
    $other = createUserWithCompany();
    $plan  = Plan::where('code', 'pro')->firstOrFail();
    $order = createPendingOrder($user, $plan);
    $wallet = createCryptoWallet();

    $response = $this->actingAs($other)->post(route('billing.initiate.crypto'), [
        'order_id'        => $order->id,
        'wallet_id'       => $wallet->id,
        'tx_hash'         => '0x'.Str::random(64),
        'declared_amount' => 15.50,
        'tx_date'         => now()->format('Y-m-d\TH:i'),
    ]);

    $response->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Admin — CRUD wallets
|--------------------------------------------------------------------------
*/

it('admin can view crypto wallets', function () {
    $admin = User::factory()->create(['is_superadmin' => true]);
    createCryptoWallet();

    $response = $this->actingAs($admin)->get(route('admin.crypto-wallets.index'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/CryptoWallets')
        ->has('wallets', 1)
    );
});

it('admin can toggle crypto wallet active status', function () {
    $admin = User::factory()->create(['is_superadmin' => true]);
    $wallet = createCryptoWallet(['is_active' => true]);

    $this->actingAs($admin)->put(route('admin.crypto-wallets.update', $wallet), [
        ...$wallet->toArray(),
        'is_active' => false,
    ])->assertRedirect();

    expect($wallet->fresh()->is_active)->toBeFalse();
});

it('admin can update wallet address', function () {
    $admin = User::factory()->create(['is_superadmin' => true]);
    $wallet = createCryptoWallet();

    $newAddress = 'TNEWxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

    $this->actingAs($admin)->put(route('admin.crypto-wallets.update', $wallet), [
        ...$wallet->toArray(),
        'wallet_address' => $newAddress,
    ])->assertRedirect();

    expect($wallet->fresh()->wallet_address)->toBe($newAddress);
});

it('non-admin cannot manage crypto wallets', function () {
    $user = createUserWithCompany();
    $wallet = createCryptoWallet();

    $this->actingAs($user)->get(route('admin.crypto-wallets.index'))->assertForbidden();
    $this->actingAs($user)->put(route('admin.crypto-wallets.update', $wallet), [])->assertForbidden();
});
