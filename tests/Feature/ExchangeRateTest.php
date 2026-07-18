<?php

use App\Models\ExchangeRate;
use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;

/*
|--------------------------------------------------------------------------
| Multi-devises & taux de change (cahier IBIG §3 DEV / §14)
|--------------------------------------------------------------------------
*/

/** Enregistre un taux direct base -> currency. */
function createRate(string $base, string $currency, float $rate, string $source = 'api'): ExchangeRate
{
    return ExchangeRate::create([
        'base_currency' => $base,
        'currency' => $currency,
        'rate' => $rate,
        'fetched_at' => now(),
        'source' => $source,
    ]);
}

/** Réponse API open.er-api simulée (succès). */
function createFakeApiSuccess(): void
{
    Http::fake([
        'open.er-api.com/*' => Http::response([
            'result' => 'success',
            'base_code' => 'XOF',
            'rates' => [
                'XOF' => 1,
                'EUR' => 0.001524,
                'USD' => 0.001695,
                'GBP' => 0.001301,
                'ZZZ' => 9.99, // hors config -> ignoré
            ],
        ], 200),
    ]);
}

/** Réponse API en échec (serveur indisponible). */
function createFakeApiFailure(): void
{
    Http::fake([
        'open.er-api.com/*' => Http::response(null, 500),
    ]);
}

it('refresh insère les taux du config depuis l\'API (source api)', function () {
    createFakeApiSuccess();

    $count = app(ExchangeRateService::class)->refresh('XOF');

    expect($count)->toBe(4); // XOF, EUR, USD, GBP (ZZZ ignoré)
    expect(ExchangeRate::where('currency', 'ZZZ')->exists())->toBeFalse();

    $eur = ExchangeRate::where('base_currency', 'XOF')->where('currency', 'EUR')->first();
    expect($eur->source)->toBe('api');
    expect($eur->rate)->toBe(0.001524);
});

it('bascule sur le repli quand l\'API échoue (source fallback)', function () {
    createFakeApiFailure();

    $count = app(ExchangeRateService::class)->refresh('XOF');

    expect($count)->toBeGreaterThan(0);

    $eur = ExchangeRate::where('base_currency', 'XOF')->where('currency', 'EUR')->first();
    expect($eur)->not->toBeNull();
    expect($eur->source)->toBe('fallback');
    // 1 XOF = 1/655.957 EUR
    expect($eur->rate)->toEqualWithDelta(1 / 655.957, 1e-8);

    $usd = ExchangeRate::where('base_currency', 'XOF')->where('currency', 'USD')->first();
    expect($usd->source)->toBe('fallback');
    expect($usd->rate)->toEqualWithDelta(1 / 590.0, 1e-8);
});

it('seedFallback dérive EUR/USD du pivot fixe', function () {
    $count = app(ExchangeRateService::class)->seedFallback('XOF');

    expect($count)->toBe(3); // XOF, EUR, USD
    expect(ExchangeRate::where('source', 'fallback')->count())->toBe(3);
});

it('rate renvoie 1 pour une devise identique', function () {
    expect(app(ExchangeRateService::class)->rate('EUR', 'EUR'))->toBe(1.0);
});

it('rate direct lit le taux stocké', function () {
    createRate('XOF', 'EUR', 0.0015);

    expect(app(ExchangeRateService::class)->rate('XOF', 'EUR'))->toEqualWithDelta(0.0015, 1e-9);
});

it('rate inverse calcule le réciproque', function () {
    createRate('XOF', 'EUR', 0.0015);

    // EUR -> XOF non stocké : inverse de XOF -> EUR
    expect(app(ExchangeRateService::class)->rate('EUR', 'XOF'))->toEqualWithDelta(1 / 0.0015, 1e-6);
});

it('rate triangule via XOF', function () {
    createRate('XOF', 'EUR', 0.0015);
    createRate('XOF', 'USD', 0.0017);

    // EUR -> USD : (EUR->XOF) * (XOF->USD) = (1/0.0015) * 0.0017
    $expected = (1 / 0.0015) * 0.0017;
    expect(app(ExchangeRateService::class)->rate('EUR', 'USD'))->toEqualWithDelta($expected, 1e-6);
});

it('rate renvoie null pour une paire inconnue', function () {
    expect(app(ExchangeRateService::class)->rate('EUR', 'JPY'))->toBeNull();
});

it('convert applique le taux et arrondit à 2 décimales', function () {
    createRate('XOF', 'EUR', 0.0015);

    expect(app(ExchangeRateService::class)->convert(100000, 'XOF', 'EUR'))->toBe(150.0);
    expect(app(ExchangeRateService::class)->convert(1234, 'XOF', 'EUR'))->toBe(1.85);
    expect(app(ExchangeRateService::class)->convert(100, 'EUR', 'JPY'))->toBeNull();
});

it('freshness renvoie la date du dernier fetch', function () {
    expect(app(ExchangeRateService::class)->freshness())->toBeNull();

    createRate('XOF', 'EUR', 0.0015);

    expect(app(ExchangeRateService::class)->freshness())->not->toBeNull();
});

it('la commande rates:refresh s\'exécute et enregistre des taux', function () {
    createFakeApiSuccess();

    $this->artisan('rates:refresh')->assertSuccessful();

    expect(ExchangeRate::where('source', 'api')->count())->toBeGreaterThan(0);
});

it('le partage Inertia expose les taux EUR/USD après seed', function () {
    app(ExchangeRateService::class)->seedFallback('XOF');

    $user = createUserWithCompanyAndTrial();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('rates.base', 'XOF')
            ->where('rates.eur', fn ($v) => abs((float) $v - 1 / 655.957) < 1e-9)
            ->where('rates.usd', fn ($v) => abs((float) $v - 1 / 590.0) < 1e-9)
            ->whereNot('rates.updated_at', null)
        );
});
