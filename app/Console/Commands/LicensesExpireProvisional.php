<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Models\PaymentAuditLog;
use App\Notifications\LicenseSuspended;
use Illuminate\Console\Command;

class LicensesExpireProvisional extends Command
{
    protected $signature = 'licenses:expire-provisional';

    protected $description = 'Suspend les licences provisoires arrivées à échéance sans validation du paiement';

    public function handle(): int
    {
        $licenses = License::query()
            ->where('type', 'provisional')
            ->where('status', 'provisional')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->with('user')
            ->get();

        if ($licenses->isEmpty()) {
            $this->info('Aucune licence provisoire à traiter.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($licenses as $license) {
            $oldStatus = $license->status;
            $license->update(['status' => 'suspended']);

            PaymentAuditLog::record(
                'provisional_license_expired',
                'license',
                (string) $license->id,
                ['status' => $oldStatus],
                ['status' => 'suspended'],
                'Licence provisoire échue sans validation du paiement : suspension automatique (scheduler)',
            );

            $license->user?->notify(new LicenseSuspended($license));
            $count++;
        }

        $this->info("{$count} licence(s) provisoire(s) suspendue(s).");

        return self::SUCCESS;
    }
}
