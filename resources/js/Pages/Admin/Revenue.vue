<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    kpis: Object,
    monthly: Array,
    by_gateway: Array,
    by_plan: Array,
    recent_orders: Array,
});

// ─── Formatage ────────────────────────────────────────────────────────────────
const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);
const fmtFcfa = (n) => fmt(n) + ' FCFA';
const fmtShort = (n) => {
    if (n >= 1_000_000) return (n / 1_000_000).toLocaleString('fr-FR', { maximumFractionDigits: 1 }) + 'M F';
    if (n >= 1_000)    return (n / 1_000).toLocaleString('fr-FR', { maximumFractionDigits: 0 }) + 'k F';
    return fmt(n) + ' F';
};

const fmtDate = (s) => s ? new Date(s).toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' }) : '—';

// ─── Graphique SVG ────────────────────────────────────────────────────────────
const chartHeight = 200;
const chartPad    = 10;

const chartMax = computed(() => {
    const max = Math.max(...props.monthly.map(m => m.revenue), 1);
    return max;
});

const bars = computed(() => {
    const total = props.monthly.length;
    const barW  = 100 / total;
    return props.monthly.map((m, i) => {
        const h = chartMax.value > 0 ? (m.revenue / chartMax.value) * (chartHeight - chartPad * 2) : 0;
        const x = i * barW;
        const y = chartHeight - chartPad - h;
        return { ...m, h, x, y, barW, midX: x + barW / 2 };
    });
});

// ─── Barres de progression (by_gateway / by_plan) ─────────────────────────────
const gatewayMax = computed(() => Math.max(...(props.by_gateway ?? []).map(g => g.total), 1));
const planMax    = computed(() => Math.max(...(props.by_plan ?? []).map(p => p.total), 1));

const gatewayLabels = {
    orange_money:     'Orange Money',
    mtn_momo:         'MTN MoMo',
    wave:             'Wave',
    moov:             'Moov Money',
    bank_transfer:    'Virement bancaire',
    cash:             'Espèces',
    moneroo:          'Moneroo',
    manuel:           'Manuel',
};
const gatewayLabel = (g) => gatewayLabels[g] ?? g;
</script>

<template>
    <Head title="Dashboard Revenus" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 leading-tight">
                Dashboard Revenus
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Tabs -->
                <AdminTabs />

                <!-- ─── Ligne 1 : KPI principaux ─────────────────────────────── -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- MRR -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5 border-t-4 border-blue-600">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">MRR</p>
                        <p class="mt-1 text-2xl font-extrabold text-blue-700 dark:text-blue-400">{{ fmtFcfa(kpis.mrr) }}</p>
                        <p class="mt-1 text-xs text-gray-400">Revenu Mensuel Récurrent</p>
                    </div>

                    <!-- ARR -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5 border-t-4 border-yellow-400">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">ARR</p>
                        <p class="mt-1 text-2xl font-extrabold text-yellow-500 dark:text-yellow-400">{{ fmtFcfa(kpis.arr) }}</p>
                        <p class="mt-1 text-xs text-gray-400">Revenu Annuel Récurrent</p>
                    </div>

                    <!-- Croissance MoM -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5 border-t-4"
                         :class="kpis.growth >= 0 ? 'border-green-500' : 'border-red-500'">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Croissance MoM</p>
                        <p class="mt-1 text-2xl font-extrabold"
                           :class="kpis.growth >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ kpis.growth >= 0 ? '+' : '' }}{{ kpis.growth }}%
                        </p>
                        <p class="mt-1 text-xs text-gray-400">vs mois précédent</p>
                    </div>

                    <!-- Churn Rate -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-5 border-t-4 border-orange-400">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Churn Rate</p>
                        <p class="mt-1 text-2xl font-extrabold text-orange-500">{{ kpis.churn_rate }}%</p>
                        <p class="mt-1 text-xs text-gray-400">Licences perdues ce mois</p>
                    </div>
                </div>

                <!-- ─── Ligne 2 : KPI secondaires ────────────────────────────── -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-xl">💸</div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Ce mois</p>
                            <p class="font-bold text-gray-800 dark:text-gray-100">{{ fmtFcfa(kpis.revenue_this_month) }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center text-xl">🏦</div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Revenu total</p>
                            <p class="font-bold text-gray-800 dark:text-gray-100">{{ fmtFcfa(kpis.total_revenue) }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center text-xl">🔑</div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Licences actives</p>
                            <p class="font-bold text-gray-800 dark:text-gray-100">{{ fmt(kpis.active_licenses) }}</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center text-xl">👤</div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Nouveaux clients</p>
                            <p class="font-bold text-gray-800 dark:text-gray-100">{{ fmt(kpis.new_users) }} <span class="text-xs text-gray-400">/ {{ fmt(kpis.total_users) }} total</span></p>
                        </div>
                    </div>
                </div>

                <!-- ─── Graphique 12 mois ─────────────────────────────────────── -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Revenus des 12 derniers mois</h3>
                    <div class="overflow-x-auto">
                        <svg
                            :viewBox="`0 0 100 ${chartHeight + 24}`"
                            preserveAspectRatio="none"
                            class="w-full"
                            :style="`height: ${chartHeight + 24}px; min-width: 480px`"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <!-- Grille horizontale légère -->
                            <line v-for="i in 4" :key="i"
                                  x1="0" :y1="chartPad + ((chartHeight - chartPad * 2) / 4) * (i - 1)"
                                  x2="100" :y2="chartPad + ((chartHeight - chartPad * 2) / 4) * (i - 1)"
                                  stroke="#e5e7eb" stroke-width="0.3" />

                            <!-- Barres -->
                            <g v-for="b in bars" :key="b.month">
                                <rect
                                    :x="b.x + b.barW * 0.15"
                                    :y="b.y"
                                    :width="b.barW * 0.7"
                                    :height="b.h"
                                    rx="0.8"
                                    fill="#1a56db"
                                    opacity="0.85"
                                />
                                <!-- Montant au dessus de la barre -->
                                <text
                                    v-if="b.revenue > 0"
                                    :x="b.midX"
                                    :y="b.y - 1"
                                    text-anchor="middle"
                                    font-size="2.2"
                                    fill="#374151"
                                    class="dark:fill-gray-300"
                                >{{ fmtShort(b.revenue) }}</text>
                                <!-- Label mois en bas -->
                                <text
                                    :x="b.midX"
                                    :y="chartHeight + 14"
                                    text-anchor="middle"
                                    font-size="2.4"
                                    fill="#6b7280"
                                >{{ b.month }}</text>
                            </g>
                        </svg>
                    </div>
                </div>

                <!-- ─── Passerelles + Plans ────────────────────────────────────── -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Passerelles -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Revenus par passerelle</h3>
                        <div v-if="by_gateway.length === 0" class="text-sm text-gray-400 text-center py-6">Aucune donnée</div>
                        <ul class="space-y-3">
                            <li v-for="g in by_gateway" :key="g.gateway" class="space-y-1">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ gatewayLabel(g.gateway) }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 rounded-full px-2 py-0.5">{{ g.count }} paiements</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-100 text-xs">{{ fmtFcfa(g.total) }}</span>
                                    </div>
                                </div>
                                <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 rounded-full transition-all"
                                         :style="`width: ${(g.total / gatewayMax) * 100}%`" />
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Plans -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-6">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-4">Revenus par plan</h3>
                        <div v-if="by_plan.length === 0" class="text-sm text-gray-400 text-center py-6">Aucune donnée</div>
                        <ul class="space-y-3">
                            <li v-for="p in by_plan" :key="p.plan" class="space-y-1">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-200">{{ p.plan }}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300 rounded-full px-2 py-0.5">{{ p.count }} ventes</span>
                                        <span class="font-semibold text-gray-800 dark:text-gray-100 text-xs">{{ fmtFcfa(p.total) }}</span>
                                    </div>
                                </div>
                                <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-yellow-400 rounded-full transition-all"
                                         :style="`width: ${(p.total / planMax) * 100}%`" />
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- ─── Dernières transactions ──────────────────────────────────── -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Dernières transactions</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">N° Commande</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Client</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Plan</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Montant</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Passerelle</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-if="recent_orders.length === 0">
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Aucune transaction enregistrée</td>
                                </tr>
                                <tr v-for="o in recent_orders" :key="o.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <td class="px-4 py-3 font-mono text-xs text-blue-700 dark:text-blue-400 font-semibold">{{ o.order_number }}</td>
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ o.user ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">{{ o.plan ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap">{{ fmtFcfa(o.amount) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-block bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded px-2 py-0.5 text-xs font-medium">
                                            {{ gatewayLabel(o.gateway) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">{{ fmtDate(o.paid_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
