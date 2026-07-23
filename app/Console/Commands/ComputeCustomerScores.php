<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\CustomerScoringService;
use Illuminate\Console\Command;

class ComputeCustomerScores extends Command
{
    protected $signature   = 'scoring:customers {--company=}';
    protected $description = 'Calcule les scores risque et churn pour tous les clients';

    public function handle(CustomerScoringService $service): void
    {
        $query = Company::query();
        if ($this->option('company')) {
            $query->where('id', $this->option('company'));
        }

        $companies = $query->get();
        foreach ($companies as $company) {
            $this->info("Scoring {$company->name}...");
            $service->computeForCompany($company);
        }
        $this->info('Scoring terminé.');
    }
}
