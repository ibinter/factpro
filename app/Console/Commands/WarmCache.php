<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\CacheService;
use Illuminate\Console\Command;

class WarmCache extends Command
{
    protected $signature = 'cache:warm {--company= : ID de la company à chauffer} {--all : Chauffer toutes les companies}';

    protected $description = 'Pré-chauffe le cache pour une company ou toutes (dashboard, taux de change, plans)';

    public function handle(): int
    {
        if ($this->option('all')) {
            $companies = Company::all();
            $this->info("Chauffage du cache pour {$companies->count()} company(ies)...");
            foreach ($companies as $company) {
                $this->warmForCompany($company);
            }
        } elseif ($companyId = $this->option('company')) {
            $company = Company::find($companyId);
            if (! $company) {
                $this->error("Company #{$companyId} introuvable.");
                return self::FAILURE;
            }
            $this->warmForCompany($company);
        } else {
            $this->error('Précisez --company=ID ou --all.');
            return self::FAILURE;
        }

        $this->info('Cache pré-chargé avec succès.');
        return self::SUCCESS;
    }

    private function warmForCompany(Company $company): void
    {
        $this->line("  Chauffage company #{$company->id} ({$company->name})...");

        // CA mensuel sur 12 mois
        CacheService::rememberForCompany(
            $company->id,
            'monthly_revenue',
            CacheService::TTL_DASHBOARD,
            fn () => $this->buildMonthlyRevenue($company)
        );

        // Top clients
        CacheService::rememberForCompany(
            $company->id,
            'top_customers',
            CacheService::TTL_DASHBOARD,
            fn () => $this->buildTopCustomers($company)
        );

        // Top produits
        CacheService::rememberForCompany(
            $company->id,
            'top_products',
            CacheService::TTL_DASHBOARD,
            fn () => $this->buildTopProducts($company)
        );

        // Plans tarifaires (global)
        CacheService::rememberGlobal(
            'pricing_plans',
            CacheService::TTL_PLANS,
            fn () => \App\Models\Plan::orderBy('sort_order')->get()->toArray()
        );

        $this->line("    OK");
    }

    private function buildMonthlyRevenue(Company $company): array
    {
        return collect(range(11, 0))->map(function ($i) use ($company) {
            $month = now()->subMonths($i);
            $query = \App\Models\Document::where('company_id', $company->id)
                ->where('type', 'invoice')
                ->whereNotIn('status', ['cancelled', 'draft'])
                ->whereYear('issue_date', $month->year)
                ->whereMonth('issue_date', $month->month);
            return [
                'month' => $month->translatedFormat('M Y'),
                'revenue' => (float) $query->sum('total'),
                'invoices_count' => $query->count(),
            ];
        })->values()->all();
    }

    private function buildTopCustomers(Company $company): array
    {
        $since = now()->subMonths(12);
        return \App\Models\Document::where('documents.company_id', $company->id)
            ->where('documents.type', 'invoice')
            ->whereNotIn('documents.status', ['cancelled', 'draft'])
            ->where('documents.issue_date', '>=', $since)
            ->whereNotNull('documents.customer_id')
            ->join('customers', 'customers.id', '=', 'documents.customer_id')
            ->selectRaw('customers.name, SUM(documents.total) as total, COUNT(documents.id) as invoices_count')
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'total' => (float) $row->total,
                'invoices_count' => (int) $row->invoices_count,
            ])
            ->all();
    }

    private function buildTopProducts(Company $company): array
    {
        $since = now()->subMonths(12);
        return \DB::table('document_lines')
            ->join('documents', 'documents.id', '=', 'document_lines.document_id')
            ->where('documents.company_id', $company->id)
            ->where('documents.type', 'invoice')
            ->whereNotIn('documents.status', ['cancelled', 'draft'])
            ->where('documents.issue_date', '>=', $since)
            ->whereNull('documents.deleted_at')
            ->selectRaw('document_lines.description as name, SUM(document_lines.quantity) as quantity, SUM(document_lines.line_total) as revenue')
            ->groupBy('document_lines.description')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'quantity' => (float) $row->quantity,
                'revenue' => (float) $row->revenue,
            ])
            ->all();
    }
}
