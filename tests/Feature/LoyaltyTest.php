<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyReward;
use App\Services\LoyaltyService;

// ─────────────────────────────────────────────────────────────────────────────
// Helpers locaux
// ─────────────────────────────────────────────────────────────────────────────

function createLoyaltyProgram($company, array $attrs = []): LoyaltyProgram
{
    return LoyaltyProgram::create([
        'company_id' => $company->id,
        'name' => 'Programme Test',
        'is_active' => true,
        'points_per_1000' => 1,
        'currency' => 'XOF',
        'bronze_threshold' => 0,
        'silver_threshold' => 500,
        'gold_threshold' => 2000,
        'expiry_months' => null,
        ...$attrs,
    ]);
}

function createDocumentForCustomer($company, $customer, float $total = 5000): Document
{
    $doc = Document::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'type' => 'invoice',
        'number' => 'INV-' . uniqid(),
        'status' => 'paid',
        'issue_date' => now()->toDateString(),
        'due_date' => now()->addDays(30)->toDateString(),
        'currency' => 'XOF',
        'subtotal' => $total,
        'tax_amount' => 0,
        'discount_amount' => 0,
        'total' => $total,
        'amount_paid' => $total,
    ]);

    return $doc;
}

// ─────────────────────────────────────────────────────────────────────────────
// Tests
// ─────────────────────────────────────────────────────────────────────────────

it('creates a loyalty program for company', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;

    $program = createLoyaltyProgram($company);

    expect($program->company_id)->toBe($company->id)
        ->and($program->is_active)->toBeTrue()
        ->and($program->silver_threshold)->toBe(500)
        ->and($program->gold_threshold)->toBe(2000);
});

it('awards points when payment is registered', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $customer = createCustomerFor($company);

    createLoyaltyProgram($company);

    $doc = createDocumentForCustomer($company, $customer, 5000);

    $service = app(LoyaltyService::class);
    $points = $service->awardPoints($doc, 5000);

    expect($points)->toBe(5)
        ->and(LoyaltyPoint::where('customer_id', $customer->id)->count())->toBe(1);
});

it('calculates correct points based on amount', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;
    $customer = createCustomerFor($company);

    createLoyaltyProgram($company, ['points_per_1000' => 2]);
    $doc = createDocumentForCustomer($company, $customer, 3500);

    $service = app(LoyaltyService::class);
    $points = $service->awardPoints($doc, 3500);

    // 3500 / 1000 * 2 = 7 points
    expect($points)->toBe(7);
});

it('returns customer balance correctly', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;
    $customer = createCustomerFor($company);

    createLoyaltyProgram($company);

    $service = app(LoyaltyService::class);

    $doc1 = createDocumentForCustomer($company, $customer, 5000);
    $service->awardPoints($doc1, 5000);

    $doc2 = createDocumentForCustomer($company, $customer, 3000);
    $service->awardPoints($doc2, 3000);

    expect($service->getBalance($customer->id, $company->id))->toBe(8);
});

it('detects gold level at threshold', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;

    $program = createLoyaltyProgram($company);
    $service = app(LoyaltyService::class);

    $level = $service->getLevel(2000, $program);

    expect($level['name'])->toBe('Or');
});

it('detects silver level at threshold', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;

    $program = createLoyaltyProgram($company);
    $service = app(LoyaltyService::class);

    $level = $service->getLevel(500, $program);
    expect($level['name'])->toBe('Argent');

    $levelBelow = $service->getLevel(499, $program);
    expect($levelBelow['name'])->toBe('Bronze');
});

it('redeems reward and creates coupon', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;
    $customer = createCustomerFor($company);

    createLoyaltyProgram($company);

    $service = app(LoyaltyService::class);
    $doc = createDocumentForCustomer($company, $customer, 100000);
    $service->awardPoints($doc, 100000);

    $reward = LoyaltyReward::create([
        'company_id' => $company->id,
        'name' => 'Remise 10%',
        'points_cost' => 50,
        'reward_type' => 'discount_percent',
        'reward_value' => 10,
        'is_active' => true,
    ]);

    $code = $service->redeemReward($customer->id, $company->id, $reward);

    expect($code)->toStartWith('FID-');

    $balanceAfter = $service->getBalance($customer->id, $company->id);
    expect($balanceAfter)->toBe(50); // 100 - 50
});

it('rejects redemption when insufficient points', function () {
    $user = createUserWithCompany();
    $company = $user->currentCompany;
    $customer = createCustomerFor($company);

    createLoyaltyProgram($company);

    $reward = LoyaltyReward::create([
        'company_id' => $company->id,
        'name' => 'Remise premium',
        'points_cost' => 1000,
        'reward_type' => 'discount_percent',
        'reward_value' => 20,
        'is_active' => true,
    ]);

    $service = app(LoyaltyService::class);

    expect(fn () => $service->redeemReward($customer->id, $company->id, $reward))
        ->toThrow(\Exception::class, 'Solde de points insuffisant.');
});

it('generates loyalty card pdf', function () {
    $user = createUserWithCompanyAndTrial();
    $company = $user->currentCompany;
    $customer = createCustomerFor($company);

    createLoyaltyProgram($company);

    $response = $this->actingAs($user)
        ->get(route('loyalty.card', $customer));

    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'application/pdf');
});

it('isolates loyalty between companies', function () {
    $user1 = createUserWithCompany();
    $user2 = createUserWithCompany();
    $company1 = $user1->currentCompany;
    $company2 = $user2->currentCompany;

    $customer1 = createCustomerFor($company1);
    $customer2 = createCustomerFor($company2);

    createLoyaltyProgram($company1);
    createLoyaltyProgram($company2);

    $service = app(LoyaltyService::class);

    $doc1 = createDocumentForCustomer($company1, $customer1, 50000);
    $service->awardPoints($doc1, 50000);

    expect($service->getBalance($customer1->id, $company1->id))->toBe(50)
        ->and($service->getBalance($customer2->id, $company2->id))->toBe(0)
        ->and($service->getBalance($customer1->id, $company2->id))->toBe(0);
});
