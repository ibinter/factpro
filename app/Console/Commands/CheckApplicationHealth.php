<?php

namespace App\Console\Commands;

use App\Services\MonitoringService;
use Illuminate\Console\Command;

class CheckApplicationHealth extends Command
{
    protected $signature = 'app:health-check {--alert : Envoyer une alerte si dégradé}';

    protected $description = "Vérifie la santé de l'application";

    public function handle(MonitoringService $monitoring): int
    {
        $health = $monitoring->checkHealth();

        $this->info("Statut : {$health['status']}");

        foreach ($health['checks'] as $name => $check) {
            $icon = $check['status'] === 'ok' ? '✅' : '❌';
            $this->line("  $icon $name : {$check['status']}");
        }

        if ($this->option('alert') && $health['status'] !== 'healthy') {
            $monitoring->captureMessage(
                "FactPro health check dégradé : {$health['status']}",
                'warning',
                $health['checks']
            );
        }

        return $health['status'] === 'healthy' ? self::SUCCESS : self::FAILURE;
    }
}
