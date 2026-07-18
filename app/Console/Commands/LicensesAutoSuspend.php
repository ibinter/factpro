<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Models\PaymentAuditLog;
use App\Notifications\LicenseSuspended;
use Illuminate\Console\Command;

class LicensesAutoSuspend extends Command
{
    protected $signature = 'licenses:auto-suspend';

    protected $description = 'Suspend les licences dont la période de tolérance est terminée';

    public function handle(): int
    {
        $licenses = License::query()
            ->where('status', 'grace_period')
            ->whereNotNull('grace_period_ends_at')
            ->where('grace_period_ends_at', '<', now())
            ->with('user')
            ->get();

        if ($licenses->isEmpty()) {
            $this->info('Aucune licence à suspendre.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($licenses as $license) {
            $oldStatus = $license->status;
            $license->update(['status' => 'suspended']);

            PaymentAuditLog::record(
                'license_auto_suspended',
                'license',
                (string) $license->id,
                ['status' => $oldStatus],
                ['status' => 'suspended'],
                'Suspension automatique : période de tolérance terminée sans renouvellement (scheduler)',
            );

            $license->user?->notify(new LicenseSuspended($license));
            $count++;
        }

        $this->info("{$count} licence(s) suspendue(s).");

        return self::SUCCESS;
    }
}
