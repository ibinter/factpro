<?php

namespace App\Console\Commands;

use App\Models\CustomerSubscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Console\Command;

class ProcessSubscriptions extends Command
{
    protected $signature   = 'subscriptions:process';
    protected $description = 'Génère les factures des abonnements arrivés à échéance';

    public function handle(): int
    {
        $processed = 0;
        $errors    = 0;

        CustomerSubscription::where('status', 'active')
            ->where('next_billing_date', '<=', today())
            ->with(['customer', 'company'])
            ->each(function (CustomerSubscription $sub) use (&$processed, &$errors) {
                try {
                    $doc = $sub->generateInvoice();

                    SubscriptionInvoice::create([
                        'subscription_id' => $sub->id,
                        'document_id'     => $doc->id,
                        'billing_date'    => today(),
                        'amount'          => $sub->amount,
                        'status'          => 'generated',
                    ]);

                    $sub->update([
                        'last_billed_at'    => now(),
                        'next_billing_date' => $sub->calculateNextBillingDate(),
                    ]);

                    $this->info("Facture {$doc->number} générée pour {$sub->customer->name}");
                    $processed++;
                } catch (\Exception $e) {
                    $this->error("Erreur abonnement #{$sub->id} : " . $e->getMessage());

                    SubscriptionInvoice::create([
                        'subscription_id' => $sub->id,
                        'document_id'     => null,
                        'billing_date'    => today(),
                        'amount'          => $sub->amount,
                        'status'          => 'failed',
                        'notes'           => $e->getMessage(),
                    ]);

                    $errors++;
                }
            });

        $this->info("Traitement terminé : {$processed} facture(s) générée(s), {$errors} erreur(s).");

        return self::SUCCESS;
    }
}
