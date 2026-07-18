<?php

namespace App\Console\Commands;

use App\Services\RecurringService;
use Illuminate\Console\Command;

/**
 * Génération planifiée des factures récurrentes (abonnements — cahier §3).
 * Exécutée chaque matin à 06:00 (voir routes/console.php).
 */
class InvoicesGenerateRecurring extends Command
{
    protected $signature = 'invoices:generate-recurring';

    protected $description = 'Génère les factures récurrentes arrivées à échéance (abonnements automatiques)';

    public function handle(RecurringService $recurring): int
    {
        $count = $recurring->runDue();

        $this->info("{$count} facture(s) récurrente(s) générée(s).");

        return self::SUCCESS;
    }
}
