<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    currency: String,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(Math.round(Number(n ?? 0)));

const dt = (value) => value
    ? new Date(value).toLocaleString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    : '—';

const methodLabels = {
    cash: 'Espèces',
    mobile_money: 'Mobile Money',
    card: 'Carte bancaire',
    bank_transfer: 'Virement',
    cheque: 'Chèque',
    credit: 'Crédit',
};
</script>

<template>
    <Head title="Rapport X — Situation intraday" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">Rapport X — Situation en cours</h2>
                <Link :href="route('pos.index')" class="text-sm text-indigo-600 hover:underline">
                    ← Retour caisse
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl space-y-6 px-4">

                <!-- En-tête session -->
                <div class="rounded-lg border bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Session #{{ stats.session_id }}</h3>
                            <p class="text-sm text-gray-500">Caissier : {{ stats.cashier ?? '—' }}</p>
                            <p class="text-sm text-gray-500">Ouverte le {{ dt(stats.opened_at) }}</p>
                        </div>
                        <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-700">En cours</span>
                    </div>

                    <div class="grid grid-cols-3 gap-4 border-t pt-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-indigo-600">{{ stats.tickets_count }}</div>
                            <div class="text-xs text-gray-500">Tickets</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ fmt(stats.total_sales) }}</div>
                            <div class="text-xs text-gray-500">Total ventes ({{ currency }})</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-700">{{ fmt(stats.opening_float) }}</div>
                            <div class="text-xs text-gray-500">Fonds initial ({{ currency }})</div>
                        </div>
                    </div>
                </div>

                <!-- Répartition par moyen de paiement -->
                <div class="rounded-lg border bg-white p-6 shadow-sm">
                    <h3 class="mb-4 font-semibold text-gray-700">Répartition par moyen de paiement</h3>
                    <div v-if="Object.keys(stats.totals_by_method ?? {}).length === 0" class="text-sm text-gray-400">
                        Aucun encaissement pour le moment.
                    </div>
                    <table v-else class="w-full text-sm">
                        <tbody>
                            <tr v-for="(amount, method) in stats.totals_by_method" :key="method"
                                class="border-b last:border-0">
                                <td class="py-2 font-medium text-gray-700">
                                    {{ methodLabels[method] ?? method }}
                                </td>
                                <td class="py-2 text-right font-semibold text-gray-800">
                                    {{ fmt(amount) }} {{ currency }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button @click="window.print()"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Imprimer
                    </button>
                    <Link :href="route('pos.index')"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Retour à la caisse
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
