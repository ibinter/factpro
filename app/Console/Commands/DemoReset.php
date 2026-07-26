<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DemoReset extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Réinitialise les comptes démo : reseed + suppression des données supplémentaires créées par les visiteurs.';

    public function handle(): int
    {
        $this->info('Réinitialisation des comptes démo...');

        // Supprime les documents créés au-delà des données seedées (trial_watermark = false
        // signifie qu'ils ont une licence valide ; on cible les créations humaines extra).
        $demoEmails = User::where('email', 'like', '%@demo.factpro.ibigsoft.com')->pluck('id');

        $deleted = Document::whereIn('created_by', $demoEmails)->delete();

        $this->line("  → {$deleted} document(s) démo supprimé(s).");

        // Relance le seeder pour recréer les données de base
        $this->call('db:seed', ['--class' => 'DemoUsersSeeder', '--force' => true]);

        Log::info('demo:reset exécuté', [
            'documents_deleted' => $deleted,
            'at' => now()->toDateTimeString(),
        ]);

        $this->info('Comptes démo réinitialisés avec succès.');

        return self::SUCCESS;
    }
}
