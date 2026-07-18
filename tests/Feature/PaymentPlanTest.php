<?php

use App\Models\Customer;
use App\Models\Document;
use App\Models\License;
use App\Models\PaymentPlan;
use App\Models\PaymentPlanInstallment;
use App\Models\Plan;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\LicenseService;
use App\Services\PaymentPlanService;

/*
|--------------------------------------------------------------------------
| Helpers locaux au module acomptes / plans de paiement (préfixe createDeposit…)
|--------------------------------------------------------------------------
*/

/** Licence active sur un plan donné. */
function createDepositLicense(User $user, string $planCode): License
{
    seedPlans();
    $plan = Plan::where('code', $planCode)->firstOrFail();

    return License::create([
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'license_key' => app(LicenseService::class)->generateKey(),
        'type' => 'paid',
        'status' => 'active',
        'starts_at' => now(),
        'ends_at' => now()->addYear(),
        'limits' => $plan->limits,
        'activation_source' => 'manual',
    ]);
}

/** Utilisateur + société + licence active du plan donné (pro par défaut). */
function createDepositOwner(string $planCode = 'pro'): User
{
    $user = createUserWithCompany();
    createDepositLicense($user, $planCode);

    return $user->fresh();
}

/** Devis finalisable de $total (une ligne HT sans TVA → total = $total). */
function createDepositQuote(User $user, float $total = 100000, ?Customer $customer = null): Document
{
    $customer ??= createCustomerFor($user->currentCompany, ['email' => 'client@exemple.ci']);

    return app(DocumentService::class)->create(
        $user->currentCompany,
        $user,
        [
            'type' => 'quote',
            'customer_id' => $customer->id,
            'issue_date' => now()->toDateString(),
            'currency' => 'XOF',
        ],
        [[
            'description' => 'Prestation',
            'quantity' => 1,
            'unit_price' => $total,
            'tax_rate' => 0,
        ]],
    );
}

/*
|--------------------------------------------------------------------------
| Gate forfait (PRO et plus — STARTER exclu)
|--------------------------------------------------------------------------
*/

it('forbids creating a payment plan for a STARTER user', function () {
    $user = createDepositOwner('starter');
    $quote = createDepositQuote($user);

    $this->actingAs($user)->post(route('payment-plans.create', $quote), [
        'installments' => [
            ['label' => 'Acompte', 'due_date' => now()->toDateString(), 'percentage' => 100],
        ],
    ])->assertForbidden();

    expect(PaymentPlan::count())->toBe(0);
});

it('forbids the payment plans index for a STARTER user', function () {
    $user = createDepositOwner('starter');

    $this->actingAs($user)->get(route('payment-plans.index'))->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Création d'un plan
|--------------------------------------------------------------------------
*/

it('creates a 30/70 plan from a 100000 quote', function () {
    $user = createDepositOwner('pro');
    $quote = createDepositQuote($user, 100000);

    $this->actingAs($user)->post(route('payment-plans.create', $quote), [
        'installments' => [
            ['label' => 'Acompte 30%', 'due_date' => now()->toDateString(), 'percentage' => 30],
            ['label' => 'Solde 70%', 'due_date' => now()->addDays(30)->toDateString(), 'percentage' => 70],
        ],
    ])->assertRedirect()->assertSessionHas('success');

    $plan = PaymentPlan::first();
    expect($plan)->not->toBeNull()
        ->and((float) $plan->total_amount)->toBe(100000.0)
        ->and($plan->status)->toBe('active')
        ->and($plan->installments)->toHaveCount(2)
        ->and((float) $plan->installments[0]->amount)->toBe(30000.0)
        ->and((float) $plan->installments[1]->amount)->toBe(70000.0)
        ->and($plan->installments[0]->status)->toBe('pending');
});

it('rejects a plan whose installments do not sum to the total', function () {
    $user = createDepositOwner('pro');
    $quote = createDepositQuote($user, 100000);

    $this->actingAs($user)->post(route('payment-plans.create', $quote), [
        'installments' => [
            ['label' => 'Acompte', 'due_date' => now()->toDateString(), 'amount' => 30000],
            ['label' => 'Solde', 'due_date' => now()->addDays(30)->toDateString(), 'amount' => 50000],
        ],
    ])->assertSessionHas('error');

    expect(PaymentPlan::count())->toBe(0);
});

it('throws when the sum differs from the total (service level)', function () {
    $user = createDepositOwner('pro');
    $quote = createDepositQuote($user, 100000);

    app(PaymentPlanService::class)->createFromDocument($quote, [
        ['label' => 'Acompte', 'due_date' => now()->toDateString(), 'amount' => 10000],
    ], $user);
})->throws(RuntimeException::class, 'La somme des échéances doit égaler le total');

/*
|--------------------------------------------------------------------------
| Génération des factures d'acompte / de solde
|--------------------------------------------------------------------------
*/

it('generates a finalized deposit invoice linked to the installment', function () {
    $user = createDepositOwner('pro');
    $quote = createDepositQuote($user, 100000);

    $plan = app(PaymentPlanService::class)->createFromDocument($quote, [
        ['label' => 'Acompte 30%', 'due_date' => now()->toDateString(), 'percentage' => 30],
        ['label' => 'Solde 70%', 'due_date' => now()->addDays(30)->toDateString(), 'percentage' => 70],
    ], $user);

    $first = $plan->installments->first();

    $response = $this->actingAs($user)->post(route('payment-plans.installment.invoice', $first));

    $first->refresh();
    $invoice = Document::find($first->document_id);

    expect($invoice)->not->toBeNull()
        ->and($invoice->type)->toBe('deposit_invoice')
        ->and($invoice->finalized_at)->not->toBeNull()
        ->and($invoice->parent_id)->toBe($quote->id)
        ->and((float) $invoice->total)->toBe(30000.0)
        ->and($first->status)->toBe('invoiced');

    $response->assertRedirect(route('documents.show', $invoice));
});

it('generates a balance invoice for the last installment', function () {
    $user = createDepositOwner('pro');
    $quote = createDepositQuote($user, 100000);

    $plan = app(PaymentPlanService::class)->createFromDocument($quote, [
        ['label' => 'Acompte 30%', 'due_date' => now()->toDateString(), 'percentage' => 30],
        ['label' => 'Solde 70%', 'due_date' => now()->addDays(30)->toDateString(), 'percentage' => 70],
    ], $user);

    $last = $plan->installments->last();
    $invoice = app(PaymentPlanService::class)->generateInstallmentInvoice($last, $user);

    expect($invoice->type)->toBe('balance_invoice')
        ->and((float) $invoice->total)->toBe(70000.0);
});

it('refuses to generate an invoice twice for the same installment', function () {
    $user = createDepositOwner('pro');
    $quote = createDepositQuote($user, 100000);

    $plan = app(PaymentPlanService::class)->createFromDocument($quote, [
        ['label' => 'Acompte 30%', 'due_date' => now()->toDateString(), 'percentage' => 30],
        ['label' => 'Solde 70%', 'due_date' => now()->addDays(30)->toDateString(), 'percentage' => 70],
    ], $user);

    $first = $plan->installments->first();
    app(PaymentPlanService::class)->generateInstallmentInvoice($first, $user);

    app(PaymentPlanService::class)->generateInstallmentInvoice($first->fresh(), $user);
})->throws(RuntimeException::class);

/*
|--------------------------------------------------------------------------
| Cycle de vie du plan
|--------------------------------------------------------------------------
*/

it('marks the plan completed once every installment is paid', function () {
    $user = createDepositOwner('pro');
    $quote = createDepositQuote($user, 100000);
    $service = app(PaymentPlanService::class);

    $plan = $service->createFromDocument($quote, [
        ['label' => 'Acompte 30%', 'due_date' => now()->toDateString(), 'percentage' => 30],
        ['label' => 'Solde 70%', 'due_date' => now()->addDays(30)->toDateString(), 'percentage' => 70],
    ], $user);

    foreach ($plan->installments as $installment) {
        $service->markInstallmentPaid($installment);
    }

    expect($plan->fresh()->status)->toBe('completed');
});

it('cancels a plan when no installment is paid', function () {
    $user = createDepositOwner('pro');
    $quote = createDepositQuote($user, 100000);

    $plan = app(PaymentPlanService::class)->createFromDocument($quote, [
        ['label' => 'Acompte', 'due_date' => now()->toDateString(), 'percentage' => 100],
    ], $user);

    $this->actingAs($user)->post(route('payment-plans.cancel', $plan))
        ->assertRedirect()->assertSessionHas('success');

    expect($plan->fresh()->status)->toBe('cancelled');
});

it('refuses to cancel a plan with a paid installment', function () {
    $user = createDepositOwner('pro');
    $quote = createDepositQuote($user, 100000);
    $service = app(PaymentPlanService::class);

    $plan = $service->createFromDocument($quote, [
        ['label' => 'Acompte 30%', 'due_date' => now()->toDateString(), 'percentage' => 30],
        ['label' => 'Solde 70%', 'due_date' => now()->addDays(30)->toDateString(), 'percentage' => 70],
    ], $user);

    $service->markInstallmentPaid($plan->installments->first());

    $this->actingAs($user)->post(route('payment-plans.cancel', $plan))->assertSessionHas('error');

    expect($plan->fresh()->status)->toBe('active');
});

/*
|--------------------------------------------------------------------------
| Isolation multi-sociétés
|--------------------------------------------------------------------------
*/

it('forbids acting on a plan belonging to another company', function () {
    $owner = createDepositOwner('pro');
    $quote = createDepositQuote($owner, 100000);
    $plan = app(PaymentPlanService::class)->createFromDocument($quote, [
        ['label' => 'Acompte', 'due_date' => now()->toDateString(), 'percentage' => 100],
    ], $owner);

    $intruder = createDepositOwner('pro');

    $this->actingAs($intruder)->get(route('payment-plans.show', $plan))->assertForbidden();
    $this->actingAs($intruder)->post(route('payment-plans.cancel', $plan))->assertForbidden();
    $this->actingAs($intruder)
        ->post(route('payment-plans.installment.invoice', $plan->installments->first()))
        ->assertForbidden();
});
