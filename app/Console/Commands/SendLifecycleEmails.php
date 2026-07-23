<?php

namespace App\Console\Commands;

use App\Mail\LicenseExpired;
use App\Mail\LicenseExpiringSoon;
use App\Mail\TrialEndingMail;
use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendLifecycleEmails extends Command
{
    protected $signature = 'emails:lifecycle';

    protected $description = 'Envoie les e-mails de cycle de vie : fin d\'essai, expiration de licence';

    public function handle(): int
    {
        $this->sendTrialEndingReminders();
        $this->sendLicenseExpiryReminders();
        $this->sendLicenseExpiredNotices();

        $this->info('Lifecycle emails dispatched successfully.');

        return self::SUCCESS;
    }

    private function sendTrialEndingReminders(): void
    {
        foreach ([3, 1] as $daysLeft) {
            $targetDate = now()->addDays($daysLeft)->toDateString();

            License::query()
                ->where('status', 'trial')
                ->whereDate('trial_ends_at', $targetDate)
                ->with('user')
                ->each(function (License $license) use ($daysLeft) {
                    if (! $license->user) {
                        return;
                    }

                    Mail::to($license->user->email)
                        ->queue(new TrialEndingMail($license->user, $license, $daysLeft));

                    $this->line("  TrialEndingMail (J-{$daysLeft}) → {$license->user->email}");
                });
        }
    }

    private function sendLicenseExpiryReminders(): void
    {
        foreach ([7, 1] as $daysLeft) {
            $targetDate = now()->addDays($daysLeft)->toDateString();

            License::query()
                ->whereIn('status', ['active', 'provisional'])
                ->whereDate('ends_at', $targetDate)
                ->with('user')
                ->each(function (License $license) use ($daysLeft) {
                    if (! $license->user) {
                        return;
                    }

                    Mail::to($license->user->email)
                        ->queue(new LicenseExpiringSoon($license, $daysLeft));

                    $this->line("  LicenseExpiringSoon (J-{$daysLeft}) → {$license->user->email}");
                });
        }
    }

    private function sendLicenseExpiredNotices(): void
    {
        $yesterday = now()->subDay()->toDateString();

        License::query()
            ->whereIn('status', ['expired', 'grace_period'])
            ->whereDate('ends_at', $yesterday)
            ->with('user')
            ->each(function (License $license) {
                if (! $license->user) {
                    return;
                }

                Mail::to($license->user->email)
                    ->queue(new LicenseExpired($license));

                $this->line("  LicenseExpired → {$license->user->email}");
            });
    }
}
