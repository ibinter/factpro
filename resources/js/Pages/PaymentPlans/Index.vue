<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    plans: Object,
    stats: Object,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const statusLabels = { active: 'Actif', completed: 'Terminé', cancelled: 'Annulé' };
const statusColors = {
    active: 'bg-green-100 text-green-700',
    completed: 'bg-brand-100 text-brand-700',
    cancelled: 'bg-gray-100 text-gray-500',
};

const progress = (plan) =>
    plan.total_amount ? Math.min(100, Math.round((plan.total_invoiced / plan.total_amount) * 100)) : 0;
</script>

<template>
    <Head title="Plans de paiement" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">📅 Plans de paiement & acomptes</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Stats -->
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="text-sm text-gray-400">Plans actifs</div>
                        <div class="mt-1 text-2xl font-bold text-brand-900">{{ stats.active }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="text-sm text-gray-400">Reste à facturer</div>
                        <div class="mt-1 text-2xl font-bold text-brand-900">{{ fmt(stats.outstanding) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <div class="text-sm text-gray-400">Échéances à venir (30j)</div>
                        <div class="mt-1 text-2xl font-bold text-brand-900">{{ stats.upcoming }}</div>
                    </div>
                </div>

                <!-- Tableau -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="bg-brand-900 text-left text-xs uppercase tracking-wide text-white">
                            <tr>
                                <th class="px-6 py-3">Plan</th>
                                <th class="px-6 py-3">Source</th>
                                <th class="px-6 py-3">Client</th>
                                <th class="px-6 py-3 text-right">Total</th>
                                <th class="px-6 py-3">Progression</th>
                                <th class="px-6 py-3 text-center">Statut</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="plan in plans.data" :key="plan.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3 font-medium text-gray-700">{{ plan.name }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ plan.source_document?.number ?? '—' }}</td>
                                <td class="px-6 py-3 text-gray-500">{{ plan.customer?.name ?? '—' }}</td>
                                <td class="px-6 py-3 text-right font-semibold">{{ fmt(plan.total_amount) }} {{ plan.currency }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-24 overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-full rounded-full bg-brand-600" :style="{ width: progress(plan) + '%' }"></div>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ progress(plan) }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="statusColors[plan.status]">
                                        {{ statusLabels[plan.status] ?? plan.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <Link :href="route('payment-plans.show', plan.id)" class="font-semibold text-brand-600 hover:underline">
                                        Ouvrir →
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!plans.data.length">
                                <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                                    Aucun plan de paiement. Créez-en un depuis un devis ou une facture.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
