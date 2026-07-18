<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Notifications\LicenseExpiryAlert;
use Illuminate\Console\Command;

class LicensesSendExpiryAlerts extends Command
{
    protected $signature = 'licenses:send-expiry-alerts';

    protected $description = "Envoie les alertes d'expiration J-7 / J-3 / J-1 pour les licences payantes actives";

    public function handle(): int
    {
        $sentCount = 0;

        foreach ([7, 3, 1] as $days) {
            $marker = 'J-' . $days;

            $licenses = License::query()
                ->where('type', 'paid')
                ->where('status', 'active')
                ->whereDate('ends_at', now()->addDays($days)->toDateString())
                ->with('user')
                ->get();

            foreach ($licenses as $license) {
                $metadata = $license->metadata ?? [];
                $alreadySent = $metadata['expiry_alerts_sent'] ?? [];

                if (in_array($marker, $alreadySent, true)) {
                    continue; // alerte déjà envoyée pour cette échéance
                }

                // Marqueur d'idempotence enregistré AVANT l'envoi
                $alreadySent[] = $marker;
                $metadata['expiry_alerts_sent'] = $alreadySent;
                $license->update(['metadata' => $metadata]);

                $license->user?->notify(new LicenseExpiryAlert($license, $days));
                $sentCount++;
            }
        }

        if ($sentCount === 0) {
            $this->info("Aucune alerte d'expiration à envoyer.");
        } else {
            $this->info("{$sentCount} alerte(s) d'expiration envoyée(s).");
        }

        return self::SUCCESS;
    }
}
