<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminTabs from '@/Components/AdminTabs.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    revenue: Object,
    mrr: Number,
    licensesByStatus: Object,
    revenueByPlan: Array,
    expiringSoon: Array,
    recentPayments: Array,
    users: Object,
    chart: Array,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const maxChart = computed(() => Math.max(...props.chart.map((c) => c.total), 1));

const statusLabels = {
    trial: 'Essai', pending: 'En attente', provisional: 'Provisoire', active: 'Active',
    grace_period: 'Période de grâce', suspended: 'Suspendue', expired: 'Expirée',
    terminated: 'Résiliée', revoked: 'Révoquée',
};
const statusColors = {
    trial: 'bg-blue-100 text-blue-700', pending: 'bg-amber-100 text-amber-700',
    provisional: 'bg-indigo-100 text-indigo-700', active: 'bg-green-100 text-green-700',
    grace_period: 'bg-teal-100 text-teal-700', suspended: 'bg-orange-100 text-orange-700',
    expired: 'bg-gray-100 text-gray-500', terminated: 'bg-gray-200 text-gray-600',
    revoked: 'bg-red-100 text-red-700',
};

const providerLabels = {
    orange_money: 'Orange Money', mtn_momo: 'MTN MoMo', wave: 'Wave', moov: 'Moov Money',
    bank_transfer_national: 'Virement national', bank_transfer_international: 'Virement international',
    moneroo: 'Moneroo', cash: 'Espèces',
};
</script>

<template>
    <Head title="Admin — Tableau de bord" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Console Superadmin — <span class="text-gold-600">Tableau de bord</span>
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <AdminTabs />

                <!-- KPIs financiers -->
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <div class="rounded-lg bg-gradient-to-br from-brand-900 to-brand-600 p-5 text-white shadow">
                        <div class="text-xs uppercase tracking-wide opacity-75">CA aujourd'hui</div>
                        <div class="mt-1 text-2xl font-bold">{{ fmt(revenue.day) }} <span class="text-sm font-normal">FCFA</span></div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">CA du mois</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">{{ fmt(revenue.month) }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs uppercase tracking-wide text-gray-500">CA de l'année</div>
                        <div class="mt-1 text-2xl font-bold text-gray-800">{{ fmt(revenue.year) }} <span class="text-sm font-normal text-gray-400">FCFA</span></div>
                    </div>
                    <div class="rounded-lg bg-gradient-to-br from-gold-400 to-amber-500 p-5 text-white shadow">
                        <div class="text-xs uppercase tracking-wide opacity-90">MRR estimé</div>
                        <div class="mt-1 text-2xl font-bold">{{ fmt(mrr) }} <span class="text-sm font-normal">FCFA</span></div>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Graphique CA 6 mois -->
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

                    <!-- Répartition licences + utilisateurs -->
                    <div class="space-y-4">
                        <div class="rounded-lg bg-white p-5 shadow">
                            <h3 class="mb-3 font-semibold text-gray-800">Licences par statut</h3>
                            <div class="space-y-2">
                                <div
                                    v-for="(count, status) in licensesByStatus"
                                    :key="status"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="statusColors[status]">
                                        {{ statusLabels[status] ?? status }}
                                    </span>
                                    <span class="font-bold text-gray-800">{{ count }}</span>
                                </div>
                                <div v-if="!Object.keys(licensesByStatus).length" class="text-sm text-gray-400">
                                    Aucune licence.
                                </div>
                            </div>
                        </div>
                        <div class="rounded-lg bg-white p-5 shadow">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Utilisateurs</div>
                            <div class="mt-1 text-2xl font-bold text-gray-800">{{ users.total }}</div>
                            <div class="mt-1 text-xs text-green-600">+{{ users.new_month }} ce mois</div>
                        </div>
                    </div>
                </div>

                <!-- Revenus par forfait -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold text-gray-800">Revenus par forfait</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Forfait</th>
                                <th class="px-6 py-3 text-right">Ce mois</th>
                                <th class="px-6 py-3 text-right">Total cumulé</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="row in revenueByPlan" :key="row.code" class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-semibold text-gray-800">{{ row.name }}</td>
                                <td class="px-6 py-3 text-right">{{ fmt(row.month) }} FCFA</td>
                                <td class="px-6 py-3 text-right font-semibold">{{ fmt(row.total) }} FCFA</td>
                            </tr>
                            <tr v-if="!revenueByPlan.length">
                                <td colspan="3" class="px-6 py-8 text-center text-gray-400">Aucun revenu enregistré.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Licences expirant sous 7 jours -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b px-6 py-4">
                            <h3 class="font-semibold text-gray-800">Licences expirant sous 7 jours</h3>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Client</th>
                                    <th class="px-6 py-3">Forfait</th>
                                    <th class="px-6 py-3 text-right">Échéance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="l in expiringSoon" :key="l.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <div class="font-semibold text-gray-800">{{ l.user?.name ?? '—' }}</div>
                                        <div class="text-xs text-gray-400">{{ l.user?.email }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-gray-500">{{ l.plan?.name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="font-semibold text-gray-800">{{ l.ends_at }}</div>
                                        <div class="text-xs text-amber-600">J-{{ l.days_remaining }}</div>
                                    </td>
                                </tr>
                                <tr v-if="!expiringSoon.length">
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-400">Aucune échéance proche.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- 10 derniers paiements validés -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="border-b px-6 py-4">
                            <h3 class="font-semibold text-gray-800">Derniers paiements validés</h3>
                        </div>
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-6 py-3">Référence</th>
                                    <th class="px-6 py-3">Client</th>
                                    <th class="px-6 py-3 text-right">Montant</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="p in recentPayments" :key="p.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-3">
                                        <div class="font-mono text-xs font-bold text-brand-700">{{ p.internal_reference }}</div>
                                        <div class="text-xs text-gray-400">{{ providerLabels[p.provider] ?? p.provider }} · {{ p.confirmed_at }}</div>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="font-semibold text-gray-800">{{ p.user?.name ?? '—' }}</div>
                                        <div class="text-xs text-gray-400">{{ p.plan ?? '—' }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-right font-semibold text-green-600">{{ fmt(p.amount) }} {{ p.currency }}</td>
                                </tr>
                                <tr v-if="!recentPayments.length">
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-400">Aucun paiement validé.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
