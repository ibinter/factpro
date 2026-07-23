<?php

namespace App\Console\Commands;

use App\Mail\Billing\TrialEndingMail;
use App\Models\License;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendTrialEndingEmails extends Command
{
    protected $signature   = 'emails:trial-ending';
    protected $description = 'Send trial ending reminder emails (3 days before expiry)';

    public function handle(): void
    {
        // Licences trial qui expirent dans exactement 3 jours
        $licenses = License::with('user')
            ->where('type', 'trial')
            ->where('status', 'trial')
            ->whereDate('trial_ends_at', now()->addDays(3)->toDateString())
            ->get();

        foreach ($licenses as $license) {
            if (! $license->user) {
                continue;
            }

            $user    = $license->user;
            $company = $user->currentCompany ?? $user->companies()->first();

            $docsCount = $company
                ? DB::table('documents')->where('company_id', $company->id)->count()
                : 0;

            $customersCount = $company
                ? DB::table('customers')->where('company_id', $company->id)->count()
                : 0;

            Mail::to($user->email)->send(new TrialEndingMail(
                user:            $user,
                days_left:       3,
                expires_at:      $license->trial_ends_at->format('d/m/Y'),
                docs_count:      $docsCount,
                customers_count: $customersCount,
            ));
        }

        $this->info("Trial ending emails sent: {$licenses->count()}");
    }
}
