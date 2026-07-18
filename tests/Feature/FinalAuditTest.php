<?php

use Illuminate\Support\Facades\Route;

it('has more than 150 named routes', function () {
    $routes = collect(Route::getRoutes()->getRoutes());
    $named  = $routes->filter(fn($r) => $r->getName())->count();
    expect($named)->toBeGreaterThan(150);
});

it('has more than 50 migrations', function () {
    $migrations = glob(database_path('migrations/*.php'));
    expect(count($migrations))->toBeGreaterThan(50);
});

it('has more than 30 models', function () {
    $models = glob(app_path('Models/*.php'));
    expect(count($models))->toBeGreaterThan(30);
});

it('has more than 20 services', function () {
    $services = glob(app_path('Services/*.php'));
    expect(count($services))->toBeGreaterThan(20);
});

it('has more than 30 controllers', function () {
    $controllers = glob(app_path('Http/Controllers/*.php'));
    expect(count($controllers))->toBeGreaterThan(30);
});

it('has pdf templates config', function () {
    $templates = config('pdf_templates', []);
    expect($templates)->not->toBeEmpty();
    expect(count($templates))->toBeGreaterThan(10);
});

it('has at least 5 languages', function () {
    $langs = glob(base_path('lang/*/'));
    expect(count($langs))->toBeGreaterThanOrEqual(5);
});

it('health route exists and returns 200', function () {
    expect(Route::has('health'))->toBeTrue();
    $response = $this->get('/health');
    $response->assertStatus(200);
});

it('api v1 routes exist', function () {
    $routes = collect(Route::getRoutes()->getRoutes());
    $apiRoutes = $routes->filter(fn($r) => str_starts_with($r->uri(), 'api/'));
    expect($apiRoutes->count())->toBeGreaterThan(0);
});

it('all required route files are included in web php', function () {
    $webPhp = file_get_contents(base_path('routes/web.php'));

    $required = [
        'hr.php',
        'approval.php',
        'email-tracking.php',
        'loyalty.php',
        'forecasting.php',
        'archive.php',
        'accounting-export.php',
        'oss-vat.php',
        'pos-reports.php',
        'special-labels.php',
        'project-milestones.php',
        'public-products.php',
        'auto-reorder.php',
        'push.php',
        'barcode.php',
        'mobile-money.php',
        'monitoring.php',
    ];

    foreach ($required as $file) {
        expect($webPhp)->toContain($file);
    }
});
