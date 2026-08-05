<?php

use App\Http\Controllers\DocumentController;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Régression : doublon de route documents.convert
|--------------------------------------------------------------------------
| POST /documents/{document}/convert était déclarée deux fois sous le même
| nom `documents.convert` :
|   - web.php                        → DocumentController@convert (testé, OK)
|   - feature_document_conversion.php → DocumentConversionController@convert
| Ce dernier fichier étant requis APRÈS web.php, sa route écrasait la bonne.
| DocumentConversionController@convert appelait Document::generateNumber()
| (méthode inexistante) → 500 sur TOUTE conversion en production.
|
| Ces tests verrouillent : (1) le nom résout vers le bon contrôleur ; (2) la
| conversion HTTP réelle réussit (redirect, pas 500) et crée la facture.
*/

beforeEach(function () {
    $this->user = createUserWithCompanyAndTrial();
    $this->company = $this->user->currentCompany;
});

it('mappe la route documents.convert vers DocumentController@convert (pas le doublon)', function () {
    $route = Route::getRoutes()->getByName('documents.convert');

    expect($route)->not->toBeNull();
    expect($route->getActionName())->toBe(DocumentController::class.'@convert');
    // Garde-fou explicite contre le retour du contrôleur cassé.
    expect($route->getActionName())->not->toContain('DocumentConversionController');
});

it('convertit un devis en facture via HTTP sans 500 et crée la facture liée', function () {
    $service = app(DocumentService::class);
    $quote = $service->create($this->company, $this->user, [
        'type' => 'quote',
        'issue_date' => now()->toDateString(),
        'currency' => 'XOF',
    ], [
        ['description' => 'Prestation', 'quantity' => 2, 'unit_price' => 50000, 'tax_rate' => 18],
    ]);
    $service->finalize($quote);

    $response = $this->actingAs($this->user)
        ->post(route('documents.convert', $quote), ['target_type' => 'invoice']);

    // Le bug produisait un 500 (Document::generateNumber() inexistant) ; on attend une redirection.
    $response->assertRedirect();
    $response->assertSessionHas('success');

    $invoice = Document::where('company_id', $this->company->id)
        ->where('type', 'invoice')
        ->where('parent_id', $quote->id)
        ->firstOrFail();

    expect($invoice->status)->toBe('draft')
        ->and($invoice->lines()->count())->toBe(1)
        ->and((float) $invoice->total)->toBe((float) $quote->total)
        // public_token régénéré (sinon collision UNIQUE — l'autre bug de conversion).
        ->and($invoice->public_token)->not->toBe($quote->public_token)
        // La signature client du devis ne doit pas être héritée.
        ->and($invoice->signature_path)->toBeNull()
        ->and($invoice->signed_by_name)->toBeNull();

    expect($quote->fresh()->status)->toBe('converted');
});
