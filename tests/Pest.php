<?php

/*
|--------------------------------------------------------------------------
| Configuration Pest
|--------------------------------------------------------------------------
| Tous les tests Feature utilisent la TestCase Laravel + RefreshDatabase
| (base sqlite :memory: — voir phpunit.xml).
*/

use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentProof;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Models\User;
use App\Services\LicenseService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Helpers partagés
|--------------------------------------------------------------------------
*/

/** Seed les 4 plans (starter/pro/business/enterprise) si absents. */
function seedPlans(): void
{
    if (! Plan::where('code', 'pro')->exists()) {
        test()->seed(PlanSeeder::class);
    }
}

/** Crée un utilisateur + sa société (pivot owner + current_company_id). */
function createUserWithCompany(array $userAttributes = []): User
{
    $user = User::factory()->create($userAttributes);

    $company = Company::create([
        'owner_id' => $user->id,
        'name' => 'Société Test '.$user->id,
        'email' => $user->email,
        'country' => 'CI',
        'currency' => 'XOF',
    ]);

    $company->users()->attach($user->id, ['role' => 'owner']);
    $user->forceFill(['current_company_id' => $company->id])->save();

    return $user->fresh();
}

/** Crée un utilisateur + société + licence d'essai 7 jours (plan pro). */
function createUserWithCompanyAndTrial(array $userAttributes = []): User
{
    seedPlans();

    $user = createUserWithCompany($userAttributes);
    app(LicenseService::class)->startTrial($user);

    return $user->fresh();
}

/** Crée un client rattaché à une société. */
function createCustomerFor(Company $company, array $attributes = []): Customer
{
    return Customer::create([
        'company_id' => $company->id,
        'name' => 'Client Test',
        'country' => 'CI',
        'currency' => 'XOF',
        ...$attributes,
    ]);
}

/** Crée une commande d'abonnement payable (pending_payment, expire dans 72h). */
function createPendingOrder(User $user, Plan $plan, int $months = 1, array $attributes = []): Order
{
    return Order::create([
        'order_number' => 'FP-'.now()->year.'-'.strtoupper(Str::random(6)),
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'duration_months' => $months,
        'amount' => $plan->priceFor($months),
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => $plan->priceFor($months),
        'currency' => 'XOF',
        'country' => 'CI',
        'status' => 'pending_payment',
        'expires_at' => now()->addHours(72),
        ...$attributes,
    ]);
}

/** Crée une transaction manuelle en cours de revue pour une commande. */
function createUnderReviewTransaction(Order $order, array $attributes = []): PaymentTransaction
{
    return PaymentTransaction::create([
        'order_id' => $order->id,
        'user_id' => $order->user_id,
        'payment_provider' => 'orange_money',
        'provider_reference' => 'OM-'.strtoupper(Str::random(8)),
        'internal_reference' => 'FP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
        'amount_expected' => $order->total_amount,
        'amount_declared' => $order->total_amount,
        'currency' => $order->currency,
        'status' => 'under_review',
        'sender_name' => 'Expéditeur Test',
        'initiated_at' => now(),
        ...$attributes,
    ]);
}

/** Crée une preuve de paiement (enregistrement seul, sans fichier réel). */
function createProofFor(PaymentTransaction $transaction, array $attributes = []): PaymentProof
{
    return PaymentProof::create([
        'transaction_id' => $transaction->id,
        'original_filename' => 'recu.jpg',
        'stored_filename' => Str::random(40).'.jpg',
        'file_path' => 'private/proofs/'.Str::random(40).'.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 1024,
        'file_hash' => hash('sha256', Str::random(32)),
        'uploaded_by' => $transaction->user_id,
        'verification_status' => 'pending',
        ...$attributes,
    ]);
}
