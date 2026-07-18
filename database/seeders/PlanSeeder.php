<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Grille tarifaire FCFA — cahier des charges §22.
 */
class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'starter',
                'name' => 'STARTER',
                'short_description' => 'Pour démarrer : devis et factures essentiels',
                'price_monthly' => 2500,
                'sort_order' => 1,
                'features' => [
                    'Devis & Factures', 'QR Anti-Falsification basique',
                    'Export PDF standard', 'Support email 72h',
                ],
                'limits' => [
                    'documents_per_month' => 10,
                    'users' => 1,
                    'companies' => 1,
                    'customers' => 25,
                    'products' => 15,
                    'templates' => 5,
                    'storage_mb' => 500,
                ],
            ],
            [
                'code' => 'pro',
                'name' => 'PRO',
                'short_description' => 'Le plus populaire : cycle commercial complet',
                'price_monthly' => 10000,
                'sort_order' => 2,
                'features' => [
                    'Documents illimités', 'Bons de commande & livraison',
                    'Avoir, reçu, acompte', 'Factures récurrentes',
                    'QR Anti-Falsification', 'Archivage immuable 10 ans',
                    'Signature électronique', 'Export Excel / CSV',
                    'Portail client', 'Multi-devises 160+',
                    'Relances email (3/mois)', 'Support email 48h',
                ],
                'limits' => [
                    'documents_per_month' => 'unlimited',
                    'users' => 3,
                    'companies' => 1,
                    'customers' => 'unlimited',
                    'products' => 'unlimited',
                    'templates' => 30,
                    'storage_mb' => 2048,
                ],
            ],
            [
                'code' => 'business',
                'name' => 'BUSINESS',
                'short_description' => 'Commerce physique : POS, stocks, thermique',
                'price_monthly' => 15000,
                'sort_order' => 3,
                'features' => [
                    'Tout le plan PRO', 'Ticket de caisse POS',
                    'Impression thermique 58/80mm', 'Étiquettes & Stickers Avery',
                    'Gestion des stocks', 'Time Tracking & projets',
                    'Comptabilité simplifiée + FEC', 'Mobile Money (Wave, Orange…)',
                    'Relances SMS & WhatsApp illimitées', 'API REST 1000/h',
                    'Support chat 24h',
                ],
                'limits' => [
                    'documents_per_month' => 'unlimited',
                    'users' => 10,
                    'companies' => 3,
                    'customers' => 'unlimited',
                    'products' => 'unlimited',
                    'templates' => 100,
                    'storage_mb' => 5120,
                ],
            ],
            [
                'code' => 'enterprise',
                'name' => 'ENTERPRISE',
                'short_description' => 'Grande structure : illimité + White-Label',
                'price_monthly' => 25000,
                'sort_order' => 4,
                'features' => [
                    'Tout le plan BUSINESS', 'Utilisateurs & sociétés illimités',
                    'White-Label revendeur', 'Signature certifiée',
                    'Portail client marque blanche', 'API REST illimitée',
                    'SLA 99.9%', 'Support WhatsApp dédié + formation live',
                ],
                'limits' => [
                    'documents_per_month' => 'unlimited',
                    'users' => 'unlimited',
                    'companies' => 'unlimited',
                    'customers' => 'unlimited',
                    'products' => 'unlimited',
                    'templates' => 'unlimited',
                    'storage_mb' => 10240,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['code' => $plan['code']], [
                ...$plan,
                'currency' => 'XOF',
                'trial_days' => 7,
                'is_active' => true,
            ]);
        }
    }
}
