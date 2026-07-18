<?php

use App\Models\Document;
use App\Models\License;
use App\Models\Plan;
use App\Services\DocumentService;
use App\Services\LicenseService;

/** Crée un utilisateur + société + licence ACTIVE sur le plan starter (5 templates). */
function createUserWithStarterLicense(): \App\Models\User
{
    seedPlans();

    $user = createUserWithCompany();
    $plan = Plan::where('code', 'starter')->firstOrFail();

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

it('persists a valid template_key when storing a document', function () {
    $user = createUserWithCompanyAndTrial(); // essai = plan PRO → 30 templates (les 12 dispo)

    $response = $this->actingAs($user)->post('/documents', [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'template_key' => 'luxury-01',
        'lines' => [
            ['description' => 'Prestation', 'quantity' => 1, 'unit_price' => 10000, 'tax_rate' => 18],
        ],
    ]);

    $document = Document::where('company_id', $user->current_company_id)->firstOrFail();

    $response->assertRedirect(route('documents.show', $document));
    expect($document->template_key)->toBe('luxury-01');
});

it('rejects a template_key beyond the starter plan limit with a validation error', function () {
    $user = createUserWithStarterLicense(); // starter → 5 premiers templates seulement

    // africa-01 est le 11e modèle du registre : hors forfait starter
    $response = $this->actingAs($user)->post('/documents', [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'template_key' => 'africa-01',
        'lines' => [
            ['description' => 'Prestation', 'quantity' => 1, 'unit_price' => 10000, 'tax_rate' => 18],
        ],
    ]);

    $response->assertSessionHasErrors('template_key');
    expect(Document::where('company_id', $user->current_company_id)->count())->toBe(0);

    // Le 5e modèle du registre (luxury-01) reste, lui, autorisé pour starter
    $ok = $this->actingAs($user)->post('/documents', [
        'type' => 'invoice',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
        'template_key' => 'luxury-01',
        'lines' => [
            ['description' => 'Prestation', 'quantity' => 1, 'unit_price' => 10000, 'tax_rate' => 18],
        ],
    ]);

    $ok->assertSessionDoesntHaveErrors('template_key');
});

it('renders corporate-03 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'corporate-03'],
        [['description' => 'Test', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders minimal-02 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'minimal-02'],
        [['description' => 'Test', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders luxury-02 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'luxury-02'],
        [['description' => 'Test', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders creative-02 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'creative-02'],
        [['description' => 'Test', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders resto-02 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'resto-02'],
        [['description' => 'Test', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders africa-02 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'africa-02'],
        [['description' => 'Test', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders legal-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'legal-01'],
        [['description' => 'Honoraires', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders immo-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'immo-01'],
        [['description' => 'Commission vente', 'quantity' => 1, 'unit_price' => 50000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders finance-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'finance-01'],
        [['description' => 'Frais de gestion', 'quantity' => 1, 'unit_price' => 5000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders sport-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'sport-01'],
        [['description' => 'Abonnement mensuel', 'quantity' => 1, 'unit_price' => 15000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders beauty-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'beauty-01'],
        [['description' => 'Coiffure et soins', 'quantity' => 1, 'unit_price' => 8000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders transport-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'transport-01'],
        [['description' => 'Transport marchandises', 'quantity' => 1, 'unit_price' => 25000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders education-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'education-01'],
        [['description' => 'Frais de scolarité', 'quantity' => 1, 'unit_price' => 150000, 'tax_rate' => 0]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders medical-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'medical-01'],
        [['description' => 'Consultation médicale', 'quantity' => 1, 'unit_price' => 10000, 'tax_rate' => 0]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders hotel-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'hotel-01'],
        [['description' => 'Nuitée suite deluxe', 'quantity' => 3, 'unit_price' => 80000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders ong-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'ong-01'],
        [['description' => 'Don caritatif', 'quantity' => 1, 'unit_price' => 50000, 'tax_rate' => 0]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders agri-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'agri-01'],
        [['description' => 'Vente cacao — 500 kg', 'quantity' => 500, 'unit_price' => 600, 'tax_rate' => 0]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders btp-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'btp-01'],
        [['description' => 'Situation de travaux n°1', 'quantity' => 1, 'unit_price' => 2500000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders tech-saas-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'tech-saas-01'],
        [['description' => 'Abonnement SaaS Pro — 12 mois', 'quantity' => 1, 'unit_price' => 120000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders resto-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'resto-01'],
        [['description' => 'Menu dégustation', 'quantity' => 2, 'unit_price' => 45000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('renders auto-01 without error', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(\App\Services\DocumentService::class)->create(
        $user->currentCompany,
        $user,
        ['type' => 'invoice', 'issue_date' => now()->toDateString(), 'currency' => 'XOF', 'template_key' => 'auto-01'],
        [['description' => 'Révision complète + pneus', 'quantity' => 1, 'unit_price' => 180000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

it('falls back to the default PDF view when template_key is unknown', function () {
    $user = createUserWithCompanyAndTrial();

    $document = app(DocumentService::class)->create(
        $user->currentCompany,
        $user,
        [
            'type' => 'invoice',
            'issue_date' => now()->toDateString(),
            'currency' => 'XOF',
            'template_key' => 'modele-fantome-99', // inconnu du registre
        ],
        [['description' => 'Prestation', 'quantity' => 1, 'unit_price' => 10000, 'tax_rate' => 18]],
    );

    $response = $this->actingAs($user)->get(route('documents.pdf', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
