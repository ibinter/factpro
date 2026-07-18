<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

uses(RefreshDatabase::class);

it('sets locale to portuguese', function () {
    $response = $this->withSession(['locale' => 'pt'])->get('/');
    expect(App::getLocale())->toBe('pt');
});

it('sets locale to spanish', function () {
    $response = $this->withSession(['locale' => 'es'])->get('/');
    expect(App::getLocale())->toBe('es');
});

it('portuguese translations file exists', function () {
    expect(file_exists(lang_path('pt/ui.php')))->toBeTrue();
    expect(file_exists(lang_path('pt/documents.php')))->toBeTrue();
});

it('spanish translations file exists', function () {
    expect(file_exists(lang_path('es/ui.php')))->toBeTrue();
    expect(file_exists(lang_path('es/documents.php')))->toBeTrue();
});

it('portuguese auth messages are translated', function () {
    App::setLocale('pt');
    $translations = require lang_path('pt/ui.php');

    expect($translations)->toBeArray();
    expect($translations['dashboard'])->toBe('Painel');
    expect($translations['invoices'])->toBe('Faturas');
    expect($translations['customers'])->toBe('Clientes');
    expect($translations['save'])->toBe('Guardar');
    expect($translations['cancel'])->toBe('Cancelar');
});

it('spanish auth messages are translated', function () {
    App::setLocale('es');
    $translations = require lang_path('es/ui.php');

    expect($translations)->toBeArray();
    expect($translations['dashboard'])->toBe('Panel');
    expect($translations['invoices'])->toBe('Facturas');
    expect($translations['customers'])->toBe('Clientes');
    expect($translations['save'])->toBe('Guardar');
    expect($translations['delete'])->toBe('Eliminar');
});

it('language switcher includes portuguese option', function () {
    $content = file_get_contents(resource_path('js/Components/LanguageSelector.vue'));

    expect($content)->toContain("code: 'pt'");
    expect($content)->toContain('Português');
    expect($content)->toContain('🇦🇴');
});

it('language switcher includes spanish option', function () {
    $content = file_get_contents(resource_path('js/Components/LanguageSelector.vue'));

    expect($content)->toContain("code: 'es'");
    expect($content)->toContain('Español');
    expect($content)->toContain('🇪🇸');
});

it('available locales config includes pt and es', function () {
    $locales = Config::get('app.available_locales');

    expect($locales)->toContain('pt');
    expect($locales)->toContain('es');
    expect($locales)->toContain('fr');
    expect($locales)->toContain('en');
    expect($locales)->toContain('ar');
});

it('validation messages translated in portuguese', function () {
    $docs = require lang_path('pt/documents.php');
    $en   = require lang_path('en/documents.php');

    $missing = array_diff_key($en, $docs);
    expect($missing)->toBeEmpty('Portuguese documents.php is missing keys: ' . implode(', ', array_keys($missing)));
});

it('validation messages translated in spanish', function () {
    $docs = require lang_path('es/documents.php');
    $en   = require lang_path('en/documents.php');

    $missing = array_diff_key($en, $docs);
    expect($missing)->toBeEmpty('Spanish documents.php is missing keys: ' . implode(', ', array_keys($missing)));
});
