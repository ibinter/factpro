<?php

namespace Database\Seeders;

use App\Models\ModuleFeature;
use Illuminate\Database\Seeder;

class ModuleFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            ['slug' => 'facturation', 'category' => 'module', 'name_fr' => 'Facturation', 'name_en' => 'Invoicing', 'description_fr' => 'Devis, factures, avoirs, bons de commande, récurrences.', 'icon' => '📄', 'available_in_plans' => ['starter', 'pro', 'business', 'enterprise'], 'sort_order' => 1],
            ['slug' => 'crm', 'category' => 'module', 'name_fr' => 'Clients & CRM', 'name_en' => 'Clients & CRM', 'description_fr' => 'Gestion clients, pipeline commercial, relances automatiques.', 'icon' => '👥', 'available_in_plans' => ['starter', 'pro', 'business', 'enterprise'], 'sort_order' => 2],
            ['slug' => 'stock', 'category' => 'module', 'name_fr' => 'Stock & Inventaire', 'name_en' => 'Stock & Inventory', 'description_fr' => 'Gestion des stocks, alertes, codes-barres, inventaires.', 'icon' => '📦', 'available_in_plans' => ['pro', 'business', 'enterprise'], 'sort_order' => 3],
            ['slug' => 'tresorerie', 'category' => 'module', 'name_fr' => 'Trésorerie', 'name_en' => 'Cash Flow', 'description_fr' => 'Encaissements, dépenses, solde temps réel.', 'icon' => '💰', 'available_in_plans' => ['pro', 'business', 'enterprise'], 'sort_order' => 4],
            ['slug' => 'pos', 'category' => 'module', 'name_fr' => 'Caisse POS', 'name_en' => 'POS Terminal', 'description_fr' => 'Point de vente tactile, ticket thermique, caisse enregistreuse.', 'icon' => '🏪', 'available_in_plans' => ['pro', 'business', 'enterprise'], 'sort_order' => 5],
            ['slug' => 'rapports', 'category' => 'module', 'name_fr' => 'Rapports & Analytics', 'name_en' => 'Reports & Analytics', 'description_fr' => 'KPIs, graphiques, exports PDF/Excel, comparaisons N-1.', 'icon' => '📊', 'available_in_plans' => ['starter', 'pro', 'business', 'enterprise'], 'sort_order' => 6],
            ['slug' => 'api', 'category' => 'module', 'name_fr' => 'API & Webhooks', 'name_en' => 'API & Webhooks', 'description_fr' => 'API REST publique, webhooks Zapier/Make, intégrations.', 'icon' => '🔗', 'available_in_plans' => ['business', 'enterprise'], 'sort_order' => 7],
            ['slug' => 'multi-societes', 'category' => 'module', 'name_fr' => 'Multi-sociétés', 'name_en' => 'Multi-company', 'description_fr' => 'Gérer plusieurs entreprises depuis un seul compte.', 'icon' => '🏢', 'available_in_plans' => ['business', 'enterprise'], 'sort_order' => 8],
            ['slug' => 'sara', 'category' => 'feature', 'name_fr' => 'Assistant IA SARA', 'name_en' => 'SARA AI Assistant', 'description_fr' => 'Assistance intelligente 24h/24 pour utilisateurs et visiteurs.', 'icon' => '🤖', 'available_in_plans' => null, 'sort_order' => 9],
            ['slug' => 'pwa', 'category' => 'feature', 'name_fr' => 'Application PWA', 'name_en' => 'PWA App', 'description_fr' => 'Installable sur tous appareils, mode hors ligne.', 'icon' => '📱', 'available_in_plans' => null, 'sort_order' => 10],
            ['slug' => 'mobile-money', 'category' => 'integration', 'name_fr' => 'Mobile Money', 'name_en' => 'Mobile Money', 'description_fr' => 'Orange Money, Wave, MTN MoMo, Moov Money.', 'icon' => '💳', 'available_in_plans' => ['starter', 'pro', 'business', 'enterprise'], 'status' => 'available', 'sort_order' => 11],
            ['slug' => 'cinetpay', 'category' => 'integration', 'name_fr' => 'CinetPay', 'name_en' => 'CinetPay', 'description_fr' => 'Passerelle de paiement CinetPay.', 'icon' => '💳', 'available_in_plans' => ['pro', 'business', 'enterprise'], 'status' => 'available', 'sort_order' => 12],
        ];

        foreach ($modules as $data) {
            ModuleFeature::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge(['status' => 'available'], $data),
            );
        }
    }
}
