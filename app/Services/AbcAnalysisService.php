<?php

namespace App\Services;

use App\Models\DocumentLine;
use Illuminate\Support\Facades\DB;

class AbcAnalysisService
{
    /**
     * Calcule la classification ABC pour tous les produits d'une company.
     * Basé sur les ventes des N derniers mois (factures finalisées).
     */
    public function analyze(int $companyId, int $months = 12): array
    {
        $since = now()->subMonths($months)->startOfDay();

        // CA par produit sur la période (factures & tickets de caisse finalisés)
        $revenues = DB::table('document_lines')
            ->join('documents', 'document_lines.document_id', '=', 'documents.id')
            ->join('products', 'document_lines.product_id', '=', 'products.id')
            ->where('documents.company_id', $companyId)
            ->whereIn('documents.type', ['invoice', 'pos_ticket'])
            ->whereNotNull('documents.finalized_at')
            ->where('documents.issue_date', '>=', $since->toDateString())
            ->whereNull('documents.deleted_at')
            ->whereNull('products.deleted_at')
            ->whereNotNull('document_lines.product_id')
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'products.sku as product_sku',
                'products.stock_quantity',
                DB::raw('SUM(document_lines.line_total) as revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.stock_quantity')
            ->orderByDesc('revenue')
            ->get();

        // Ajouter les produits sans ventes (classe C par défaut)
        $allProducts = DB::table('products')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->select('id as product_id', 'name as product_name', 'sku as product_sku', 'stock_quantity')
            ->get()
            ->keyBy('product_id');

        // Indexer les produits avec revenus
        $revenueMap = $revenues->keyBy('product_id');

        // Construire la liste complète triée par CA décroissant
        $products = [];
        foreach ($revenues as $row) {
            $products[] = [
                'product_id' => $row->product_id,
                'product_name' => $row->product_name,
                'product_sku' => $row->product_sku,
                'stock_quantity' => (float) $row->stock_quantity,
                'revenue' => (float) $row->revenue,
            ];
        }

        // Produits sans vente
        foreach ($allProducts as $id => $p) {
            if (! $revenueMap->has($id)) {
                $products[] = [
                    'product_id' => $p->product_id,
                    'product_name' => $p->product_name,
                    'product_sku' => $p->product_sku,
                    'stock_quantity' => (float) $p->stock_quantity,
                    'revenue' => 0.0,
                ];
            }
        }

        $totalRevenue = array_sum(array_column($products, 'revenue'));

        // Calcul % cumulé et classification
        $cumulative = 0.0;
        $result = [];

        foreach ($products as $p) {
            $pct = $totalRevenue > 0 ? ($p['revenue'] / $totalRevenue) * 100 : 0;
            $cumulativeBefore = $cumulative;
            $cumulative += $pct;

            // Déterminer la classe en fonction du % cumulé AVANT ce produit
            if ($totalRevenue <= 0 || $p['revenue'] <= 0) {
                $class = 'C';
            } elseif ($cumulativeBefore < 80) {
                $class = 'A';
            } elseif ($cumulativeBefore < 95) {
                $class = 'B';
            } else {
                $class = 'C';
            }

            $result[] = array_merge($p, [
                'revenue_pct' => round($pct, 2),
                'cumulative_pct' => round($cumulative, 2),
                'class' => $class,
                'recommendations' => $this->getRecommendations($class),
            ]);
        }

        // Résumé par classe
        $summary = ['A' => ['count' => 0, 'revenue' => 0.0, 'revenue_pct' => 80],
                     'B' => ['count' => 0, 'revenue' => 0.0, 'revenue_pct' => 15],
                     'C' => ['count' => 0, 'revenue' => 0.0, 'revenue_pct' => 5]];

        foreach ($result as $item) {
            $c = $item['class'];
            $summary[$c]['count']++;
            $summary[$c]['revenue'] += $item['revenue'];
        }

        return [
            'products' => $result,
            'summary' => $summary,
            'period_months' => $months,
            'total_revenue' => $totalRevenue,
        ];
    }

    /** Retourne les recommandations par classe. */
    public function getRecommendations(string $class): array
    {
        return match ($class) {
            'A' => [
                'Gestion rigoureuse du stock',
                'Réapprovisionnement fréquent',
                'Prix compétitif',
            ],
            'B' => [
                'Stock modéré',
                'Réapprovisionnement mensuel',
            ],
            'C' => [
                'Stock minimal',
                'Envisager le déréférencement si >6 mois sans vente',
            ],
            default => [],
        };
    }
}
