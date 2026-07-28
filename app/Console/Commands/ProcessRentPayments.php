<?php

namespace App\Console\Commands;

use App\Models\Lease;
use App\Models\RentPayment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRentPayments extends Command
{
    protected $signature = 'rents:process';
    protected $description = 'Génère automatiquement les quittances de loyer du jour';

    public function handle(): int
    {
        $today = today();
        $period = $today->copy()->startOfMonth();

        $leases = Lease::where('status', 'active')
            ->where('payment_day', $today->day)
            ->with('property')
            ->get();

        $count = 0;

        foreach ($leases as $lease) {
            // Éviter les doublons pour le même mois
            $exists = RentPayment::where('lease_id', $lease->id)
                ->where('period_month', $period->toDateString())
                ->exists();

            if ($exists) {
                continue;
            }

            try {
                $document = $lease->property->generateRentInvoice($lease, $period);

                RentPayment::create([
                    'lease_id'     => $lease->id,
                    'document_id'  => $document->id,
                    'period_month' => $period->toDateString(),
                    'amount'       => $lease->monthly_rent,
                    'status'       => 'pending',
                ]);

                $count++;
                $this->info("Quittance générée: bail #{$lease->id} - {$period->format('m/Y')}");
            } catch (\Exception $e) {
                $this->error("Erreur bail #{$lease->id}: " . $e->getMessage());
            }
        }

        $this->info("{$count} quittance(s) générée(s).");

        return Command::SUCCESS;
    }
}
