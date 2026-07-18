<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    stats: Object,
    recentDocuments: Array,
    chart: Array,
    monthlyRevenue: Array,
    topCustomers: Array,
    topProducts: Array,
    alerts: Array,
    conversionRate: Number,
});

const page = usePage();
const currency = computed(() => page.props.company?.currency ?? 'XOF');

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);
const fmt2 = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(n ?? 0);

// Multi-devises : équivalents EUR/USD du CA du mois (partage Inertia "rates")
const rates = computed(() => page.props.rates ?? null);
const revenueEur = computed(() =>
    rates.value?.eur ? (props.stats.revenue_month ?? 0) * rates.value.eur : null,
);
const revenueUsd = computed(() =>
    rates.value?.usd ? (props.stats.revenue_month ?? 0) * rates.value.usd : null,
);

const maxChart = computed(() => Math.max(...props.chart.map((c) => c.total), 1));

// 12 mois bar chart SVG
const maxMonthly = computed(() => Math.max(...(props.monthlyRevenue ?? []).map((m) => m.revenue), 1));
const hoveredBar = ref(null);

const statusLabels = {
    draft: 'Brouillon', sent: 'Envoyé', viewed: 'Vu', accepted: 'Accepté',
    rejected: 'Refusé', partial: 'Partiel', paid: 'Payé', overdue: 'En retard',
    cancelled: 'Annulé', converted: 'Converti',
};
const statusColors = {
    draft: 'bg-gray-100 text-gray-700', sent: 'bg-blue-100 text-blue-700',
    viewed: 'bg-indigo-100 text-indigo-700', accepted: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700', partial: 'bg-amber-100 text-amber-700',
    paid: 'bg-green-100 text-green-700', overdue: 'bg-red-100 text-red-700',
    cancelled: 'bg-gray-100 text-gray-500', converted: 'bg-purple-100 text-purple-700',
};

const typeLabels = {
    quote: 'Devis', proforma: 'Proforma', sales_order: 'BC', purchase_order: 'BCF',
    delivery_note: 'BL', invoice: 'Facture', credit_note: 'Avoir',
    payment_receipt: 'Reçu', deposit_invoice: 'Acompte', balance_invoice: 'Solde',
    work_order: 'BT', pos_ticket: 'Ticket',
};

// Alertes
const alertsOpen = ref(true);
const alertSeverityClass = (severity) => ({
    danger: 'bg-red-50 border-red-200 text-red-700',
    warning: 'bg-amber-50 border-amber-200 text-amber-700',
    info: 'bg-blue-50 border-blue-200 text-blue-700',
}[severity] ?? 'bg-gray-50 border-gray-200 text-gray-700');

const alertIconClass = (severity) => ({
    danger: 'text-red-500',
    warning: 'text-amber-500',
    info: 'text-blue-500',
}[severity] ?? 'text-gray-500');

const alertLabel = (severity) => ({
    danger: 'Critique',
    warning: 'Attention',
    info: 'Info',
}[severity] ?? severity);

// SVG bar chart helpers
const chartWidth = 560;
const chartHeight = 160;
const barPad = 6;
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Tableau de bord</h2>
                <div class="flex gap-2">
                    <Link
                        :href="route('documents.create', { type: 'quote' })"
                        class="rounded-md border border-brand-600 px-4 py-2 text-sm font-semibold text-brand-600 hover:bg-brand-50"
                    >
                        + Devis
                    </Link>
                    <Link
                        :href="route('documents.create', { type: 'invoice' })"
                        class="rounded-md bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                    >
                        + Facture
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <!-- Alertes -->
                <div v-if="alerts && alerts.length > 0" class="rounded-lg border bg-white shadow-sm overflow-hidden">
                    <button
                        class="flex w-full items-center justify-between px-5 py-3 text-left hover:bg-gray-50"
                        @click="alertsOpen = !alertsOpen"
                    >
                        <span class="flex items-center gap-2 font-semibold text-gray-800">
                            <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            {{ alerts.length }} alerte{{ alerts.length > 1 ? 's' : '' }} active{{ alerts.length > 1 ? 's' : '' }}
                        </span>
                        <svg :class="['h-4 w-4 text-gray-400 transition-transform', alertsOpen ? 'rotate-180' : '']"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div v-if="alertsOpen" class="divide-y border-t">
                        <div v-for="alert in alerts" :key="alert.type"
                            class="flex items-center justify-between gap-3 px-5 py-3"
                            :class="alertSeverityClass(alert.severity)"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="h-4 w-4 flex-shrink-0" :class="alertIconClass(alert.severity)"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                <span class="text-sm">
                                    <span class="font-semibold mr-1">{{ alertLabel(alert.severity) }} :</span>
                                    {{ alert.message }}
                                </span>
                            </div>
                            <a v-if="alert.link" :href="alert.link"
                                class="flex-shrink-0 text-xs font-semibold underline hover:no-underline">
                                Voir →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- KPIs -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4" :class="conversionRate > 0 ? 'lg:grid-cols-5' : 'lg:grid-cols-4'">
                    <div class="rounded-lg bg-gradient-to-br from-brand-900 to-brand-600 p-5 text-white shadow">
                        <div class="text-xs uppercase tracking-wide opacity-75">CA du mois</div>
                        <div class="mt-1 text-2xl font-bold">{{ fmt(stats.revenue_month) }} <span class="text-sm font-normal">{{ currency }}</span></div>
                        <div v-if="revenueEur !== null || revenueUsd !== null" class="mt-1 text-xs opacity-75">
                            ≈
                            <span v-if="revenueEur !== null">{{ fmt2(revenueEur) }} €</span>
                            <span v-if="revenueEur !== null && revenueUsd !== null"> · </span>
                            <span v-if="revenueUsd !== null">{{ fmt2(revenueUsd) }} $</span>
                        </div>
                        <div v-if="rates?.updated_at" class="mt-0.5 opacity-60" style="font-size: 10px">
                            Taux du {{ rates.updated_at }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Impayés</div>
                        <div class="mt-1 text-2xl font-bold text-red-600">{{ fmt(stats.outstanding) }} <span class="text-sm font-normal text-gray-400">{{ currency }}</span></div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Factures ce mois</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">{{ stats.invoices_month }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Devis en attente</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">{{ stats.quotes_pending }}</div>
                    </div>
                    <!-- KPI taux de conversion -->
                    <div v-if="conversionRate > 0" class="rounded-lg bg-white p-5 shadow border-l-4 border-brand-600">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Conversion devis</div>
                        <div class="mt-1 text-2xl font-bold text-brand-600">{{ conversionRate }}%</div>
                        <div class="mt-0.5 text-xs text-gray-400">30 derniers jours</div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Graphique CA 6 mois (ancien) -->
                    <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                        <h3 class="mb-4 font-semibold text-gray-800">Chiffre d'affaires — 6 derniers mois</h3>
                        <div class="flex h-48 items-end gap-3">
                            <div v-for="c in chart" :key="c.month" class="flex flex-1 flex-col items-center gap-1">
                                <div class="text-xs font-semibold text-gray-600">{{ fmt(c.total) }}</div>
                                <div
                                    class="w-full rounded-t bg-gradient-to-t from-brand-700 to-brand-500 transition-all"
                                    :style="{ height: Math.max((c.total / maxChart) * 150, 4) + 'px' }"
                                ></div>
                                <div class="text-xs text-gray-500">{{ c.month }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Compteurs -->
                    <div class="space-y-4">
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-gray-500">Clients</div>
                                    <div class="text-2xl font-bold text-gray-800">{{ stats.customers }}</div>
                                </div>
                                <Link :href="route('customers.index')" class="text-sm font-semibold text-brand-600 hover:underline">Gérer →</Link>
                            </div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs uppercase tracking-wide text-gray-500">Produits & services</div>
                                    <div class="text-2xl font-bold text-gray-800">{{ stats.products }}</div>
                                </div>
                                <Link :href="route('products.index')" class="text-sm font-semibold text-brand-600 hover:underline">Gérer →</Link>
                            </div>
                        </div>
                        <div class="rounded-lg border-2 border-dashed border-gold-400 bg-gold-400/5 p-5">
                            <div class="text-sm font-semibold text-gray-700">🔒 QR Anti-Falsification</div>
                            <p class="mt-1 text-xs text-gray-500">
                                Chaque document finalisé est scellé par un hash SHA-256 et vérifiable publiquement par QR code.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Graphique CA 12 mois SVG natif -->
                <div v-if="monthlyRevenue && monthlyRevenue.length > 0" class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 font-semibold text-gray-800">Évolution du CA — 12 derniers mois</h3>
                    <div class="relative overflow-x-auto">
                        <svg :viewBox="`0 0 ${chartWidth} ${chartHeight + 50}`" class="w-full" style="min-width:400px">
                            <defs>
                                <linearGradient id="barGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#0062CC" stop-opacity="1"/>
                                    <stop offset="100%" stop-color="#002D5B" stop-opacity="0.8"/>
                                </linearGradient>
                            </defs>
                            <!-- Axes horizontaux de référence -->
                            <line v-for="tick in [0, 0.25, 0.5, 0.75, 1]" :key="tick"
                                :x1="30" :y1="chartHeight - tick * chartHeight + 5"
                                :x2="chartWidth - 10" :y2="chartHeight - tick * chartHeight + 5"
                                stroke="#e5e7eb" stroke-width="1"
                            />
                            <!-- Barres -->
                            <g v-for="(m, i) in monthlyRevenue" :key="m.month">
                                <rect
                                    :x="30 + i * ((chartWidth - 40) / monthlyRevenue.length) + barPad / 2"
                                    :y="chartHeight - (m.revenue / maxMonthly) * (chartHeight - 10) + 5"
                                    :width="(chartWidth - 40) / monthlyRevenue.length - barPad"
                                    :height="Math.max((m.revenue / maxMonthly) * (chartHeight - 10), 2)"
                                    fill="url(#barGrad)"
                                    rx="2"
                                    class="cursor-pointer transition-opacity"
                                    :opacity="hoveredBar === i ? 1 : 0.85"
                                    @mouseover="hoveredBar = i"
                                    @mouseleave="hoveredBar = null"
                                />
                                <!-- Tooltip au survol -->
                                <g v-if="hoveredBar === i">
                                    <rect
                                        :x="Math.min(30 + i * ((chartWidth - 40) / monthlyRevenue.length) + barPad / 2 - 10, chartWidth - 130)"
                                        :y="chartHeight - (m.revenue / maxMonthly) * (chartHeight - 10) - 28"
                                        width="120" height="24" rx="4"
                                        fill="#002D5B" opacity="0.92"
                                    />
                                    <text
                                        :x="Math.min(30 + i * ((chartWidth - 40) / monthlyRevenue.length) + barPad / 2 + 50, chartWidth - 70)"
                                        :y="chartHeight - (m.revenue / maxMonthly) * (chartHeight - 10) - 11"
                                        text-anchor="middle" fill="white" font-size="10" font-weight="600"
                                    >
                                        {{ new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(m.revenue) }} XOF
                                    </text>
                                </g>
                                <!-- Labels mois -->
                                <text
                                    :x="30 + i * ((chartWidth - 40) / monthlyRevenue.length) + (chartWidth - 40) / monthlyRevenue.length / 2"
                                    :y="chartHeight + 22"
                                    text-anchor="middle"
                                    fill="#6b7280"
                                    font-size="9"
                                >{{ m.month.slice(0, 6) }}</text>
                            </g>
                        </svg>
                    </div>
                </div>

                <!-- Top clients + Top produits -->
                <div v-if="(topCustomers && topCustomers.length > 0) || (topProducts && topProducts.length > 0)"
                    class="grid gap-6 lg:grid-cols-2">
                    <!-- Top 5 clients -->
                    <div v-if="topCustomers && topCustomers.length > 0" class="rounded-lg bg-white shadow overflow-hidden">
                        <div class="flex items-center justify-between border-b px-5 py-4">
                            <h3 class="font-semibold text-gray-800">Top 5 clients (12 mois)</h3>
                            <Link :href="route('customers.index')" class="text-xs font-semibold text-brand-600 hover:underline">Tout voir →</Link>
                        </div>
                        <ul class="divide-y">
                            <li v-for="(customer, i) in topCustomers" :key="i"
                                class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
                                <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                    :class="i === 0 ? 'bg-yellow-100 text-yellow-700' : i === 1 ? 'bg-gray-100 text-gray-600' : 'bg-gray-50 text-gray-500'">
                                    {{ i + 1 }}
                                </span>
                                <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-800">{{ customer.name }}</span>
                                <span class="text-xs text-gray-400 mr-2">{{ customer.invoices_count }} fact.</span>
                                <span class="flex-shrink-0 text-sm font-bold text-brand-600">{{ fmt(customer.total) }} XOF</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Top 5 produits -->
                    <div v-if="topProducts && topProducts.length > 0" class="rounded-lg bg-white shadow overflow-hidden">
                        <div class="flex items-center justify-between border-b px-5 py-4">
                            <h3 class="font-semibold text-gray-800">Top 5 produits/services (12 mois)</h3>
                            <Link :href="route('products.index')" class="text-xs font-semibold text-brand-600 hover:underline">Tout voir →</Link>
                        </div>
                        <ul class="divide-y">
                            <li v-for="(product, i) in topProducts" :key="i"
                                class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
                                <span class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                    :class="i === 0 ? 'bg-yellow-100 text-yellow-700' : i === 1 ? 'bg-gray-100 text-gray-600' : 'bg-gray-50 text-gray-500'">
                                    {{ i + 1 }}
                                </span>
                                <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-800">{{ product.name }}</span>
                                <span class="text-xs text-gray-400 mr-2">qté {{ new Intl.NumberFormat('fr-FR', {maximumFractionDigits: 0}).format(product.quantity) }}</span>
                                <span class="flex-shrink-0 text-sm font-bold text-gray-700">{{ fmt(product.revenue) }} XOF</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Documents récents -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="flex items-center justify-between border-b px-6 py-4">
                        <h3 class="font-semibold text-gray-800">Documents récents</h3>
                        <Link :href="route('documents.index')" class="text-sm font-semibold text-brand-600 hover:underline">Tout voir →</Link>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Type</th>
                                <th class="px-6 py-3">Numéro</th>
                                <th class="px-6 py-3">Client</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3 text-right">Total</th>
                                <th class="px-6 py-3">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="doc in recentDocuments" :key="doc.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3 text-gray-500">{{ typeLabels[doc.type] ?? doc.type }}</td>
                                <td class="px-6 py-3">
                                    <Link :href="route('documents.show', doc.id)" class="font-semibold text-brand-600 hover:underline">
                                        {{ doc.number }}
                                    </Link>
                                </td>
                                <td class="px-6 py-3">{{ doc.customer?.name ?? '—' }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ doc.issue_date?.slice(0, 10) }}</td>
                                <td class="px-6 py-3 text-right font-semibold">{{ fmt(doc.total) }} {{ doc.currency }}</td>
                                <td class="px-6 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="statusColors[doc.status]">
                                        {{ statusLabels[doc.status] ?? doc.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!recentDocuments.length">
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                    Aucun document — créez votre premier devis ou facture !
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
