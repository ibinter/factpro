<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Models\PaymentAuditLog;
use App\Notifications\LicenseGracePeriod;
use Illuminate\Console\Command;

class LicensesApplyGracePeriod extends Command
{
    protected $signature = 'licenses:apply-grace-period';

    protected $description = 'Place en période de tolérance les licences payantes actives arrivées à échéance';

    public function handle(): int
    {
        $graceDays = (int) config('factpro.license.grace_period_days', 7);

        $licenses = License::query()
            ->where('type', 'paid')
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->whereNull('grace_period_ends_at')
            ->with('user')
            ->get();

        if ($licenses->isEmpty()) {
            $this->info('Aucune licence à placer en période de tolérance.');

            return self::SUCCESS;
        }

        $count = 0;

        foreach ($licenses as $license) {
            $old = [
                'status' => $license->status,
                'grace_period_ends_at' => optional($license->grace_period_ends_at)->toDateTimeString(),
            ];

            $graceEndsAt = $license->ends_at->copy()->addDays($graceDays);

            $license->update([
                'status' => 'grace_period',
                'grace_period_ends_at' => $graceEndsAt,
            ]);

            PaymentAuditLog::record(
                'license_grace_period_applied',
                'license',
                (string) $license->id,
                $old,
                [
                    'status' => 'grace_period',
                    'grace_period_ends_at' => $graceEndsAt->toDateTimeString(),
                ],
                "Licence échue : période de tolérance de {$graceDays} jour(s) appliquée automatiquement (scheduler)",
            );

            $license->user?->notify(new LicenseGracePeriod($license));
            $count++;
        }

        $this->info("{$count} licence(s) placée(s) en période de tolérance.");

        return self::SUCCESS;
    }
}
