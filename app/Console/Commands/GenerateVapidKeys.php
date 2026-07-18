<?php

namespace App\Console\Commands;

use App\Services\PushNotificationService;
use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature   = 'push:generate-vapid';
    protected $description = 'Génère une paire de clés VAPID et les écrit dans .env';

    public function handle(): int
    {
        $this->info('Génération des clés VAPID…');

        $keys = PushNotificationService::generateVapidKeys();

        $envPath = base_path('.env');
        $env     = file_get_contents($envPath);

        // Remplace ou ajoute VAPID_PUBLIC_KEY
        if (str_contains($env, 'VAPID_PUBLIC_KEY=')) {
            $env = preg_replace('/^VAPID_PUBLIC_KEY=.*/m', 'VAPID_PUBLIC_KEY=' . $keys['public'], $env);
        } else {
            $env .= "\nVAPID_PUBLIC_KEY=" . $keys['public'];
        }

        // Remplace ou ajoute VAPID_PRIVATE_KEY
        if (str_contains($env, 'VAPID_PRIVATE_KEY=')) {
            $env = preg_replace('/^VAPID_PRIVATE_KEY=.*/m', 'VAPID_PRIVATE_KEY=' . $keys['private'], $env);
        } else {
            $env .= "\nVAPID_PRIVATE_KEY=" . $keys['private'];
        }

        file_put_contents($envPath, $env);

        $this->info('Clés VAPID écrites dans .env :');
        $this->line('  Public  : ' . $keys['public']);
        $this->line('  Private : (masquée pour la sécurité)');
        $this->newLine();
        $this->warn('Ajoutez VAPID_PUBLIC_KEY dans votre config front-end si nécessaire.');

        return self::SUCCESS;
    }
}
