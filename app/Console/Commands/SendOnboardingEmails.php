<?php

namespace App\Console\Commands;

use App\Mail\Onboarding\DayOneMail;
use App\Mail\Onboarding\DayThreeMail;
use App\Mail\Onboarding\DaySevenMail;
use App\Mail\Onboarding\DayFourteenMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendOnboardingEmails extends Command
{
    protected $signature = 'emails:onboarding';

    protected $description = "Envoie les emails d'onboarding aux nouveaux utilisateurs (J+1, J+3, J+7, J+14)";

    public function handle(): void
    {
        $sequences = [
            1  => DayOneMail::class,
            3  => DayThreeMail::class,
            7  => DaySevenMail::class,
            14 => DayFourteenMail::class,
        ];

        foreach ($sequences as $days => $mailClass) {
            $start = now()->subDays($days)->startOfDay();
            $end   = now()->subDays($days)->endOfDay();

            User::whereBetween('created_at', [$start, $end])
                ->whereHas('licenses', fn ($q) => $q->where('status', 'trial'))
                ->chunk(50, function ($users) use ($mailClass) {
                    foreach ($users as $user) {
                        Mail::to($user->email)->queue(new $mailClass($user));
                    }
                });
        }

        $this->info('Emails onboarding envoyés.');
    }
}
