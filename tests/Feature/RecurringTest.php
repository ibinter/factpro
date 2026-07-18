<?php

use App\Mail\DocumentMail;
use App\Models\Customer;
use App\Models\Document;
use App\Models\License;
use App\Models\Plan;
use App\Models\RecurringTemplate;
use App\Models\User;
use App\Services\LicenseService;
use App\Services\RecurringService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Helpers locaux au module factures récurrentes (préfixe createRecurringTemplate…)
|--------------------------------------------------------------------------
*/

/** Crée une licence active sur un plan donné. */
function createRecurringTemplateLicense(User $user, string $planCode): License
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

/** Crée un utilisateur + société + licence active du plan donné. */
function createRecurringTemplateOwner(string $planCode = 'pro'): User
{
    $user = createUserWithCompany();
    createRecurringTemplateLicense($user, $planCode);

    return $user;
}

/**
 * Crée un gabarit récurrent pour la société de l'utilisateur.
 * Lignes par défaut : 2 × 10 000 à 18 % TVA → HT 20 000, TVA 3 600, TTC 23 600.
 */
function createRecurringTemplateFor(User $user, array $attributes = [], ?Customer $customer = null): RecurringTemplate
{
    $customer ??= createCustomerFor($user->currentCompany, ['email' => 'client@exemple.ci']);

    return RecurringTemplate::create([
        'company_id' => $user->current_company_id,
        'customer_id' => $customer->id,
        'created_by' => $user->id,
        'name' => 'Abonnement maintenance mensuel',
        'frequency' => 'monthly',
        'interval' => 1,
        'day_of_month' => 15,
        'next_run_date' => '2026-08-15',
        'currency' => 'XOF',
        'due_days' => 30,
        'auto_finalize' => false,
        'auto_send' => false,
        'lines' => [[
            'product_id' => null,
            'description' => 'Maintenance applicative',
            'quantity' => 2,
            'unit' => 'mois',
            'unit_price' => 10000,
            'discount_percent' => 0,
            'tax_rate' => 18,
        ]],
        'is_active' => true,
        ...$attributes,
    ]);
}

/*
|--------------------------------------------------------------------------
| Gate forfait (PRO et plus — STARTER exclu)
|--------------------------------------------------------------------------
*/

it('shows the upsell page for a STARTER user', function () {
    $user = createRecurringTemplateOwner('starter');

    $this->actingAs($user)
        ->get(route('recurring.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Recurring/Index')
            ->where('hasAccess', false));
});

it('forbids mutations for a STARTER user', function () {
    $user = createRecurringTemplateOwner('starter');
    $customer = createCustomerFor($user->currentCompany);

    $this->actingAs($user)->post(route('recurring.store'), [
        'name' => 'Test',
        'customer_id' => $customer->id,
        'frequency' => 'monthly',
        'interval' => 1,
        'next_run_date' => now()->addDay()->toDateString(),
        'due_days' => 30,
        'lines' => [['description' => 'Ligne', 'quantity' => 1, 'unit_price' => 1000]],
    ])->assertForbidden();
});

it('lets a PRO user create a recurring template', function () {
    $user = createRecurringTemplateOwner('pro');
    $customer = createCustomerFor($user->currentCompany);

    $this->actingAs($user)->post(route('recurring.store'), [
        'name' => 'Abonnement hébergement',
        'customer_id' => $customer->id,
        'frequency' => 'monthly',
        'interval' => 1,
        'day_of_month' => 5,
        'next_run_date' => now()->addDay()->toDateString(),
        'due_days' => 30,
        'auto_finalize' => true,
        'auto_send' => false,
        'lines' => [['description' => 'Hébergement web', 'quantity' => 1, 'unit_price' => 25000, 'tax_rate' => 18]],
    ])->assertRedirect()->assertSessionHas('success');

    $this->assertDatabaseHas('recurring_templates', [
        'company_id' => $user->current_company_id,
        'customer_id' => $customer->id,
        'name' => 'Abonnement hébergement',
        'frequency' => 'monthly',
        'day_of_month' => 5,
        'is_active' => true,
    ]);
});

/*
|--------------------------------------------------------------------------
| Moteur de génération (RecurringService::runDue)
|--------------------------------------------------------------------------
*/

it('generates the invoice at the due date with correct totals and advances the template', function () {
    Mail::fake();
    $user = createRecurringTemplateOwner('pro');
    $template = createRecurringTemplateFor($user);

    $count = app(RecurringService::class)->runDue(Carbon::parse('2026-08-15'));

    expect($count)->toBe(1);

    $invoice = Document::where('reference', 'REC-'.$template->id)->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->type)->toBe('invoice')
        ->and($invoice->customer_id)->toBe($template->customer_id)
        ->and($invoice->issue_date->toDateString())->toBe('2026-08-15')
        ->and($invoice->due_date->toDateString())->toBe('2026-09-14')
        ->and((float) $invoice->subtotal)->toBe(20000.0)
        ->and((float) $invoice->tax_amount)->toBe(3600.0)
        ->and((float) $invoice->total)->toBe(23600.0);

    $template->refresh();
    expect($template->occurrences_done)->toBe(1)
        ->and($template->last_run_date->toDateString())->toBe('2026-08-15')
        ->and($template->next_run_date->toDateString())->toBe('2026-09-15') // +1 mois calé au 15
        ->and($template->is_active)->toBeTrue();
});

it('does not generate twice when replayed the same day (idempotence)', function () {
    Mail::fake();
    $user = createRecurringTemplateOwner('pro');
    $template = createRecurringTemplateFor($user);

    $service = app(RecurringService::class);
    expect($service->runDue(Carbon::parse('2026-08-15')))->toBe(1)
        ->and($service->runDue(Carbon::parse('2026-08-15')))->toBe(0)
        ->and(Document::where('reference', 'REC-'.$template->id)->count())->toBe(1);
});

it('seals the invoice when auto_finalize is enabled', function () {
    Mail::fake();
    $user = createRecurringTemplateOwner('pro');
    $template = createRecurringTemplateFor($user, ['auto_finalize' => true]);

    app(RecurringService::class)->runDue(Carbon::parse('2026-08-15'));

    $invoice = Document::where('reference', 'REC-'.$template->id)->first();
    expect($invoice->finalized_at)->not->toBeNull()
        ->and($invoice->integrity_hash)->not->toBeNull();
});

it('emails the invoice to the customer when auto_send is enabled', function () {
    Mail::fake();
    $user = createRecurringTemplateOwner('pro');
    $template = createRecurringTemplateFor($user, ['auto_send' => true]);

    app(RecurringService::class)->runDue(Carbon::parse('2026-08-15'));

    Mail::assertSent(DocumentMail::class, function (DocumentMail $mail) use ($template) {
        return $mail->hasTo('client@exemple.ci')
            && $mail->document->reference === 'REC-'.$template->id;
    });

    $invoice = Document::where('reference', 'REC-'.$template->id)->first();
    expect($invoice->status)->toBe('sent')
        ->and($invoice->sent_at)->not->toBeNull();
});

it('deactivates the template once the occurrences limit is reached', function () {
    Mail::fake();
    $user = createRecurringTemplateOwner('pro');
    $template = createRecurringTemplateFor($user, ['occurrences_limit' => 1]);

    app(RecurringService::class)->runDue(Carbon::parse('2026-08-15'));

    $template->refresh();
    expect($template->occurrences_done)->toBe(1)
        ->and($template->is_active)->toBeFalse();

    // Rejoué plus tard : plus rien n'est généré
    expect(app(RecurringService::class)->runDue(Carbon::parse('2026-09-15')))->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Actions HTTP (génération manuelle, isolation multi-sociétés)
|--------------------------------------------------------------------------
*/

it('generates an invoice immediately via the manual run action', function () {
    Mail::fake();
    $user = createRecurringTemplateOwner('pro');
    $template = createRecurringTemplateFor($user, ['next_run_date' => now()->toDateString()]);

    $response = $this->actingAs($user)->post(route('recurring.run', $template));

    $invoice = Document::where('reference', 'REC-'.$template->id)->first();
    expect($invoice)->not->toBeNull()
        ->and($invoice->issue_date->toDateString())->toBe(now()->toDateString());

    $response->assertRedirect(route('documents.show', $invoice));

    $template->refresh();
    expect($template->occurrences_done)->toBe(1)
        ->and($template->next_run_date->gt(now()))->toBeTrue();
});

it('forbids acting on a template belonging to another company', function () {
    Mail::fake();
    $owner = createRecurringTemplateOwner('pro');
    $template = createRecurringTemplateFor($owner);

    $intruder = createRecurringTemplateOwner('pro');

    $this->actingAs($intruder)->post(route('recurring.run', $template))->assertForbidden();
    $this->actingAs($intruder)->post(route('recurring.toggle', $template))->assertForbidden();
    $this->actingAs($intruder)->delete(route('recurring.destroy', $template))->assertForbidden();
});
