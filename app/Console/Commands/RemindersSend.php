<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\ReminderService;
use Illuminate\Console\Command;

/**
 * Relances automatiques des factures en retard : escalade J+3 / J+7 / J+15
 * (courtois → ferme → mise en demeure). Cahier des charges §13.
 */
class RemindersSend extends Command
{
    protected $signature = 'reminders:send';

    protected $description = "Envoie les relances email automatiques des factures en retard (escalade 3 niveaux)";

    public function handle(ReminderService $reminders): int
    {
        $totalSent = 0;

        // Seules les sociétés ayant au moins une facture en retard sont parcourues
        $companies = Company::query()
            ->whereHas('documents', fn ($q) => $q
                ->where('type', 'invoice')
                ->whereIn('status', ReminderService::ACTIVE_STATUSES)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
                ->whereColumn('total', '>', 'amount_paid'))
            ->get();

        foreach ($companies as $company) {
            // Relances activées par défaut ; désactivables via settings['reminders']['enabled'] = false
            if (! $reminders->isEnabled($company)) {
                continue;
            }

            $totalSent += $reminders->runAuto($company);
        }

        $this->info($totalSent === 0
            ? 'Aucune relance à envoyer.'
            : "{$totalSent} relance(s) email envoyée(s).");

        return self::SUCCESS;
    }
}
