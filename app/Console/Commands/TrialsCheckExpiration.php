<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Models\PaymentAuditLog;
use App\Notifications\TrialExpired;
use App\Notifications\TrialExpiryReminder;
use Illuminate\Console\Command;

class TrialsCheckExpiration extends Command
{
    protected $signature = 'trials:check-expiration';

    protected $description = "Expire les licences d'essai échues et envoie les rappels J-7 / J-3 / J-1";

    public function handle(): int
    {
        $reminders = $this->sendReminders();
        $expired = $this->expireTrials();

        if ($reminders === 0 && $expired === 0) {
            $this->info("Aucune licence d'essai à traiter.");
        } else {
            $this->info("{$reminders} rappel(s) envoyé(s), {$expired} licence(s) d'essai expirée(s).");
        }

        return self::SUCCESS;
    }

    private function sendReminders(): int
    {
        $sentCount = 0;

        foreach ([7, 3, 1] as $days) {
            $marker = 'J-' . $days;

            $licenses = License::query()
                ->where('type', 'trial')
                ->where('status', 'trial')
                ->whereDate('trial_ends_at', now()->addDays($days)->toDateString())
                ->with('user')
                ->get();

            foreach ($licenses as $license) {
                $metadata = $license->metadata ?? [];
                $alreadySent = $metadata['reminders_sent'] ?? [];

                if (in_array($marker, $alreadySent, true)) {
                    continue; // rappel déjà envoyé pour cette échéance
                }

                // Marqueur d'idempotence enregistré AVANT l'envoi
                $alreadySent[] = $marker;
                $metadata['reminders_sent'] = $alreadySent;
                $license->update(['metadata' => $metadata]);

                $license->user?->notify(new TrialExpiryReminder($license, $days));
                $sentCount++;
            }
        }

        return $sentCount;
    }

    private function expireTrials(): int
    {
        $licenses = License::query()
            ->where('type', 'trial')
            ->where('status', 'trial')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->with('user')
            ->get();

        $count = 0;

        foreach ($licenses as $license) {
            $oldStatus = $license->status;
            $license->update(['status' => 'expired']);

            PaymentAuditLog::record(
                'trial_expired',
                'license',
                (string) $license->id,
                ['status' => $oldStatus],
                ['status' => 'expired'],
                "Expiration automatique de la période d'essai (scheduler)",
            );

            $license->user?->notify(new TrialExpired($license));
            $count++;
        }

        return $count;
    }
}
