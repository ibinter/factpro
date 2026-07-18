<?php

use Illuminate\Support\Facades\File;

/**
 * Tests de la Collection Centenniale — Templates PDF IBIG FactPro
 *
 * Valide l'état final après ajout des 4 templates de la collection centenniale :
 * corporate-diamond, eco-nature, digital-neon, africa-kente.
 */

it('has 4 new centennial templates in config', function () {
    $templates = config('pdf_templates');
    expect($templates)->toHaveKey('corporate-diamond');
    expect($templates)->toHaveKey('eco-nature');
    expect($templates)->toHaveKey('digital-neon');
    expect($templates)->toHaveKey('africa-kente');
});

it('corporate_diamond template view exists', function () {
    $viewPath = resource_path('views/pdf/templates/corporate-diamond.blade.php');
    expect(File::exists($viewPath))->toBeTrue("Le fichier blade corporate-diamond doit exister.");
});

it('eco_nature template view exists', function () {
    $viewPath = resource_path('views/pdf/templates/eco-nature.blade.php');
    expect(File::exists($viewPath))->toBeTrue("Le fichier blade eco-nature doit exister.");
});

it('digital_neon template view exists', function () {
    $viewPath = resource_path('views/pdf/templates/digital-neon.blade.php');
    expect(File::exists($viewPath))->toBeTrue("Le fichier blade digital-neon doit exister.");
});

it('africa_kente template view exists', function () {
    $viewPath = resource_path('views/pdf/templates/africa-kente.blade.php');
    expect(File::exists($viewPath))->toBeTrue("Le fichier blade africa-kente doit exister.");
});

it('africa_kente is the last template in config', function () {
    $templates = config('pdf_templates');
    $slugs = array_keys($templates);
    expect(end($slugs))->toBe('africa-kente');
});

it('no duplicate slugs in config', function () {
    $templates = config('pdf_templates');
    $slugs = array_keys($templates);
    expect(count($slugs))->toBe(count(array_unique($slugs)));
});

it('all templates have required fields', function () {
    $templates = config('pdf_templates');
    $required = ['name', 'family', 'description', 'primary', 'secondary', 'accent'];
    $missing = [];

    foreach ($templates as $slug => $template) {
        foreach ($required as $field) {
            if (! array_key_exists($field, $template)) {
                $missing[] = "{$slug}.{$field}";
            }
        }
    }

    expect($missing)->toBeEmpty(
        'Champs manquants : ' . implode(', ', $missing)
    );
});

it('all templates have existing view files', function () {
    $templates = config('pdf_templates');
    $missing = [];

    foreach ($templates as $slug => $template) {
        $viewPath = resource_path("views/pdf/templates/{$slug}.blade.php");
        if (! File::exists($viewPath)) {
            $missing[] = $slug;
        }
    }

    expect($missing)->toBeEmpty(
        "Les templates suivants n'ont pas de fichier blade : " . implode(', ', $missing)
    );
});

it('corporate_diamond belongs to Corporate B2B family', function () {
    $template = config('pdf_templates.corporate-diamond');
    expect($template['family'])->toBe('Corporate B2B');
});

it('eco_nature belongs to Naturel Bio family', function () {
    $template = config('pdf_templates.eco-nature');
    expect($template['family'])->toBe('Naturel & Bio');
});

it('digital_neon belongs to Futuriste Tech family', function () {
    $template = config('pdf_templates.digital-neon');
    expect($template['family'])->toBe('Futuriste & Tech');
});

it('africa_kente belongs to Afrique Export family', function () {
    $template = config('pdf_templates.africa-kente');
    expect($template['family'])->toBe('Afrique & Export');
});

it('africa_kente blade contains kente SVG pattern', function () {
    $viewPath = resource_path('views/pdf/templates/africa-kente.blade.php');
    $content = File::get($viewPath);
    expect($content)->toContain('kente');
    expect($content)->toContain('<svg');
    expect($content)->toContain('polygon');
});

it('digital_neon blade uses dark background', function () {
    $viewPath = resource_path('views/pdf/templates/digital-neon.blade.php');
    $content = File::get($viewPath);
    expect($content)->toContain('0D0D1A');
    expect($content)->toContain('00F5FF');
});

it('corporate_diamond blade contains gold accent', function () {
    $viewPath = resource_path('views/pdf/templates/corporate-diamond.blade.php');
    $content = File::get($viewPath);
    expect($content)->toContain('F0C040');
    expect($content)->toContain('1a1a1a');
});

it('eco_nature blade contains green palette', function () {
    $viewPath = resource_path('views/pdf/templates/eco-nature.blade.php');
    $content = File::get($viewPath);
    expect($content)->toContain('2D6A4F');
    expect($content)->toContain('40916C');
});

it('config has at least 81 templates total', function () {
    $templates = config('pdf_templates');
    expect(count($templates))->toBeGreaterThanOrEqual(81);
});
