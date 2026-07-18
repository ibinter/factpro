<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    from: String,
    to: String,
    canExport: Boolean,
    currency: String,
    kpis: Object,
    revenueByMonth: Object,
    topCustomers: Array,
    topProducts: Array,
    salesByType: Array,
    quoteConversion: Object,
    paymentsByMethod: Object,
});

const from = ref(props.from);
const to = ref(props.to);

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);
const fmtQty = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(n ?? 0);

const apply = () => {
    router.get(route('reports.index'), { from: from.value, to: to.value }, { preserveScroll: true });
};

const pad = (n) => String(n).padStart(2, '0');
const iso = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

const setRange = (range) => {
    const now = new Date();
    let start;
    let end = new Date(now.getFullYear(), now.getMonth() + 1, 0);

    if (range === 'month') {
        start = new Date(now.getFullYear(), now.getMonth(), 1);
    } else if (range === 'quarter') {
        const q = Math.floor(now.getMonth() / 3) * 3;
        start = new Date(now.getFullYear(), q, 1);
        end = new Date(now.getFullYear(), q + 3, 0);
    } else if (range === 'year') {
        start = new Date(now.getFullYear(), 0, 1);
        end = new Date(now.getFullYear(), 11, 31);
    } else {
        start = new Date(now.getFullYear(), now.getMonth() - 11, 1);
    }

    from.value = iso(start);
    to.value = iso(end);
    apply();
};

const shortcuts = [
    { key: 'month', label: 'Ce mois' },
    { key: 'quarter', label: 'Ce trimestre' },
    { key: 'year', label: 'Cette année' },
    { key: '12m', label: '12 mois' },
];

const maxMonth = computed(() =>
    Math.max(...props.revenueByMonth.months.map((m) => Math.abs(m.total)), 1)
);

const maxCustomer = computed(() =>
    Math.max(...props.topCustomers.map((c) => Math.abs(c.revenue)), 1)
);
const maxProduct = computed(() =>
    Math.max(...props.topProducts.map((p) => Math.abs(p.revenue)), 1)
);

const paymentsTotal = computed(() =>
    props.paymentsByMethod.reduce((sum, m) => sum + m.amount, 0)
);

const datasets = [
    { key: 'documents', label: 'Documents', icon: '📄', period: true },
    { key: 'customers', label: 'Clients', icon: '👥', period: false },
    { key: 'products', label: 'Produits', icon: '📦', period: false },
    { key: 'payments', label: 'Paiements', icon: '💰', period: true },
];

const exportUrl = (dataset) =>
    route('reports.export', { dataset, from: from.value, to: to.value });

const excelExportUrl = (dataset) => {
    const map = {
        documents: route('export.excel.documents', { from: from.value, to: to.value }),
        customers: route('export.excel.customers'),
        products:  route('export.excel.products'),
        payments:  route('export.excel.documents', { from: from.value, to: to.value }),
    };
    return map[dataset] ?? null;
};
</script>

<template>
    <Head title="Rapports & analytiques" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Rapports & analytiques</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Sélecteur de période -->
                <div class="rounded-lg bg-white p-4 shadow">
                    <div class="flex flex-wrap items-end gap-3">
                        <div>
                            <label for="report-from" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Du</label>
                            <input id="report-from" v-model="from" type="date"
                                class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-600 focus:ring-brand-600" />
                        </div>
                        <div>
                            <label for="report-to" class="block text-xs font-semibold uppercase tracking-wide text-gray-500">Au</label>
                            <input id="report-to" v-model="to" type="date"
                                class="mt-1 rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-600 focus:ring-brand-600" />
                        </div>
                        <button type="button" @click="apply"
                            class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                            Appliquer
                        </button>
                        <div class="ml-auto flex flex-wrap gap-2">
                            <button v-for="s in shortcuts" :key="s.key" type="button" @click="setRange(s.key)"
                                class="rounded-full border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:border-brand-600 hover:text-brand-600">
                                {{ s.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
                    <div class="rounded-lg bg-gradient-to-br from-brand-900 to-brand-600 p-5 text-white shadow">
                        <div class="text-xs uppercase tracking-wide opacity-75">CA de la période</div>
                        <div class="mt-1 text-2xl font-bold">{{ fmt(kpis.revenue) }} <span class="text-sm font-normal">{{ currency }}</span></div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Encaissé</div>
                        <div class="mt-1 text-2xl font-bold text-green-600">{{ fmt(kpis.collected) }} <span class="text-sm font-normal text-gray-400">{{ currency }}</span></div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Encours</div>
                        <div class="mt-1 text-2xl font-bold text-red-600">{{ fmt(kpis.outstanding) }} <span class="text-sm font-normal text-gray-400">{{ currency }}</span></div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Panier moyen</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">{{ fmt(kpis.average_basket) }} <span class="text-sm font-normal text-gray-400">{{ currency }}</span></div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Nouveaux clients</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">{{ kpis.new_customers }}</div>
                    </div>
                </div>

                <!-- CA par mois -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">Chiffre d'affaires par mois</h3>
                        <div class="text-sm text-gray-500">
                            Total : <span class="font-bold text-gray-800">{{ fmt(revenueByMonth.total) }} {{ currency }}</span>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <div class="flex h-48 min-w-full items-end gap-2" :style="{ minWidth: revenueByMonth.months.length * 56 + 'px' }">
                            <div v-for="m in revenueByMonth.months" :key="m.key" class="flex flex-1 flex-col items-center gap-1">
                                <div class="text-xs font-semibold" :class="m.total < 0 ? 'text-red-600' : 'text-gray-600'">{{ fmt(m.total) }}</div>
                                <div
                                    class="w-full rounded-t transition-all"
                                    :class="m.total < 0 ? 'bg-gradient-to-t from-red-600 to-red-400' : 'bg-gradient-to-t from-brand-700 to-brand-500'"
                                    :style="{ height: Math.max((Math.abs(m.total) / maxMonth) * 140, 4) + 'px' }"
                                ></div>
                                <div class="whitespace-nowrap text-xs text-gray-500">{{ m.label }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top clients / Top produits -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <h3 class="border-b px-6 py-4 font-semibold text-gray-800">Top 10 clients</h3>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Client</th>
                                    <th class="px-4 py-3 text-right">Docs</th>
                                    <th class="px-4 py-3 text-right">CA TTC</th>
                                    <th class="px-6 py-3 text-right">Encours</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="c in topCustomers" :key="c.customer_id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <div class="font-semibold text-gray-800">{{ c.name }}</div>
                                        <div class="mt-1 h-1.5 w-full max-w-[160px] rounded-full bg-gray-100">
                                            <div class="h-1.5 rounded-full bg-gradient-to-r from-brand-700 to-brand-500"
                                                :style="{ width: Math.max((Math.abs(c.revenue) / maxCustomer) * 100, 2) + '%' }"></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-500">{{ c.documents_count }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ fmt(c.revenue) }}</td>
                                    <td class="px-6 py-3 text-right" :class="c.outstanding > 0 ? 'font-semibold text-red-600' : 'text-gray-400'">{{ fmt(c.outstanding) }}</td>
                                </tr>
                                <tr v-if="!topCustomers.length">
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-400">Aucune vente sur la période.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <h3 class="border-b px-6 py-4 font-semibold text-gray-800">Top 10 produits</h3>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Produit</th>
                                    <th class="px-4 py-3 text-right">Quantité</th>
                                    <th class="px-6 py-3 text-right">CA HT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="p in topProducts" :key="p.product_id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <div class="font-semibold text-gray-800">{{ p.name }}</div>
                                        <div class="mt-1 h-1.5 w-full max-w-[160px] rounded-full bg-gray-100">
                                            <div class="h-1.5 rounded-full bg-gold-400"
                                                :style="{ width: Math.max((Math.abs(p.revenue) / maxProduct) * 100, 2) + '%' }"></div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-500">{{ fmtQty(p.quantity) }}</td>
                                    <td class="px-6 py-3 text-right font-semibold">{{ fmt(p.revenue) }}</td>
                                </tr>
                                <tr v-if="!topProducts.length">
                                    <td colspan="3" class="px-6 py-10 text-center text-gray-400">Aucune ligne produit sur la période.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Ventes par type / Encaissements par moyen -->
                <div class="grid gap-6 lg:grid-cols-2">
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <h3 class="border-b px-6 py-4 font-semibold text-gray-800">Ventes par type de document</h3>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Type</th>
                                    <th class="px-4 py-3 text-right">Nombre</th>
                                    <th class="px-6 py-3 text-right">Montant TTC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="t in salesByType" :key="t.type" class="hover:bg-gray-50">
                                    <td class="px-6 py-3 font-semibold text-gray-800">{{ t.label }}</td>
                                    <td class="px-4 py-3 text-right text-gray-500">{{ t.documents_count }}</td>
                                    <td class="px-6 py-3 text-right font-semibold">{{ fmt(t.total) }} {{ currency }}</td>
                                </tr>
                                <tr v-if="!salesByType.length">
                                    <td colspan="3" class="px-6 py-10 text-center text-gray-400">Aucun document finalisé sur la période.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="space-y-6">
                        <div class="overflow-hidden rounded-lg bg-white shadow">
                            <h3 class="border-b px-6 py-4 font-semibold text-gray-800">Encaissements par moyen</h3>
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                    <tr>
                                        <th class="px-6 py-3">Moyen</th>
                                        <th class="px-4 py-3 text-right">Nombre</th>
                                        <th class="px-4 py-3 text-right">Montant</th>
                                        <th class="px-6 py-3 text-right">Part</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="m in paymentsByMethod" :key="m.method" class="hover:bg-gray-50">
                                        <td class="px-6 py-3 font-semibold text-gray-800">{{ m.label }}</td>
                                        <td class="px-4 py-3 text-right text-gray-500">{{ m.payments_count }}</td>
                                        <td class="px-4 py-3 text-right font-semibold">{{ fmt(m.amount) }} {{ currency }}</td>
                                        <td class="px-6 py-3 text-right text-gray-500">{{ paymentsTotal > 0 ? Math.round((m.amount / paymentsTotal) * 100) : 0 }} %</td>
                                    </tr>
                                    <tr v-if="!paymentsByMethod.length">
                                        <td colspan="4" class="px-6 py-10 text-center text-gray-400">Aucun encaissement sur la période.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Conversion devis -->
                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3 class="font-semibold text-gray-800">Conversion des devis</h3>
                            <div class="mt-3 flex items-center gap-6">
                                <div class="text-4xl font-bold text-brand-600">{{ quoteConversion.rate }} %</div>
                                <div class="text-sm text-gray-500">
                                    <div><span class="font-semibold text-gray-800">{{ quoteConversion.converted }}</span> devis converti(s) sur <span class="font-semibold text-gray-800">{{ quoteConversion.total }}</span></div>
                                    <div v-if="quoteConversion.avg_days !== null" class="mt-1">
                                        Délai moyen de conversion : <span class="font-semibold text-gray-800">{{ quoteConversion.avg_days }} jour(s)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 h-2 w-full rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-gradient-to-r from-brand-700 to-brand-500" :style="{ width: Math.min(quoteConversion.rate, 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exports -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-800">📥 Exports</h3>
                        <span v-if="!canExport" class="text-xs text-gray-500">
                            🔒 Réservé aux forfaits PRO et supérieurs —
                            <Link :href="route('billing.plans')" class="font-semibold text-brand-600 hover:underline">changer de forfait</Link>
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">CSV (séparateur « ; », UTF-8) ou Excel natif (.xlsx). Documents et paiements sont limités à la période sélectionnée. Les exports Excel nécessitent un forfait BUSINESS ou supérieur.</p>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <template v-for="d in datasets" :key="d.key">
                            <div class="flex flex-col gap-2">
                                <!-- CSV -->
                                <a v-if="canExport"
                                    :href="exportUrl(d.key)"
                                    target="_blank"
                                    class="flex items-center justify-center gap-2 rounded-md border border-brand-600 px-4 py-3 text-sm font-semibold text-brand-600 hover:bg-brand-50">
                                    <span>{{ d.icon }}</span> {{ d.label }} CSV
                                </a>
                                <button v-else type="button" disabled
                                    class="flex cursor-not-allowed items-center justify-center gap-2 rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-400">
                                    <span>🔒</span> {{ d.label }} CSV
                                </button>
                                <!-- Excel -->
                                <a v-if="canExport && excelExportUrl(d.key)"
                                    :href="excelExportUrl(d.key)"
                                    target="_blank"
                                    class="flex items-center justify-center gap-2 rounded-md border border-green-600 px-4 py-3 text-sm font-semibold text-green-700 hover:bg-green-50">
                                    📊 {{ d.label }} Excel
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
