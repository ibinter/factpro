<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    session: Object,
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

const totalsByMethod = computed(() => Object.entries(props.session.totals_by_method ?? {}));
const difference = computed(() => Number(props.session.difference ?? 0));

const printPage = () => window.print();
</script>

<template>
    <Head :title="`Rapport Z — Session #${session.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between print:hidden">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Rapport Z — Session de caisse #{{ session.id }}</h2>
                <div class="flex gap-3">
                    <Link :href="route('pos.index')" class="text-sm font-semibold text-brand-600 hover:underline">
                        ← Retour à la caisse
                    </Link>
                    <button
                        class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700"
                        @click="printPage"
                    >
                        🖨 Imprimer
                    </button>
                </div>
            </div>
        </template>

        <div class="py-8 print:py-0">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl bg-white shadow print:shadow-none">
                    <!-- En-tête -->
                    <div class="border-b bg-brand-900 px-8 py-6 text-white print:bg-white print:text-brand-900">
                        <h1 class="text-2xl font-extrabold">RAPPORT Z</h1>
                        <p class="mt-1 text-sm opacity-80">
                            Session de caisse #{{ session.id }} —
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-bold uppercase"
                                :class="session.status === 'closed' ? 'bg-green-500 text-white' : 'bg-gold-400 text-brand-900'"
                            >
                                {{ session.status === 'closed' ? 'Clôturée' : 'Ouverte' }}
                            </span>
                        </p>
                    </div>

                    <div class="space-y-8 px-8 py-6">
                        <!-- Informations générales -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <div class="text-xs font-semibold uppercase text-gray-400">Caissier</div>
                                <div class="font-bold text-gray-800">{{ session.user?.name ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase text-gray-400">Période</div>
                                <div class="font-bold text-gray-800">{{ dt(session.opened_at) }} → {{ dt(session.closed_at) }}</div>
                            </div>
                        </div>

                        <!-- Chiffres clés -->
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="rounded-xl bg-gray-50 py-4">
                                <div class="text-xs font-semibold uppercase text-gray-400">Fonds initial</div>
                                <div class="text-xl font-extrabold text-gray-800">{{ fmt(session.opening_float) }}</div>
                            </div>
                            <div class="rounded-xl bg-brand-50 py-4">
                                <div class="text-xs font-semibold uppercase text-brand-600">CA total</div>
                                <div class="text-xl font-extrabold text-brand-900">{{ fmt(session.total_sales) }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 py-4">
                                <div class="text-xs font-semibold uppercase text-gray-400">Tickets</div>
                                <div class="text-xl font-extrabold text-gray-800">{{ session.tickets_count }}</div>
                            </div>
                        </div>

                        <!-- Répartition par moyen de paiement -->
                        <div>
                            <h3 class="mb-2 text-sm font-bold uppercase tracking-wide text-gray-500">
                                Répartition par moyen de paiement
                            </h3>
                            <table class="w-full text-sm">
                                <tbody class="divide-y">
                                    <tr v-for="[method, amount] in totalsByMethod" :key="method">
                                        <td class="py-2 text-gray-600">{{ methodLabels[method] ?? method }}</td>
                                        <td class="py-2 text-right font-bold text-gray-800">{{ fmt(amount) }} {{ currency }}</td>
                                    </tr>
                                    <tr v-if="!totalsByMethod.length">
                                        <td colspan="2" class="py-4 text-center text-gray-400">Aucun encaissement.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Contrôle des espèces -->
                        <div>
                            <h3 class="mb-2 text-sm font-bold uppercase tracking-wide text-gray-500">Contrôle des espèces</h3>
                            <table class="w-full text-sm">
                                <tbody class="divide-y">
                                    <tr>
                                        <td class="py-2 text-gray-600">Espèces attendues (fonds + ventes espèces)</td>
                                        <td class="py-2 text-right font-bold text-gray-800">{{ fmt(session.expected_cash) }} {{ currency }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-gray-600">Espèces comptées</td>
                                        <td class="py-2 text-right font-bold text-gray-800">{{ fmt(session.counted_cash) }} {{ currency }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 font-bold text-gray-800">Écart de caisse</td>
                                        <td class="py-2 text-right">
                                            <span
                                                class="rounded-full px-3 py-1 text-sm font-extrabold"
                                                :class="difference === 0
                                                    ? 'bg-green-100 text-green-700'
                                                    : difference > 0
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-red-100 text-red-600'"
                                            >
                                                {{ difference > 0 ? '+' : '' }}{{ fmt(difference) }} {{ currency }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Notes -->
                        <div v-if="session.notes">
                            <h3 class="mb-2 text-sm font-bold uppercase tracking-wide text-gray-500">Notes</h3>
                            <p class="whitespace-pre-line rounded-lg bg-gray-50 p-4 text-sm text-gray-600">{{ session.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    nav, header, .print\:hidden {
        display: none !important;
    }
}
</style>
