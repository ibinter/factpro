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
                'price_monthly' => 4900,
                'sort_order' => 1,
                'features' => [
                    'Devis & Factures (10/mois)',
                    'QR Anti-Falsification',
                    'Export PDF professionnel',
                    'Gestion des clients (25 max)',
                    'Catalogue produits (15 max)',
                    'Multi-devises (160 pays)',
                    'Tableau de bord basique',
                    'Support email 72h',
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
                'price_monthly' => 12900,
                'sort_order' => 2,
                'features' => [
                    'Documents illimités',
                    'Devis, Factures, BL, Avoirs, Acomptes',
                    'Factures récurrentes automatiques',
                    'QR Anti-Falsification certifié',
                    'Portail client self-service',
                    'Multi-devises 160+',
                    'Signature électronique devis',
                    'Relances email automatiques',
                    'Export Excel / CSV / PDF',
                    'Archivage légal 10 ans',
                    'CRM prospects léger',
                    'Support email 48h',
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
                'price_monthly' => 24900,
                'sort_order' => 3,
                'features' => [
                    'Tout le plan PRO inclus',
                    'Caisse POS tactile multi-tables',
                    'Impression thermique 58/80mm',
                    'Gestion des stocks avancée',
                    'Étiquettes & codes-barres Avery',
                    'Mobile Money (Wave, Orange, MTN…)',
                    'Comptabilité + export FEC',
                    'Time Tracking & projets',
                    'Module RH & bulletins de paie',
                    'Relances SMS & WhatsApp illimitées',
                    'API REST 1000 req/h',
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
                'price_monthly' => 59900,
                'sort_order' => 4,
                'features' => [
                    'Tout le plan BUSINESS inclus',
                    'Utilisateurs & sociétés illimités',
                    'White-Label revendeur (votre marque)',
                    'Signature électronique certifiée',
                    'Portail client marque blanche',
                    'API REST illimitée + Webhooks',
                    'Connecteurs Zapier & Make',
                    'Assistant IA SARA avancé',
                    'Factur-X / e-facture France 2026',
                    'Multi-pays OHADA / Maroc / Sénégal',
                    'SLA 99,9 % garanti',
                    'Support WhatsApp dédié + formation live',
                ],
                'limits' => [
                    'documents_per_month' => 'unlimited',
                    'users' => 'unlimited',
                    'companies' => 'unlimited',
                    'customers' => 'unlimited',
                    'products' => 'unlimited',
                    'templates' => 'unlimited',
                    'storage_mb' => 51200,
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
