<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $demo = User::where('email', 'demo@factpro.test')->first();
        if (! $demo) {
            return;
        }

        $company = Company::where('owner_id', $demo->id)->where('name', 'IBIG Démo SARL')->first();
        if (! $company) {
            return;
        }

        // ─── Clients supplémentaires ───
        $extraCustomers = [
            ['name' => 'Cabinet Conseil Diallo & Associés', 'type' => 'company', 'email' => 'diallo.cabinet@gmail.com', 'phone' => '+225 27 21 11 22 33', 'city' => 'Abidjan', 'country' => 'CI', 'rccm' => 'CI-ABJ-2023-B-22334'],
            ['name' => 'Supermarché FreshMart', 'type' => 'company', 'email' => 'achats@freshmart.ci', 'phone' => '+225 27 22 55 66 77', 'city' => 'Abidjan', 'country' => 'CI'],
            ['name' => 'N\'Goran Konan Armand', 'type' => 'individual', 'email' => 'armand.ngoran@outlook.com', 'phone' => '+225 05 66 77 88 99', 'city' => 'Bouaké', 'country' => 'CI'],
            ['name' => 'Hôtel Ivoire Palace', 'type' => 'company', 'email' => 'direction@ivoirepalace.ci', 'phone' => '+225 27 23 44 55 66', 'city' => 'Abidjan', 'country' => 'CI', 'rccm' => 'CI-ABJ-2019-B-44521'],
            ['name' => 'BTP Construction Koné SARL', 'type' => 'company', 'email' => 'contact@btpkone.ci', 'phone' => '+225 05 77 88 99 00', 'city' => 'Abidjan', 'country' => 'CI'],
            ['name' => 'Clinique Mère & Enfant', 'type' => 'company', 'email' => 'admin@clinique-me.ci', 'phone' => '+225 27 22 33 44 55', 'city' => 'Yamoussoukro', 'country' => 'CI'],
            ['name' => 'Traoré Mariam', 'type' => 'individual', 'email' => 'mariam.traore@yahoo.fr', 'phone' => '+225 07 55 44 33 22', 'city' => 'San-Pédro', 'country' => 'CI'],
            ['name' => 'Agence Immo Prestige', 'type' => 'company', 'email' => 'info@immoprestige.ci', 'phone' => '+225 27 25 66 77 88', 'city' => 'Abidjan', 'country' => 'CI'],
            ['name' => 'École Privée Les Étoiles', 'type' => 'company', 'email' => 'secretariat@ecoleetoiles.ci', 'phone' => '+225 27 23 55 66 77', 'city' => 'Abidjan', 'country' => 'CI'],
            ['name' => 'Groupe Transport Sécurifleet', 'type' => 'company', 'email' => 'ops@securifleet.ci', 'phone' => '+225 05 99 00 11 22', 'city' => 'Abidjan', 'country' => 'CI'],
        ];

        foreach ($extraCustomers as $data) {
            Customer::firstOrCreate(
                ['company_id' => $company->id, 'name' => $data['name']],
                [...$data, 'currency' => 'XOF'],
            );
        }

        // ─── Produits supplémentaires ───
        $extraProducts = [
            ['name' => 'Tableau blanc magnétique 120x90cm', 'type' => 'product', 'sku' => 'TBL-MAG-120', 'unit' => 'unité', 'price' => 28500, 'cost' => 18000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 8, 'stock_alert_threshold' => 2],
            ['name' => 'Formation en bureautique (MS Office)', 'type' => 'service', 'sku' => 'FORM-OFFICE', 'unit' => 'session', 'price' => 120000, 'cost' => 0, 'tax_rate' => 18],
            ['name' => 'Câble réseau Cat6 (bobine 305m)', 'type' => 'product', 'sku' => 'CAB-CAT6-305', 'unit' => 'bobine', 'price' => 65000, 'cost' => 48000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 5, 'stock_alert_threshold' => 1],
            ['name' => 'Switch réseau 24 ports', 'type' => 'product', 'sku' => 'SWT-24P', 'unit' => 'unité', 'price' => 95000, 'cost' => 72000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 4, 'stock_alert_threshold' => 1],
            ['name' => 'Audit et rapport sécurité informatique', 'type' => 'service', 'sku' => 'SRV-AUDIT-SEC', 'unit' => 'prestation', 'price' => 350000, 'cost' => 0, 'tax_rate' => 18],
            ['name' => 'Disque dur externe 2To USB3', 'type' => 'product', 'sku' => 'HDD-EXT-2T', 'unit' => 'unité', 'price' => 42000, 'cost' => 32000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 15, 'stock_alert_threshold' => 3],
            ['name' => 'Abonnement antivirus entreprise (1 an)', 'type' => 'service', 'sku' => 'AV-ENT-1AN', 'unit' => 'licence', 'price' => 35000, 'cost' => 20000, 'tax_rate' => 18],
            ['name' => 'Clé USB 32Go Kingston', 'type' => 'product', 'sku' => 'USB-32G-KNG', 'unit' => 'unité', 'price' => 4500, 'cost' => 2800, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 50, 'stock_alert_threshold' => 10],
            ['name' => 'Onduleur APC 1500VA', 'type' => 'product', 'sku' => 'OND-APC-1500', 'unit' => 'unité', 'price' => 185000, 'cost' => 140000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 3, 'stock_alert_threshold' => 1],
            ['name' => 'Support technique mensuel (contrat)', 'type' => 'service', 'sku' => 'SRV-SUPPORT-M', 'unit' => 'mois', 'price' => 75000, 'cost' => 0, 'tax_rate' => 18],
        ];

        foreach ($extraProducts as $data) {
            Product::firstOrCreate(
                ['company_id' => $company->id, 'sku' => $data['sku']],
                [...$data, 'is_active' => true],
            );
        }

        // ─── Documents supplémentaires (idempotent) ───
        if ($company->documents()->count() >= 10) {
            return;
        }

        $documentService = app(DocumentService::class);
        $customerIds = $company->customers()->pluck('id', 'name');
        $productList = $company->products()->get()->keyBy('sku');

        // ── 5 factures payées ──

        // 1) Cabinet Diallo — HP + Maintenance
        try {
            $inv = $documentService->create($company, $demo, [
                'type' => 'invoice',
                'customer_id' => $customerIds['Cabinet Conseil Diallo & Associés'],
                'issue_date' => now()->subDays(60)->toDateString(),
                'due_date' => now()->subDays(30)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['HP-15-001']->id, 'description' => 'Ordinateur portable HP 15', 'quantity' => 2, 'unit' => 'unité', 'unit_price' => 385000, 'tax_rate' => 18],
                ['product_id' => $productList['SRV-MAINT']->id, 'description' => 'Maintenance mise en service', 'quantity' => 4, 'unit' => 'heure', 'unit_price' => 15000, 'tax_rate' => 18],
            ]);
            $documentService->finalize($inv);
            $documentService->registerPayment($inv, [
                'amount' => (float) $inv->total,
                'method' => 'bank_transfer',
                'reference' => 'VIR-BAT-2025-001',
                'paid_at' => now()->subDays(25)->toDateString(),
            ], $demo);
        } catch (\Throwable $e) { /* skip */ }

        // 2) Hôtel Ivoire Palace — Switch + câbles
        try {
            $inv = $documentService->create($company, $demo, [
                'type' => 'invoice',
                'customer_id' => $customerIds['Hôtel Ivoire Palace'],
                'issue_date' => now()->subDays(45)->toDateString(),
                'due_date' => now()->subDays(15)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['SWT-24P']->id, 'description' => 'Switch réseau 24 ports', 'quantity' => 3, 'unit' => 'unité', 'unit_price' => 95000, 'tax_rate' => 18],
                ['product_id' => $productList['CAB-CAT6-305']->id, 'description' => 'Câble réseau Cat6 305m', 'quantity' => 2, 'unit' => 'bobine', 'unit_price' => 65000, 'tax_rate' => 18],
                ['product_id' => $productList['SRV-NET']->id, 'description' => 'Installation réseau hôtel', 'quantity' => 3, 'unit' => 'jour', 'unit_price' => 85000, 'tax_rate' => 18],
            ]);
            $documentService->finalize($inv);
            $documentService->registerPayment($inv, [
                'amount' => (float) $inv->total,
                'method' => 'mobile_money',
                'reference' => 'OM-2025-HOT001',
                'paid_at' => now()->subDays(10)->toDateString(),
            ], $demo);
        } catch (\Throwable $e) { /* skip */ }

        // 3) Supermarché FreshMart — Imprimantes + papier
        try {
            $inv = $documentService->create($company, $demo, [
                'type' => 'invoice',
                'customer_id' => $customerIds['Supermarché FreshMart'],
                'issue_date' => now()->subDays(30)->toDateString(),
                'due_date' => now()->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['THERM-80']->id, 'description' => 'Imprimante thermique 80mm', 'quantity' => 5, 'unit' => 'unité', 'unit_price' => 45000, 'tax_rate' => 18],
                ['product_id' => $productList['PAP-A4']->id, 'description' => 'Rame papier A4 80g', 'quantity' => 50, 'unit' => 'unité', 'unit_price' => 3500, 'tax_rate' => 18],
                ['product_id' => $productList['USB-32G-KNG']->id, 'description' => 'Clé USB 32Go Kingston', 'quantity' => 10, 'unit' => 'unité', 'unit_price' => 4500, 'tax_rate' => 18],
            ]);
            $documentService->finalize($inv);
            $documentService->registerPayment($inv, [
                'amount' => (float) $inv->total,
                'method' => 'cash',
                'reference' => 'ESP-FRESH-001',
                'paid_at' => now()->subDays(2)->toDateString(),
            ], $demo);
        } catch (\Throwable $e) { /* skip */ }

        // 4) École Les Étoiles — Formation bureautique
        try {
            $inv = $documentService->create($company, $demo, [
                'type' => 'invoice',
                'customer_id' => $customerIds['École Privée Les Étoiles'],
                'issue_date' => now()->subDays(20)->toDateString(),
                'due_date' => now()->addDays(10)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['FORM-OFFICE']->id, 'description' => 'Formation MS Office — enseignants', 'quantity' => 3, 'unit' => 'session', 'unit_price' => 120000, 'tax_rate' => 18],
                ['product_id' => $productList['TBL-MAG-120']->id, 'description' => 'Tableau blanc magnétique', 'quantity' => 5, 'unit' => 'unité', 'unit_price' => 28500, 'tax_rate' => 18],
            ]);
            $documentService->finalize($inv);
            $documentService->registerPayment($inv, [
                'amount' => (float) $inv->total,
                'method' => 'mobile_money',
                'reference' => 'WAVE-ECOLE-001',
                'paid_at' => now()->subDays(5)->toDateString(),
            ], $demo);
        } catch (\Throwable $e) { /* skip */ }

        // 5) Clinique Mère & Enfant — Onduleurs + support mensuel
        try {
            $inv = $documentService->create($company, $demo, [
                'type' => 'invoice',
                'customer_id' => $customerIds['Clinique Mère & Enfant'],
                'issue_date' => now()->subDays(15)->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['OND-APC-1500']->id, 'description' => 'Onduleur APC 1500VA', 'quantity' => 2, 'unit' => 'unité', 'unit_price' => 185000, 'tax_rate' => 18],
                ['product_id' => $productList['SRV-SUPPORT-M']->id, 'description' => 'Support technique mensuel', 'quantity' => 6, 'unit' => 'mois', 'unit_price' => 75000, 'tax_rate' => 18],
            ]);
            $documentService->finalize($inv);
            $documentService->registerPayment($inv, [
                'amount' => (float) $inv->total,
                'method' => 'bank_transfer',
                'reference' => 'VIR-CLINIQUE-01',
                'paid_at' => now()->subDays(3)->toDateString(),
            ], $demo);
        } catch (\Throwable $e) { /* skip */ }

        // ── 3 factures en retard (overdue) ──

        // 6) BTP Construction Koné — disques durs
        try {
            $inv = $documentService->create($company, $demo, [
                'type' => 'invoice',
                'customer_id' => $customerIds['BTP Construction Koné SARL'],
                'issue_date' => now()->subDays(70)->toDateString(),
                'due_date' => now()->subDays(40)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['HDD-EXT-2T']->id, 'description' => 'Disque dur externe 2To', 'quantity' => 6, 'unit' => 'unité', 'unit_price' => 42000, 'tax_rate' => 18],
                ['product_id' => $productList['SRV-MAINT']->id, 'description' => 'Maintenance informatique', 'quantity' => 8, 'unit' => 'heure', 'unit_price' => 15000, 'tax_rate' => 18],
            ]);
            $documentService->finalize($inv);
            $inv->update(['status' => 'overdue']);
        } catch (\Throwable $e) { /* skip */ }

        // 7) Agence Immo Prestige — audit sécurité
        try {
            $inv = $documentService->create($company, $demo, [
                'type' => 'invoice',
                'customer_id' => $customerIds['Agence Immo Prestige'],
                'issue_date' => now()->subDays(55)->toDateString(),
                'due_date' => now()->subDays(25)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['SRV-AUDIT-SEC']->id, 'description' => 'Audit sécurité informatique', 'quantity' => 1, 'unit' => 'prestation', 'unit_price' => 350000, 'tax_rate' => 18],
            ]);
            $documentService->finalize($inv);
            $inv->update(['status' => 'overdue']);
        } catch (\Throwable $e) { /* skip */ }

        // 8) N'Goran Konan Armand — antivirus + clés USB
        try {
            $inv = $documentService->create($company, $demo, [
                'type' => 'invoice',
                'customer_id' => $customerIds["N'Goran Konan Armand"],
                'issue_date' => now()->subDays(50)->toDateString(),
                'due_date' => now()->subDays(20)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['AV-ENT-1AN']->id, 'description' => 'Antivirus entreprise 1 an', 'quantity' => 2, 'unit' => 'licence', 'unit_price' => 35000, 'tax_rate' => 18],
                ['product_id' => $productList['USB-32G-KNG']->id, 'description' => 'Clé USB 32Go', 'quantity' => 5, 'unit' => 'unité', 'unit_price' => 4500, 'tax_rate' => 18],
            ]);
            $documentService->finalize($inv);
            $inv->update(['status' => 'overdue']);
        } catch (\Throwable $e) { /* skip */ }

        // ── 3 devis en attente ──

        // 9) Groupe Transport Sécurifleet — switch + câbles
        try {
            $documentService->create($company, $demo, [
                'type' => 'quote',
                'customer_id' => $customerIds['Groupe Transport Sécurifleet'],
                'issue_date' => now()->subDays(5)->toDateString(),
                'due_date' => now()->addDays(25)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['SWT-24P']->id, 'description' => 'Switch réseau 24 ports', 'quantity' => 5, 'unit' => 'unité', 'unit_price' => 95000, 'tax_rate' => 18],
                ['product_id' => $productList['CAB-CAT6-305']->id, 'description' => 'Câble Cat6 bobine 305m', 'quantity' => 4, 'unit' => 'bobine', 'unit_price' => 65000, 'tax_rate' => 18],
                ['product_id' => $productList['SRV-NET']->id, 'description' => 'Installation réseau flotte', 'quantity' => 5, 'unit' => 'jour', 'unit_price' => 85000, 'tax_rate' => 18],
            ]);
        } catch (\Throwable $e) { /* skip */ }

        // 10) Hôtel Ivoire Palace — formation bureautique
        try {
            $documentService->create($company, $demo, [
                'type' => 'quote',
                'customer_id' => $customerIds['Hôtel Ivoire Palace'],
                'issue_date' => now()->subDays(2)->toDateString(),
                'due_date' => now()->addDays(28)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['FORM-OFFICE']->id, 'description' => 'Formation bureautique personnel', 'quantity' => 5, 'unit' => 'session', 'unit_price' => 120000, 'discount_percent' => 10, 'tax_rate' => 18],
            ]);
        } catch (\Throwable $e) { /* skip */ }

        // 11) BTP Construction Koné — matériel informatique
        try {
            $documentService->create($company, $demo, [
                'type' => 'quote',
                'customer_id' => $customerIds['BTP Construction Koné SARL'],
                'issue_date' => now()->subDays(1)->toDateString(),
                'due_date' => now()->addDays(29)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['HP-15-001']->id, 'description' => 'Ordinateur portable HP 15', 'quantity' => 3, 'unit' => 'unité', 'unit_price' => 385000, 'tax_rate' => 18],
                ['product_id' => $productList['OND-APC-1500']->id, 'description' => 'Onduleur APC 1500VA', 'quantity' => 3, 'unit' => 'unité', 'unit_price' => 185000, 'tax_rate' => 18],
            ]);
        } catch (\Throwable $e) { /* skip */ }

        // ── 2 devis acceptés (convertis en factures) ──

        // 12) Cabinet Diallo — support mensuel (devis accepté → facture)
        try {
            $quote = $documentService->create($company, $demo, [
                'type' => 'quote',
                'customer_id' => $customerIds['Cabinet Conseil Diallo & Associés'],
                'issue_date' => now()->subDays(25)->toDateString(),
                'due_date' => now()->addDays(5)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['SRV-SUPPORT-M']->id, 'description' => 'Support technique mensuel — contrat 12 mois', 'quantity' => 12, 'unit' => 'mois', 'unit_price' => 75000, 'discount_percent' => 5, 'tax_rate' => 18],
            ]);
            $documentService->finalize($quote);
            $quote->update(['status' => 'accepted']);
            $documentService->convert($quote, 'invoice', $demo);
        } catch (\Throwable $e) { /* skip */ }

        // 13) Traoré Mariam — clés USB + disques durs (devis accepté → facture)
        try {
            $quote = $documentService->create($company, $demo, [
                'type' => 'quote',
                'customer_id' => $customerIds['Traoré Mariam'],
                'issue_date' => now()->subDays(18)->toDateString(),
                'due_date' => now()->addDays(12)->toDateString(),
                'currency' => 'XOF',
            ], [
                ['product_id' => $productList['USB-32G-KNG']->id, 'description' => 'Clé USB 32Go Kingston', 'quantity' => 20, 'unit' => 'unité', 'unit_price' => 4500, 'tax_rate' => 18],
                ['product_id' => $productList['HDD-EXT-2T']->id, 'description' => 'Disque dur externe 2To', 'quantity' => 3, 'unit' => 'unité', 'unit_price' => 42000, 'tax_rate' => 18],
            ]);
            $documentService->finalize($quote);
            $quote->update(['status' => 'accepted']);
            $documentService->convert($quote, 'invoice', $demo);
        } catch (\Throwable $e) { /* skip */ }

        // ── 1 avoir (credit note) ──

        // 14) Pharmacie Sainte-Marie — avoir sur retour imprimante
        try {
            $creditNote = $documentService->create($company, $demo, [
                'type' => 'credit_note',
                'customer_id' => $customerIds['Pharmacie Sainte-Marie'],
                'issue_date' => now()->subDays(8)->toDateString(),
                'due_date' => now()->addDays(22)->toDateString(),
                'currency' => 'XOF',
                'notes' => 'Avoir suite retour imprimante défectueuse — ref FA-2025-002',
            ], [
                ['product_id' => $productList['THERM-80']->id, 'description' => 'Retour imprimante thermique 80mm', 'quantity' => 1, 'unit' => 'unité', 'unit_price' => 45000, 'tax_rate' => 18],
            ]);
            $documentService->finalize($creditNote);
        } catch (\Throwable $e) { /* skip */ }

        // ── 1 bon de livraison ──

        // 15) Supermarché FreshMart — bon de livraison matériel
        try {
            $delivery = $documentService->create($company, $demo, [
                'type' => 'delivery_note',
                'customer_id' => $customerIds['Supermarché FreshMart'],
                'issue_date' => now()->subDays(1)->toDateString(),
                'due_date' => now()->addDays(29)->toDateString(),
                'currency' => 'XOF',
                'notes' => 'Livraison matériel caisse — installation prévue le lendemain',
            ], [
                ['product_id' => $productList['THERM-80']->id, 'description' => 'Imprimante thermique 80mm', 'quantity' => 3, 'unit' => 'unité', 'unit_price' => 45000, 'tax_rate' => 18],
                ['product_id' => $productList['PAP-A4']->id, 'description' => 'Rame papier A4 80g', 'quantity' => 20, 'unit' => 'unité', 'unit_price' => 3500, 'tax_rate' => 18],
            ]);
            $documentService->finalize($delivery);
        } catch (\Throwable $e) { /* skip */ }
    }
}
