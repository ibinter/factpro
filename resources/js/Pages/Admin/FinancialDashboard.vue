<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    kpis: Object,
    chart12m: Array,
    byMethod: Array,
    byCountry: Array,
    byPlan: Array,
    byCurrency: Array,
    topClients: Array,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);
const pct = (n) => `${n ?? 0}%`;

const maxChart = computed(() => Math.max(...props.chart12m.map((c) => c.total), 1));
const maxCountry = computed(() => Math.max(...props.byCountry.map((c) => c.total), 1));
const maxPlan = computed(() => Math.max(...props.byPlan.map((p) => p.total), 1));

const methodTotal = computed(() => props.byMethod.reduce((s, m) => s + m.total, 0) || 1);
const methodColors = ['bg-brand-600', 'bg-amber-500', 'bg-emerald-500', 'bg-indigo-500', 'bg-pink-500', 'bg-cyan-500'];

const providerLabels = {
    orange_money: 'Orange Money', mtn_momo: 'MTN MoMo', wave: 'Wave', moov: 'Moov Money',
    bank_transfer_national: 'Virement national', bank_transfer_international: 'Virement international',
    moneroo: 'Moneroo', cash: 'Espèces',
};
</script>

<template>
    <Head title="Admin — Tableau de bord financier" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-emerald-600">Tableau de bord financier</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <!-- KPIs principaux -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="rounded-lg bg-gradient-to-br from-brand-900 to-brand-600 p-5 text-white shadow">
                        <div class="text-xs uppercase tracking-wide opacity-75">CA total</div>
                        <div class="mt-1 text-2xl font-bold">{{ fmt(kpis.revenue_total) }}</div>
                        <div class="text-xs opacity-60">FCFA toutes périodes</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Paiements du jour</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">{{ fmt(kpis.revenue_today) }}</div>
                        <div class="text-xs text-gray-400">{{ kpis.payments_today_count }} paiements</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">CA du mois</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">{{ fmt(kpis.revenue_month) }}</div>
                        <div class="text-xs text-gray-400">{{ kpis.payments_month_count }} paiements</div>
                    </div>
                    <div class="rounded-lg bg-gradient-to-br from-gold-400 to-amber-500 p-5 text-white shadow">
                        <div class="text-xs uppercase tracking-wide opacity-90">Taux de conversion</div>
                        <div class="mt-1 text-2xl font-bold">{{ pct(kpis.conversion_rate) }}</div>
                        <div class="text-xs opacity-75">{{ kpis.orders_paid }} / {{ kpis.orders_total }} commandes</div>
                    </div>
                </div>

                <!-- KPIs secondaires -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
                    <div class="rounded-lg bg-white p-5 shadow border-l-4 border-amber-500">
                        <div class="text-xs uppercase tracking-wide text-gray-500">En attente de validation</div>
                        <div class="mt-1 text-2xl font-bold text-amber-600">{{ kpis.pending_validation_count }}</div>
                        <div class="text-xs text-gray-400">{{ fmt(kpis.pending_validation_amount) }} FCFA</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow border-l-4 border-orange-500">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Licences expirant sous 30j</div>
                        <div class="mt-1 text-2xl font-bold text-orange-600">{{ kpis.licenses_expiring_30d }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow border-l-4 border-indigo-500">
                        <div class="text-xs uppercase tracking-wide text-gray-500">Licences provisoires actives</div>
                        <div class="mt-1 text-2xl font-bold text-indigo-600">{{ kpis.licenses_provisional }}</div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- CA mensuel 12 mois (barres) -->
                    <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                        <h3 class="mb-4 font-semibold text-gray-800">Chiffre d'affaires — 12 derniers mois</h3>
                        <div class="flex h-48 items-end gap-1">
                            <div
                                v-for="(bar, i) in chart12m"
                                :key="i"
                                class="group relative flex-1 flex flex-col items-center justify-end"
                            >
                                <div
                                    class="w-full rounded-t-sm bg-brand-500 transition-all hover:bg-brand-600"
                                    :style="{ height: `${(bar.total / maxChart) * 100}%`, minHeight: bar.total > 0 ? '4px' : '0' }"
                                />
                                <div class="mt-1 text-center text-xs text-gray-400 leading-tight" style="font-size:9px">
                                    {{ bar.month.split(' ')[0] }}
                                </div>
                                <!-- Tooltip -->
                                <div class="absolute bottom-full mb-1 hidden rounded bg-gray-800 px-2 py-1 text-xs text-white group-hover:block whitespace-nowrap">
                                    {{ bar.month }}<br>{{ fmt(bar.total) }} FCFA
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Répartition par méthode (donut simplifié) -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 font-semibold text-gray-800">Répartition par méthode de paiement</h3>
                        <div class="space-y-3">
                            <div v-for="(m, i) in byMethod" :key="m.method" class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium text-gray-700">{{ providerLabels[m.method] ?? m.method }}</span>
                                    <span class="text-gray-500">{{ fmt(m.total) }} FCFA ({{ m.count }})</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-gray-100">
                                    <div
                                        class="h-2 rounded-full"
                                        :class="methodColors[i % methodColors.length]"
                                        :style="{ width: `${(m.total / methodTotal) * 100}%` }"
                                    />
                                </div>
                            </div>
                            <div v-if="!byMethod.length" class="text-sm text-gray-400 text-center py-4">Aucune donnée</div>
                        </div>
                    </div>

                    <!-- Répartition par pays (barres horizontales top 10) -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 font-semibold text-gray-800">Top 10 pays</h3>
                        <div class="space-y-2">
                            <div v-for="c in byCountry" :key="c.country" class="flex items-center gap-2">
                                <div class="w-10 text-right text-xs font-semibold text-gray-600">{{ c.country }}</div>
                                <div class="flex-1 h-5 bg-gray-100 rounded-sm">
                                    <div
                                        class="h-5 rounded-sm bg-emerald-500"
                                        :style="{ width: `${(c.total / maxCountry) * 100}%` }"
                                    />
                                </div>
                                <div class="w-28 text-right text-xs text-gray-500">{{ fmt(c.total) }} ({{ c.count }})</div>
                            </div>
                            <div v-if="!byCountry.length" class="text-sm text-gray-400 text-center py-4">Aucune donnée</div>
                        </div>
                    </div>

                    <!-- Répartition par forfait -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 font-semibold text-gray-800">Revenus par forfait</h3>
                        <div class="space-y-3">
                            <div v-for="p in byPlan" :key="p.code">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-700">{{ p.plan }}</span>
                                    <span class="text-gray-500">{{ fmt(p.total) }} FCFA ({{ p.count }})</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-gray-100">
                                    <div class="h-2 rounded-full bg-indigo-500" :style="{ width: `${(p.total / maxPlan) * 100}%` }" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Revenus par devise -->
                    <div class="rounded-lg bg-white p-6 shadow">
                        <h3 class="mb-4 font-semibold text-gray-800">Revenus par devise</h3>
                        <table class="w-full text-sm">
                            <thead class="text-xs uppercase text-gray-400">
                                <tr>
                                    <th class="pb-2 text-left">Devise</th>
                                    <th class="pb-2 text-right">Total</th>
                                    <th class="pb-2 text-right">Transactions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="c in byCurrency" :key="c.currency">
                                    <td class="py-2 font-semibold">{{ c.currency }}</td>
                                    <td class="py-2 text-right font-mono">{{ fmt(c.total) }}</td>
                                    <td class="py-2 text-right text-gray-500">{{ c.count }}</td>
                                </tr>
                                <tr v-if="!byCurrency.length">
                                    <td colspan="3" class="py-4 text-center text-gray-400">Aucune donnée</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top 10 clients -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="mb-4 font-semibold text-gray-800">Top 10 clients par CA</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-400">
                                <tr>
                                    <th class="px-4 py-2 text-left">#</th>
                                    <th class="px-4 py-2 text-left">Client</th>
                                    <th class="px-4 py-2 text-left">Email</th>
                                    <th class="px-4 py-2 text-right">Commandes</th>
                                    <th class="px-4 py-2 text-right">CA total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="(client, i) in topClients" :key="client.email" class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-gray-400 font-semibold">{{ i + 1 }}</td>
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ client.name }}</td>
                                    <td class="px-4 py-2 text-gray-500">{{ client.email }}</td>
                                    <td class="px-4 py-2 text-right">{{ client.orders_count }}</td>
                                    <td class="px-4 py-2 text-right font-mono font-semibold">{{ fmt(client.total) }} FCFA</td>
                                </tr>
                                <tr v-if="!topClients.length">
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucune donnée</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
