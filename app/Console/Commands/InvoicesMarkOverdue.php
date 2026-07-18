<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;

/**
 * Passage automatique en `overdue` des factures échues impayées
 * (module Relances intelligentes — cahier des charges §13).
 */
class InvoicesMarkOverdue extends Command
{
    protected $signature = 'invoices:mark-overdue';

    protected $description = 'Passe en statut "overdue" les factures échues non soldées (sent/viewed/partial)';

    public function handle(): int
    {
        $count = Document::query()
            ->where('type', 'invoice')
            ->whereIn('status', ['sent', 'viewed', 'partial'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today())
            ->whereColumn('total', '>', 'amount_paid')
            ->update(['status' => 'overdue']);

        $this->info($count === 0
            ? 'Aucune facture à passer en retard.'
            : "{$count} facture(s) passée(s) en retard (overdue).");

        return self::SUCCESS;
    }
}
