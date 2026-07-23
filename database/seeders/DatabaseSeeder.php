<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\PaymentMethodConfig;
use App\Models\Product;
use App\Models\User;
use App\Services\DocumentService;
use App\Services\LicenseService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlanSeeder::class);

        // ─── Superadmin IBIG ───
        User::updateOrCreate(
            ['email' => 'admin@ibigsoft.com'],
            [
                'name' => 'Superadmin IBIG',
                'password' => Hash::make('Admin@Factpro2026'),
                'is_superadmin' => true,
                'country' => 'CI',
                'email_verified_at' => now(),
            ],
        );

        // ─── Compte de démonstration ───
        $demo = User::updateOrCreate(
            ['email' => 'demo@factpro.test'],
            [
                'name' => 'Patrice Kouakou',
                'password' => Hash::make('Demo@2026'),
                'phone' => '+225 07 00 00 00 01',
                'country' => 'CI',
                'email_verified_at' => now(),
            ],
        );

        $company = Company::firstOrCreate(
            ['owner_id' => $demo->id, 'name' => 'IBIG Démo SARL'],
            [
                'legal_name' => 'IBIG Démo SARL au capital de 1 000 000 FCFA',
                'email' => 'demo@factpro.test',
                'phone' => '+225 27 22 27 60 14',
                'address' => 'Cocody Riviera, Rue des Jardins',
                'city' => 'Abidjan',
                'country' => 'CI',
                'currency' => 'XOF',
                'tax_id' => 'CI-ABJ-2026-B-12345',
                'invoice_footer' => 'Merci de votre confiance — Conditions de paiement : 30 jours.',
            ],
        );
        $company->users()->syncWithoutDetaching([$demo->id => ['role' => 'owner']]);
        $demo->forceFill(['current_company_id' => $company->id])->save();

        app(LicenseService::class)->startTrial($demo);

        // ─── Clients de démo ───
        $customers = [
            ['name' => 'Boulangerie du Plateau', 'type' => 'company', 'email' => 'contact@boulangerieplateau.ci', 'phone' => '+225 27 20 21 22 23', 'city' => 'Abidjan', 'country' => 'CI'],
            ['name' => 'Pharmacie Sainte-Marie', 'type' => 'company', 'email' => 'pharmacie.stemarie@gmail.com', 'phone' => '+225 27 22 44 55 66', 'city' => 'Abidjan', 'country' => 'CI'],
            ['name' => 'Kouadio Jean-Marc', 'type' => 'individual', 'email' => 'jm.kouadio@yahoo.fr', 'phone' => '+225 05 44 33 22 11', 'city' => 'Yamoussoukro', 'country' => 'CI'],
            ['name' => 'SODECI Distribution', 'type' => 'company', 'email' => 'achats@sodeci-dist.ci', 'phone' => '+225 27 21 30 40 50', 'city' => 'Abidjan', 'country' => 'CI'],
        ];
        foreach ($customers as $data) {
            Customer::firstOrCreate(
                ['company_id' => $company->id, 'name' => $data['name']],
                [...$data, 'currency' => 'XOF'],
            );
        }

        // ─── Produits de démo ───
        $products = [
            ['name' => 'Ordinateur portable HP 15', 'type' => 'product', 'sku' => 'HP-15-001', 'unit' => 'unité', 'price' => 385000, 'cost' => 310000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 12, 'stock_alert_threshold' => 3],
            ['name' => 'Imprimante thermique 80mm', 'type' => 'product', 'sku' => 'THERM-80', 'unit' => 'unité', 'price' => 45000, 'cost' => 32000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 25, 'stock_alert_threshold' => 5],
            ['name' => 'Maintenance informatique', 'type' => 'service', 'sku' => 'SRV-MAINT', 'unit' => 'heure', 'price' => 15000, 'cost' => 0, 'tax_rate' => 18],
            ['name' => 'Installation réseau', 'type' => 'service', 'sku' => 'SRV-NET', 'unit' => 'jour', 'price' => 85000, 'cost' => 0, 'tax_rate' => 18],
            ['name' => 'Rame papier A4 80g', 'type' => 'product', 'sku' => 'PAP-A4', 'unit' => 'unité', 'price' => 3500, 'cost' => 2600, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 140, 'stock_alert_threshold' => 20],
        ];
        foreach ($products as $data) {
            Product::firstOrCreate(
                ['company_id' => $company->id, 'sku' => $data['sku']],
                [...$data, 'is_active' => true],
            );
        }

        // ─── Documents de démo ───
        if ($company->documents()->count() === 0) {
            $documentService = app(DocumentService::class);
            $customerIds = $company->customers()->pluck('id', 'name');
            $productList = $company->products()->get()->keyBy('sku');

            // Facture payée
            $invoice = $documentService->create($company, $demo, [
                'type' => 'invoice',
                'customer_id' => $customerIds['Boulangerie du Plateau'],
                'issue_date' => now()->subDays(12)->toDateString(),
                'due_date' => now()->addDays(18)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['THERM-80']->id, 'description' => 'Imprimante thermique 80mm', 'quantity' => 2, 'unit' => 'unité', 'unit_price' => 45000, 'tax_rate' => 18],
                ['product_id' => $productList['SRV-MAINT']->id, 'description' => 'Maintenance informatique — installation caisse', 'quantity' => 3, 'unit' => 'heure', 'unit_price' => 15000, 'tax_rate' => 18],
            ]);
            $documentService->finalize($invoice);
            $documentService->registerPayment($invoice, [
                'amount' => (float) $invoice->total,
                'method' => 'mobile_money',
                'reference' => 'OM-2607-TEST01',
                'paid_at' => now()->subDays(5)->toDateString(),
            ], $demo);

            // Devis en attente
            $documentService->create($company, $demo, [
                'type' => 'quote',
                'customer_id' => $customerIds['SODECI Distribution'],
                'issue_date' => now()->subDays(3)->toDateString(),
                'due_date' => now()->addDays(27)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['HP-15-001']->id, 'description' => 'Ordinateur portable HP 15', 'quantity' => 5, 'unit' => 'unité', 'unit_price' => 385000, 'discount_percent' => 5, 'tax_rate' => 18],
                ['product_id' => $productList['SRV-NET']->id, 'description' => 'Installation réseau — site principal', 'quantity' => 2, 'unit' => 'jour', 'unit_price' => 85000, 'tax_rate' => 18],
            ]);

            // Facture impayée
            $documentService->create($company, $demo, [
                'type' => 'invoice',
                'customer_id' => $customerIds['Pharmacie Sainte-Marie'],
                'issue_date' => now()->subDays(40)->toDateString(),
                'due_date' => now()->subDays(10)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['PAP-A4']->id, 'description' => 'Rame papier A4 80g', 'quantity' => 30, 'unit' => 'unité', 'unit_price' => 3500, 'tax_rate' => 18],
            ])->update(['status' => 'overdue']);
        }

        // ─── Moyens de paiement manuels (config superadmin — script §4.2) ───
        $methods = [
            ['type' => 'mobile_money', 'country' => 'CI', 'label' => 'Orange Money Côte d\'Ivoire', 'operator' => 'orange', 'account_number' => '+225 07 07 00 11 22', 'account_holder' => 'IBIG SARL', 'currency' => 'XOF', 'instructions' => 'Envoyez le montant exact puis conservez le SMS de confirmation comme preuve.', 'sort_order' => 1],
            ['type' => 'mobile_money', 'country' => 'CI', 'label' => 'Wave Côte d\'Ivoire', 'operator' => 'wave', 'account_number' => '+225 05 05 99 88 77', 'account_holder' => 'IBIG SARL', 'currency' => 'XOF', 'instructions' => 'Transfert Wave sans frais. Faites une capture d\'écran du reçu.', 'sort_order' => 2],
            ['type' => 'mobile_money', 'country' => 'CI', 'label' => 'MTN Mobile Money CI', 'operator' => 'mtn', 'account_number' => '+225 05 55 44 33 22', 'account_holder' => 'IBIG SARL', 'currency' => 'XOF', 'instructions' => 'Envoyez via MoMo puis notez la référence de transaction.', 'sort_order' => 3],
            ['type' => 'bank_national', 'country' => 'CI', 'label' => 'Banque Atlantique CI', 'operator' => null, 'bank_name' => 'Banque Atlantique', 'account_number' => 'CI93 CI042 01001 123456789012 34', 'account_holder' => 'IBIG SARL', 'currency' => 'XOF', 'instructions' => 'Mentionnez votre numéro de commande en référence du virement.', 'sort_order' => 4],
        ];
        foreach ($methods as $method) {
            PaymentMethodConfig::firstOrCreate(
                ['type' => $method['type'], 'label' => $method['label']],
                [...$method, 'is_active' => true],
            );
        }

        $this->call(DemoDataSeeder::class);
    }
}
