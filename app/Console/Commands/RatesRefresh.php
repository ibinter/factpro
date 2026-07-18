<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

/**
 * Rafraîchit les taux de change (cahier IBIG §3 DEV / §14).
 * Interroge l'API publique ; bascule automatiquement sur les taux de repli
 * si l'API est indisponible.
 */
class RatesRefresh extends Command
{
    protected $signature = 'rates:refresh {base=XOF : Devise de base}';

    protected $description = 'Rafraîchit les taux de change depuis l\'API publique (repli taux fixes)';

    public function handle(ExchangeRateService $service): int
    {
        $base = strtoupper($this->argument('base'));

        $count = $service->refresh($base);
        $freshness = $service->freshness();

        $this->info("Taux rafraîchis pour {$base} : {$count} devise(s) enregistrée(s)."
            .($freshness ? ' Dernier fetch : '.$freshness->format('d/m/Y H:i').'.' : ''));

        return self::SUCCESS;
    }
}
