<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crée les 3 comptes de démonstration publique (admin / comptable / caissier).
 * Mot de passe commun : demo1234
 * Réinitialisation recommandée toutes les 24h via scheduler.
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        // ── Entreprise démo partagée ──────────────────────────────────────
        $adminEmail = 'admin@demo.factpro.ibigsoft.com';

        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name'              => 'Admin Démo',
                'password'          => Hash::make('demo1234'),
                'email_verified_at' => now(),
            ]
        );

        // Toujours forcer le mot de passe (reset quotidien)
        $admin->update(['password' => Hash::make('demo1234')]);

        $company = Company::firstOrCreate(
            ['owner_id' => $admin->id],
            [
                'name'     => 'Démo IBIG FactPro',
                'currency' => 'XOF',
                'country'  => 'CI',
                'tax_id'   => 'DGI-DEMO-00001',
                'phone'    => '+225 27 22 27 60 14',
                'email'    => 'contact@demo.factpro.ibigsoft.com',
                'address'  => 'Cocody Riviera Palmeraie, Abidjan, Côte d\'Ivoire',
                'city'     => 'Abidjan',
            ]
        );

        // ── Comptable démo ────────────────────────────────────────────────
        $comptable = User::firstOrCreate(
            ['email' => 'comptable@demo.factpro.ibigsoft.com'],
            [
                'name'              => 'Comptable Démo',
                'password'          => Hash::make('demo1234'),
                'email_verified_at' => now(),
                'current_company_id' => $company->id,
            ]
        );
        $comptable->update(['password' => Hash::make('demo1234')]);

        // Attacher à la company si la relation company_user existe
        if (method_exists($company, 'users')) {
            $company->users()->syncWithoutDetaching([
                $comptable->id => ['role' => 'comptable'],
            ]);
        }

        // ── Caissier démo ─────────────────────────────────────────────────
        $caissier = User::firstOrCreate(
            ['email' => 'caissier@demo.factpro.ibigsoft.com'],
            [
                'name'              => 'Caissier Démo',
                'password'          => Hash::make('demo1234'),
                'email_verified_at' => now(),
                'current_company_id' => $company->id,
            ]
        );
        $caissier->update(['password' => Hash::make('demo1234')]);

        if (method_exists($company, 'users')) {
            $company->users()->syncWithoutDetaching([
                $caissier->id => ['role' => 'caissier'],
            ]);
        }

        // Lier le company_id sur l'admin
        $admin->update(['current_company_id' => $company->id]);

        $this->command->info('✅ Comptes démo créés/mis à jour (mot de passe : demo1234)');
    }
}
