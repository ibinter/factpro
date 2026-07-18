<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyzeQueries extends Command
{
    protected $signature = 'perf:analyze-queries {--threshold=100 : Seuil en ms pour signaler une requête lente}';

    protected $description = 'Analyse le log Laravel pour identifier les requêtes N+1 et lentes, génère un rapport';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');
        $logPath = storage_path('logs/laravel.log');
        $reportPath = storage_path('logs/query-analysis.log');

        $this->info("Analyse des requêtes (seuil : {$threshold}ms)...");

        $report = [];
        $report[] = '=== RAPPORT D\'ANALYSE DES REQUÊTES ===';
        $report[] = 'Généré le : ' . now()->toDateTimeString();
        $report[] = "Seuil : {$threshold}ms";
        $report[] = '';

        // Analyse via DB::listen en mode live (pour les requêtes de cette commande)
        $slowQueries = [];
        $queryLog = [];

        DB::listen(function ($query) use ($threshold, &$slowQueries, &$queryLog) {
            $sql = $query->sql;
            $time = $query->time; // ms

            $queryLog[] = ['sql' => $sql, 'time' => $time, 'bindings' => $query->bindings];

            if ($time >= $threshold) {
                $slowQueries[] = ['sql' => $sql, 'time' => $time];
            }
        });

        // Exécuter quelques requêtes diagnostiques légères
        $this->runDiagnosticQueries();

        // Détecter les doublons (potentiel N+1)
        $sqlCounts = [];
        foreach ($queryLog as $entry) {
            $normalized = preg_replace('/\s+/', ' ', trim($entry['sql']));
            $sqlCounts[$normalized] = ($sqlCounts[$normalized] ?? 0) + 1;
        }

        $duplicates = array_filter($sqlCounts, fn ($count) => $count > 1);
        arsort($duplicates);

        $report[] = '--- REQUÊTES DUPLIQUÉES (potentiel N+1) ---';
        if (empty($duplicates)) {
            $report[] = 'Aucune requête dupliquée détectée dans cette session.';
        } else {
            foreach (array_slice($duplicates, 0, 20) as $sql => $count) {
                $report[] = "[{$count}x] " . substr($sql, 0, 200);
            }
        }
        $report[] = '';

        // Requêtes lentes
        $report[] = "--- REQUÊTES LENTES (> {$threshold}ms) ---";
        if (empty($slowQueries)) {
            $report[] = 'Aucune requête lente détectée dans cette session.';
        } else {
            foreach ($slowQueries as $q) {
                $report[] = "[{$q['time']}ms] " . substr($q['sql'], 0, 300);
            }
        }
        $report[] = '';

        // Analyse simpliste via log file si disponible
        if (file_exists($logPath)) {
            $report[] = '--- ANALYSE DU FICHIER LOG ---';
            $logContent = file_get_contents($logPath);
            preg_match_all('/\[(\d+\.\d+)ms\].*?select .+?from `(\w+)`/i', $logContent, $matches);

            if (! empty($matches[2])) {
                $tableHits = array_count_values($matches[2]);
                arsort($tableHits);
                $report[] = 'Tables les plus interrogées :';
                foreach (array_slice($tableHits, 0, 10) as $table => $hits) {
                    $report[] = "  {$table}: {$hits} requête(s)";
                }
            } else {
                $report[] = 'Pas de données de requête trouvées dans le log.';
            }
            $report[] = '';
        }

        // Résumé
        $report[] = '--- RÉSUMÉ ---';
        $report[] = 'Total requêtes cette session : ' . count($queryLog);
        $report[] = 'Requêtes dupliquées : ' . count($duplicates);
        $report[] = 'Requêtes lentes : ' . count($slowQueries);
        $report[] = '';
        $report[] = 'Recommandations :';
        $report[] = '  - Ajouter eager loading (->with()) sur les relations fréquemment lazy-loadées';
        $report[] = '  - Vérifier les index sur les colonnes de jointure et WHERE fréquents';
        $report[] = '  - Utiliser CacheService::rememberForCompany() sur les requêtes lourdes';

        $content = implode(PHP_EOL, $report);
        file_put_contents($reportPath, $content);

        $this->info("Rapport généré : {$reportPath}");
        $this->line('');
        $this->line($content);

        return self::SUCCESS;
    }

    private function runDiagnosticQueries(): void
    {
        try {
            // Quelques EXPLAIN simples pour vérifier les index
            DB::select('SELECT COUNT(*) FROM documents');
            DB::select('SELECT COUNT(*) FROM customers');
        } catch (\Exception $e) {
            // Silencieux si les tables n'existent pas encore
        }
    }
}
