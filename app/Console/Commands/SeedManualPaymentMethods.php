<?php

namespace App\Console\Commands;

use App\Services\ManualPaymentMethodService;
use Illuminate\Console\Command;

class SeedManualPaymentMethods extends Command
{
    protected $signature   = 'payment:seed-manual-methods';
    protected $description = 'Crée les méthodes de paiement manuel par défaut (Mobile Money Afrique + services de transfert)';

    public function handle(ManualPaymentMethodService $service): int
    {
        $this->info('Initialisation des méthodes de paiement manuel…');
        $service->seedDefaults();
        $this->info('Méthodes de paiement manuel par défaut créées (is_active = false).');
        $this->line('Activez-les depuis le panneau superadmin > Paiements > Méthodes manuelles.');
        return Command::SUCCESS;
    }
}
