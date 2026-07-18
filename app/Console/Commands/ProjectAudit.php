<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProjectAudit extends Command
{
    protected $signature = 'project:audit {--json : Sortie JSON}';
    protected $description = 'Audit complet du projet FactPro';

    public function handle(): int
    {
        $results = [];

        // 1. Routes (compter toutes les routes nommées)
        $routes = collect(\Route::getRoutes()->getRoutes());
        $results['routes'] = [
            'total' => $routes->count(),
            'named' => $routes->filter(fn($r) => $r->getName())->count(),
            'api'   => $routes->filter(fn($r) => str_starts_with($r->uri(), 'api/'))->count(),
        ];

        // 2. Migrations
        $migrations = glob(database_path('migrations/*.php'));
        $results['migrations'] = count($migrations);

        // 3. Modèles
        $models = glob(app_path('Models/*.php'));
        $results['models'] = count($models);

        // 4. Services
        $services = glob(app_path('Services/*.php'));
        $results['services'] = count($services);

        // 5. Controllers
        $controllers = glob(app_path('Http/Controllers/*.php'));
        $results['controllers'] = count($controllers);

        // 6. Templates PDF
        $templates = count(config('pdf_templates', []));
        $results['pdf_templates'] = $templates;

        // 7. Vues Vue
        $vueFiles = glob(resource_path('js/Pages/**/*.vue'));
        $results['vue_pages'] = count($vueFiles);

        // 8. Tests
        $testFiles = glob(base_path('tests/Feature/**Test.php'));
        $results['test_files'] = count($testFiles);

        // 9. Langues
        $langs = glob(base_path('lang/*/'));
        $results['languages'] = count($langs);

        // 10. Fichiers de routes
        $routeFiles = glob(base_path('routes/*.php'));
        $results['route_files'] = count($routeFiles);

        if ($this->option('json')) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
        } else {
            $flat = [];
            foreach ($results as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subKey => $subValue) {
                        $flat[] = ["$key.$subKey", $subValue];
                    }
                } else {
                    $flat[] = [$key, $value];
                }
            }
            $this->table(['Indicateur', 'Valeur'], $flat);
        }

        return 0;
    }
}
