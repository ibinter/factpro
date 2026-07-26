<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\License;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Crée les 3 comptes démo avec licences actives + données réalistes.
 * Mot de passe commun : demo1234
 * À lancer via : php artisan db:seed --class=DemoUsersSeeder
 */
class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $plan = Plan::where('code', 'business')->first()
            ?? Plan::orderByDesc('id')->first();

        if (! $plan) {
            $this->command->error('Aucun plan trouvé. Lance php artisan db:seed --class=PlanSeeder d\'abord.');
            return;
        }

        // ── 1. Utilisateurs ──────────────────────────────────────────────
        $admin = $this->upsertUser('admin@demo.factpro.ibigsoft.com', 'Admin Démo IBIG');
        $comptable = $this->upsertUser('comptable@demo.factpro.ibigsoft.com', 'Aissatou Koné');
        $caissier = $this->upsertUser('caissier@demo.factpro.ibigsoft.com', 'Moussa Traoré');

        // ── 2. Entreprise démo ───────────────────────────────────────────
        $company = Company::firstOrCreate(
            ['owner_id' => $admin->id],
            [
                'name'     => 'Tech Solutions CI SARL',
                'currency' => 'XOF',
                'country'  => 'CI',
                'tax_id'   => 'DGI-2021-B-00-4521',
                'phone'    => '+225 27 22 27 60 14',
                'email'    => 'contact@techsolutions.ci',
                'address'  => 'Cocody Riviera Palmeraie, Rue des Jardins',
                'city'     => 'Abidjan',
                'website'  => 'https://techsolutions.ci',
                'logo'     => null,
            ]
        );

        // Rattacher tous les utilisateurs à la company
        $admin->update(['current_company_id' => $company->id]);
        $comptable->update(['current_company_id' => $company->id]);
        $caissier->update(['current_company_id' => $company->id]);

        // Attacher comptable et caissier comme membres de l'équipe
        if (method_exists($company, 'users')) {
            $company->users()->syncWithoutDetaching([
                $comptable->id => ['role' => 'comptable'],
                $caissier->id  => ['role' => 'caissier'],
            ]);
        }

        // ── 3. Licences actives (1 an, plan BUSINESS) ────────────────────
        foreach ([$admin, $comptable, $caissier] as $user) {
            $this->ensureLicense($user, $plan);
        }

        // ── 4. Clients démo ──────────────────────────────────────────────
        $customers = $this->seedCustomers($company);

        // ── 5. Produits & services démo ──────────────────────────────────
        $products = $this->seedProducts($company);

        // ── 6. Factures + devis + bons de commande démo ──────────────────
        $this->seedDocuments($admin, $company, $customers, $products);

        $this->command->info('✅ Démo complète : 3 comptes + licences + clients + produits + documents créés.');
        $this->command->info('   Email admin    : admin@demo.factpro.ibigsoft.com');
        $this->command->info('   Email comptable: comptable@demo.factpro.ibigsoft.com');
        $this->command->info('   Email caissier : caissier@demo.factpro.ibigsoft.com');
        $this->command->info('   Mot de passe   : demo1234');
    }

    // ─────────────────────────────────────────────────────────────────────
    private function upsertUser(string $email, string $name): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'password'          => Hash::make('demo1234'),
                'email_verified_at' => now(),
            ]
        );
        // Toujours réinitialiser le mot de passe
        $user->update(['password' => Hash::make('demo1234')]);
        return $user;
    }

    private function ensureLicense(User $user, Plan $plan): void
    {
        // Expirer uniquement les licences NON-démo (trial, paid, etc.)
        License::where('user_id', $user->id)
            ->where('type', '!=', 'demo')
            ->whereIn('status', ['trial', 'active', 'provisional'])
            ->update(['status' => 'expired']);

        // Créer ou remettre active la licence démo
        $existing = License::where('user_id', $user->id)->where('type', 'demo')->first();

        if ($existing) {
            $existing->update([
                'status'  => 'active',
                'ends_at' => now()->addYear(),
                'plan_id' => $plan->id,
            ]);
        } else {
            License::create([
                'user_id'           => $user->id,
                'plan_id'           => $plan->id,
                'license_key'       => 'DEMO-' . strtoupper(Str::random(12)),
                'type'              => 'demo',
                'status'            => 'active',
                'activation_source' => 'demo_seeder',
                'starts_at'         => now(),
                'ends_at'           => now()->addYear(),
                'limits'            => $plan->limits ?? [],
                'metadata'          => ['is_demo' => true],
            ]);
        }
    }

    private function seedCustomers(Company $company): array
    {
        $data = [
            ['name' => 'Cabinet Médical du Plateau', 'type' => 'company', 'email' => 'admin@cabinetplateau.ci', 'phone' => '+225 27 22 11 22 33', 'city' => 'Abidjan', 'tax_id' => 'CI-ABJ-2018-B-11234'],
            ['name' => 'Supermarché FreshMart', 'type' => 'company', 'email' => 'achats@freshmart.ci', 'phone' => '+225 27 22 55 66 77', 'city' => 'Abidjan'],
            ['name' => 'Konan Armand', 'type' => 'individual', 'email' => 'armand.konan@gmail.com', 'phone' => '+225 05 66 77 88 99', 'city' => 'Bouaké'],
            ['name' => 'Hôtel Ivoire Palace', 'type' => 'company', 'email' => 'direction@ivoirepalace.ci', 'phone' => '+225 27 23 44 55 66', 'city' => 'Abidjan', 'tax_id' => 'CI-ABJ-2019-B-44521'],
            ['name' => 'BTP Koné & Frères SARL', 'type' => 'company', 'email' => 'contact@btpkone.ci', 'phone' => '+225 05 77 88 99 00', 'city' => 'Abidjan'],
            ['name' => 'Clinique Mère & Enfant', 'type' => 'company', 'email' => 'admin@clinique-me.ci', 'phone' => '+225 27 22 33 44 55', 'city' => 'Yamoussoukro'],
            ['name' => 'Traoré Mariam', 'type' => 'individual', 'email' => 'mariam.traore@yahoo.fr', 'phone' => '+225 07 55 44 33 22', 'city' => 'San-Pédro'],
            ['name' => 'Agence Immo Prestige', 'type' => 'company', 'email' => 'info@immoprestige.ci', 'phone' => '+225 27 25 66 77 88', 'city' => 'Abidjan'],
            ['name' => 'École Privée Les Étoiles', 'type' => 'company', 'email' => 'secretariat@ecoleetoiles.ci', 'phone' => '+225 27 23 55 66 77', 'city' => 'Abidjan'],
            ['name' => 'Groupe Transport Sécurifleet', 'type' => 'company', 'email' => 'ops@securifleet.ci', 'phone' => '+225 05 99 00 11 22', 'city' => 'Abidjan'],
        ];

        $customers = [];
        foreach ($data as $row) {
            $customers[] = Customer::firstOrCreate(
                ['company_id' => $company->id, 'name' => $row['name']],
                array_merge($row, ['currency' => 'XOF'])
            );
        }
        return $customers;
    }

    private function seedProducts(Company $company): array
    {
        $data = [
            ['name' => 'Ordinateur portable Dell Inspiron 15', 'type' => 'product', 'sku' => 'DELL-INS-15', 'unit' => 'unité', 'price' => 350000, 'cost' => 265000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 12, 'stock_alert_threshold' => 3],
            ['name' => 'Imprimante HP LaserJet Pro', 'type' => 'product', 'sku' => 'HP-LJ-PRO', 'unit' => 'unité', 'price' => 185000, 'cost' => 140000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 5, 'stock_alert_threshold' => 2],
            ['name' => 'Maintenance informatique (forfait/heure)', 'type' => 'service', 'sku' => 'SRV-MAINT-H', 'unit' => 'heure', 'price' => 15000, 'cost' => 0, 'tax_rate' => 18],
            ['name' => 'Installation réseau LAN', 'type' => 'service', 'sku' => 'SRV-LAN-INSTALL', 'unit' => 'prestation', 'price' => 250000, 'cost' => 0, 'tax_rate' => 18],
            ['name' => 'Clé USB 32Go Kingston', 'type' => 'product', 'sku' => 'USB-32G', 'unit' => 'unité', 'price' => 4500, 'cost' => 2800, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 50, 'stock_alert_threshold' => 10],
            ['name' => 'Switch réseau 24 ports', 'type' => 'product', 'sku' => 'SWT-24P', 'unit' => 'unité', 'price' => 95000, 'cost' => 72000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 4, 'stock_alert_threshold' => 1],
            ['name' => 'Formation bureautique (MS Office)', 'type' => 'service', 'sku' => 'FORM-OFFICE', 'unit' => 'session', 'price' => 120000, 'cost' => 0, 'tax_rate' => 18],
            ['name' => 'Onduleur APC 1500VA', 'type' => 'product', 'sku' => 'OND-APC-1500', 'unit' => 'unité', 'price' => 185000, 'cost' => 140000, 'tax_rate' => 18, 'track_stock' => true, 'stock_quantity' => 3, 'stock_alert_threshold' => 1],
            ['name' => 'Abonnement antivirus entreprise (1 an)', 'type' => 'service', 'sku' => 'AV-ENT-1AN', 'unit' => 'licence', 'price' => 35000, 'cost' => 20000, 'tax_rate' => 18],
            ['name' => 'Support technique mensuel', 'type' => 'service', 'sku' => 'SRV-SUPPORT-M', 'unit' => 'mois', 'price' => 75000, 'cost' => 0, 'tax_rate' => 18],
        ];

        $products = [];
        foreach ($data as $row) {
            $products[] = Product::firstOrCreate(
                ['company_id' => $company->id, 'sku' => $row['sku']],
                array_merge($row, ['company_id' => $company->id, 'is_active' => true])
            );
        }
        return $products;
    }

    private function seedDocuments(User $admin, Company $company, array $customers, array $products): void
    {
        // Utilise les modèles Document/Invoice si disponibles
        if (! class_exists(\App\Models\Document::class)) {
            return;
        }

        $docClass = \App\Models\Document::class;

        $scenarios = [
            // Factures payées
            ['type' => 'invoice', 'status' => 'paid', 'customer' => 0, 'items' => [[0, 2], [2, 5]], 'days_ago' => 45],
            ['type' => 'invoice', 'status' => 'paid', 'customer' => 1, 'items' => [[1, 1], [5, 2]], 'days_ago' => 30],
            ['type' => 'invoice', 'status' => 'paid', 'customer' => 3, 'items' => [[3, 1], [9, 3]], 'days_ago' => 20],
            ['type' => 'invoice', 'status' => 'paid', 'customer' => 7, 'items' => [[6, 2], [8, 1]], 'days_ago' => 15],
            // Factures en attente
            ['type' => 'invoice', 'status' => 'sent', 'customer' => 4, 'items' => [[0, 3], [7, 1]], 'days_ago' => 5],
            ['type' => 'invoice', 'status' => 'sent', 'customer' => 2, 'items' => [[2, 10], [4, 5]], 'days_ago' => 3],
            ['type' => 'invoice', 'status' => 'overdue', 'customer' => 5, 'items' => [[3, 1]], 'days_ago' => 40],
            // Devis
            ['type' => 'quote', 'status' => 'sent', 'customer' => 6, 'items' => [[1, 2], [3, 1]], 'days_ago' => 7],
            ['type' => 'quote', 'status' => 'accepted', 'customer' => 8, 'items' => [[6, 5], [9, 2]], 'days_ago' => 12],
            ['type' => 'quote', 'status' => 'draft', 'customer' => 9, 'items' => [[0, 1], [5, 1]], 'days_ago' => 2],
        ];

        $docNumber = 1;
        foreach ($scenarios as $s) {
            $customer = $customers[$s['customer']] ?? $customers[0];
            $date = now()->subDays($s['days_ago']);
            $dueDate = $date->copy()->addDays(30);

            // Calculer les lignes
            $lines = [];
            $subtotal = 0;
            foreach ($s['items'] as [$pi, $qty]) {
                $product = $products[$pi] ?? $products[0];
                $lineTotal = $product->price * $qty;
                $subtotal += $lineTotal;
                $lines[] = [
                    'product_id'   => $product->id,
                    'description'  => $product->name,
                    'quantity'     => $qty,
                    'unit_price'   => $product->price,
                    'tax_rate'     => $product->tax_rate ?? 18,
                    'total'        => $lineTotal,
                ];
            }
            $taxAmount = round($subtotal * 0.18);
            $total = $subtotal + $taxAmount;

            $prefix = $s['type'] === 'invoice' ? 'FACT' : 'DEV';
            $number = $prefix . '-2025-' . str_pad($docNumber++, 4, '0', STR_PAD_LEFT);

            // Éviter les doublons
            if ($docClass::where('company_id', $company->id)->where('number', $number)->exists()) {
                continue;
            }

            try {
                $doc = $docClass::create([
                    'company_id'  => $company->id,
                    'created_by'  => $admin->id,
                    'customer_id' => $customer->id,
                    'type'        => $s['type'],
                    'status'      => $s['status'],
                    'number'      => $number,
                    'issue_date'  => $date->toDateString(),
                    'due_date'    => $dueDate->toDateString(),
                    'currency'    => 'XOF',
                    'subtotal'    => $subtotal,
                    'tax_amount'  => $taxAmount,
                    'total'       => $total,
                    'amount_paid' => in_array($s['status'], ['paid']) ? $total : 0,
                    'notes'       => null,
                ]);

                // Créer les lignes dans document_lines
                foreach ($lines as $sortOrder => $lineData) {
                    \App\Models\DocumentLine::create([
                        'document_id' => $doc->id,
                        'product_id'  => $lineData['product_id'],
                        'description' => $lineData['description'],
                        'quantity'    => $lineData['quantity'],
                        'unit'        => 'unité',
                        'unit_price'  => $lineData['unit_price'],
                        'tax_rate'    => $lineData['tax_rate'],
                        'line_total'  => $lineData['total'],
                        'sort_order'  => $sortOrder,
                    ]);
                }
            } catch (\Throwable $e) {
                $this->command->warn('Document ignoré: ' . $e->getMessage());
            }
        }
    }
}
