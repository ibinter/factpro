<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref } from 'vue'

const activeSection = ref('install')

const installCode = `npm install @ibigsoft/factpro-sdk`

const quickStartCode = `import { FactProClient } from '@ibigsoft/factpro-sdk';

const client = new FactProClient({
    apiKey: 'votre-cle-api-sanctum',
    // baseUrl: 'https://app.factpro.ibigsoft.com', // par défaut
});

// Tester la connexion
const me = await client.me();
console.log('Connecté :', me.data.user.email);`

const documentsCode = `// Lister les documents
const docs = await client.documents.list({
    type: 'invoice',
    status: 'final',
    page: 1,
});

// Créer une facture
const invoice = await client.documents.create({
    type: 'invoice',
    customer_id: 42,
    issue_date: '2026-01-15',
    currency: 'XOF',
    items: [{
        description: 'Prestation de conseil',
        quantity: 5,
        unit_price: 30000,
        tax_rate: 18,
    }],
});

// Finaliser et télécharger le PDF
await client.documents.finalize(invoice.data.uuid);
const pdfBytes = await client.documents.downloadPdf(invoice.data.uuid);

// En navigateur :
const blob = new Blob([pdfBytes], { type: 'application/pdf' });
const url = URL.createObjectURL(blob);
window.open(url);`

const customersCode = `// Lister les clients
const list = await client.customers.list({ search: 'acme' });

// Créer un client
const customer = await client.customers.create({
    name:  'ACME Corp',
    email: 'contact@acme.com',
    phone: '+225 07 00 00 00 00',
    type:  'company',
});

// Récupérer les factures d'un client
const invoices = await client.customers.getInvoices(customer.data.id);

// Consulter le solde en attente
const balance = await client.customers.getBalance(customer.data.id);
console.log('Solde :', balance.data.pending_amount, 'XOF');`

const productsCode = `// Créer un produit
const product = await client.products.create({
    name:       'Abonnement mensuel',
    unit_price: 25000,
    unit:       'mois',
    tax_rate:   18,
});

// Ajuster le stock
await client.products.adjustStock(product.data.id, {
    quantity: -5,
    reason: 'Vente comptoir',
});`

const invoicesCode = `// Factures en retard
const overdue = await client.invoices.list({ overdue: true });

// Enregistrer un paiement
await client.invoices.registerPayment(uuid, {
    amount: 150000,
    method: 'mobile_money',
    date:   '2026-01-20',
});

// Envoyer une relance
await client.invoices.sendReminder(uuid);

// Marquer comme payée
await client.invoices.markAsPaid(uuid);`

const errorsCode = `import { FactProClient, AuthError, ValidationError, FactProError } from '@ibigsoft/factpro-sdk';

try {
    await client.documents.create({});
} catch (e) {
    if (e instanceof AuthError) {
        // HTTP 401 — token invalide ou expiré
        console.error('Clé API invalide');
    } else if (e instanceof ValidationError) {
        // HTTP 422 — données invalides
        for (const [field, msgs] of Object.entries(e.errors)) {
            console.error(\`\${field}: \${msgs.join(', ')}\`);
        }
    } else if (e instanceof FactProError) {
        // Toute autre erreur API
        console.error(\`Erreur \${e.status}: \${e.message}\`);
    }
}`

const sections = [
    { id: 'install',   label: 'Installation' },
    { id: 'quickstart', label: 'Quick Start' },
    { id: 'documents', label: 'Documents' },
    { id: 'customers', label: 'Clients' },
    { id: 'products',  label: 'Produits' },
    { id: 'invoices',  label: 'Factures' },
    { id: 'errors',    label: 'Erreurs' },
]
</script>

<template>
    <AppLayout title="SDK JavaScript">
        <div class="max-w-6xl mx-auto px-4 py-8">

            <!-- En-tête -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-3">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">SDK JavaScript</h1>
                    <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">ESM</span>
                    <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Node 18+</span>
                    <span class="px-2 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Browser</span>
                </div>
                <p class="text-gray-600 dark:text-gray-400">
                    SDK JavaScript officiel pour l'API FactPro. Vanilla ES2020+, aucune dépendance,
                    compatible Node.js 18+ et navigateurs modernes.
                </p>
            </div>

            <div class="flex gap-6">
                <!-- Navigation latérale -->
                <nav class="hidden md:flex flex-col gap-1 w-44 shrink-0">
                    <button
                        v-for="s in sections"
                        :key="s.id"
                        @click="activeSection = s.id"
                        class="text-left px-3 py-2 rounded-lg text-sm font-medium transition"
                        :class="activeSection === s.id
                            ? 'bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300'
                            : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                    >
                        {{ s.label }}
                    </button>
                </nav>

                <!-- Contenu principal -->
                <div class="flex-1 min-w-0 space-y-6">

                    <!-- Installation -->
                    <section v-show="activeSection === 'install'">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Installation</h2>
                        <div class="rounded-lg bg-gray-900 dark:bg-gray-950 p-5">
                            <p class="text-xs text-gray-400 mb-2 font-mono">npm</p>
                            <pre class="text-green-400 font-mono text-sm">{{ installCode }}</pre>
                        </div>
                        <div class="mt-4 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-5 space-y-3">
                            <h3 class="font-semibold text-gray-900 dark:text-white">Configuration</h3>
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                                        <th class="pb-2 pr-4">Option</th>
                                        <th class="pb-2 pr-4">Type</th>
                                        <th class="pb-2 pr-4">Défaut</th>
                                        <th class="pb-2">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <tr v-for="opt in [
                                        { name: 'apiKey', type: 'string', default: '—', desc: 'Token Sanctum (requis)' },
                                        { name: 'baseUrl', type: 'string', default: 'app.factpro.ibigsoft.com', desc: 'URL de votre instance' },
                                        { name: 'timeout', type: 'number', default: '10000', desc: 'Timeout en ms' },
                                        { name: 'retries', type: 'number', default: '3', desc: 'Retries sur 429/503' },
                                    ]" :key="opt.name" class="text-gray-700 dark:text-gray-300">
                                        <td class="py-2 pr-4"><code class="text-indigo-600 dark:text-indigo-400">{{ opt.name }}</code></td>
                                        <td class="py-2 pr-4 text-gray-500">{{ opt.type }}</td>
                                        <td class="py-2 pr-4 text-gray-500 text-xs">{{ opt.default }}</td>
                                        <td class="py-2">{{ opt.desc }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Quick Start -->
                    <section v-show="activeSection === 'quickstart'">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Quick Start</h2>
                        <div class="rounded-lg bg-gray-900 dark:bg-gray-950 p-5">
                            <p class="text-xs text-gray-400 mb-2 font-mono">JavaScript (ESM)</p>
                            <pre class="text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap">{{ quickStartCode }}</pre>
                        </div>
                    </section>

                    <!-- Documents -->
                    <section v-show="activeSection === 'documents'">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">client.documents</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Devis, factures, avoirs, bons de livraison</p>
                        <div class="rounded-lg bg-gray-900 dark:bg-gray-950 p-5 mb-4">
                            <pre class="text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap">{{ documentsCode }}</pre>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Méthodes disponibles</p>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 font-mono">
                                <li>list({ page, type, status, customer_id, search })</li>
                                <li>get(uuid)</li>
                                <li>create(data)</li>
                                <li>update(uuid, data)</li>
                                <li>finalize(uuid)</li>
                                <li>send(uuid, { email, message })</li>
                                <li>downloadPdf(uuid) → Uint8Array</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Clients -->
                    <section v-show="activeSection === 'customers'">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">client.customers</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Gestion des clients et prospects</p>
                        <div class="rounded-lg bg-gray-900 dark:bg-gray-950 p-5 mb-4">
                            <pre class="text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap">{{ customersCode }}</pre>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Méthodes disponibles</p>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 font-mono">
                                <li>list({ page, search, type })</li>
                                <li>get(id)</li>
                                <li>create(data)</li>
                                <li>update(id, data)</li>
                                <li>getInvoices(id)</li>
                                <li>getBalance(id)</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Produits -->
                    <section v-show="activeSection === 'products'">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">client.products</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Catalogue produits et services</p>
                        <div class="rounded-lg bg-gray-900 dark:bg-gray-950 p-5 mb-4">
                            <pre class="text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap">{{ productsCode }}</pre>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Méthodes disponibles</p>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 font-mono">
                                <li>list({ page, search, category })</li>
                                <li>get(id)</li>
                                <li>create(data)</li>
                                <li>update(id, data)</li>
                                <li>adjustStock(id, { quantity, reason })</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Factures -->
                    <section v-show="activeSection === 'invoices'">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">client.invoices</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Raccourci spécialisé pour la gestion des factures</p>
                        <div class="rounded-lg bg-gray-900 dark:bg-gray-950 p-5 mb-4">
                            <pre class="text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap">{{ invoicesCode }}</pre>
                        </div>
                        <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Méthodes disponibles</p>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1 font-mono">
                                <li>list({ page, status, overdue })</li>
                                <li>get(uuid)</li>
                                <li>registerPayment(uuid, { amount, method, date })</li>
                                <li>sendReminder(uuid)</li>
                                <li>markAsPaid(uuid)</li>
                            </ul>
                        </div>
                    </section>

                    <!-- Erreurs -->
                    <section v-show="activeSection === 'errors'">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Gestion des erreurs</h2>
                        <div class="rounded-lg bg-gray-900 dark:bg-gray-950 p-5 mb-4">
                            <pre class="text-blue-300 font-mono text-sm overflow-x-auto whitespace-pre-wrap">{{ errorsCode }}</pre>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div v-for="err in [
                                { name: 'FactProError', status: '4xx/5xx', desc: 'Erreur de base. Propriétés : message, status, code.' },
                                { name: 'AuthError', status: '401', desc: 'Token invalide ou expiré. Extends FactProError.' },
                                { name: 'ValidationError', status: '422', desc: 'Données invalides. Propriété .errors par champ.' },
                            ]" :key="err.name" class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4">
                                <code class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ err.name }}</code>
                                <span class="ml-2 text-xs text-gray-400">HTTP {{ err.status }}</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ err.desc }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- Liens -->
                    <div class="pt-4 flex gap-3 flex-wrap">
                        <a
                            href="https://www.npmjs.com/package/@ibigsoft/factpro-sdk"
                            target="_blank"
                            class="inline-flex items-center px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition text-sm"
                        >
                            npm package
                        </a>
                        <a
                            href="/api/openapi.json"
                            download="factpro-openapi.json"
                            class="inline-flex items-center px-5 py-2.5 bg-gray-700 hover:bg-gray-800 text-white font-semibold rounded-lg transition text-sm"
                        >
                            Spec OpenAPI
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
