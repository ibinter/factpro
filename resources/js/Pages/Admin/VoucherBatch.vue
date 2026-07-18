<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    batchRef: String,
    vouchers: Array,
    stats: Object,
});

const filter = ref('all');

const filtered = computed(() => {
    if (filter.value === 'all') return props.vouchers;
    return props.vouchers.filter((v) => v.status === filter.value);
});

const statusClass = (status) => {
    const map = {
        available: 'bg-green-100 text-green-700',
        used: 'bg-blue-100 text-blue-700',
        expired: 'bg-red-100 text-red-700',
        cancelled: 'bg-gray-100 text-gray-600',
        reserved: 'bg-yellow-100 text-yellow-700',
    };
    return map[status] ?? 'bg-gray-100 text-gray-600';
};

const statusLabel = (status) => {
    const map = { available: '🟢 Disponible', used: '✅ Utilisé', expired: '🔴 Expiré', cancelled: '⚫ Annulé', reserved: '🟡 Réservé' };
    return map[status] ?? status;
};

const cancelVoucher = (voucher) => {
    if (!confirm(`Annuler le code ${voucher.code} ?`)) return;
    router.delete(route('admin.vouchers.cancel', voucher.id), { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Lot ${batchRef}`" />

    <div class="min-h-screen bg-gray-100">
        <div class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <div>
                    <a :href="route('admin.vouchers.index')" class="text-sm text-brand-600 hover:underline">&larr; Retour aux lots</a>
                    <h1 class="mt-1 text-xl font-bold text-gray-900">Lot <span class="font-mono text-brand-700">{{ batchRef }}</span></h1>
                </div>
                <a :href="route('admin.vouchers.export', batchRef)" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Exporter CSV
                </a>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash -->
            <div v-if="$page.props.flash?.success" class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ $page.props.flash.success }}
            </div>

            <!-- Stats du lot -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                <div class="rounded-xl bg-white p-4 shadow text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ stats.total }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total</div>
                </div>
                <div class="rounded-xl bg-white p-4 shadow text-center">
                    <div class="text-2xl font-bold text-green-700">{{ stats.available }}</div>
                    <div class="text-xs text-gray-500 mt-1">Disponibles</div>
                </div>
                <div class="rounded-xl bg-white p-4 shadow text-center">
                    <div class="text-2xl font-bold text-blue-700">{{ stats.used }}</div>
                    <div class="text-xs text-gray-500 mt-1">Utilisés</div>
                </div>
                <div class="rounded-xl bg-white p-4 shadow text-center">
                    <div class="text-2xl font-bold text-red-600">{{ stats.expired }}</div>
                    <div class="text-xs text-gray-500 mt-1">Expirés</div>
                </div>
                <div class="rounded-xl bg-white p-4 shadow text-center">
                    <div class="text-2xl font-bold text-gray-600">{{ stats.cancelled }}</div>
                    <div class="text-xs text-gray-500 mt-1">Annulés</div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="flex gap-2 flex-wrap">
                <button v-for="f in ['all','available','used','expired','cancelled']" :key="f"
                    @click="filter = f"
                    :class="['rounded-full px-3 py-1 text-xs font-semibold border transition', filter === f ? 'bg-brand-600 text-white border-brand-600' : 'bg-white text-gray-600 border-gray-300 hover:border-brand-400']"
                >
                    {{ f === 'all' ? 'Tous' : statusLabel(f) }}
                </button>
            </div>

            <!-- Tableau codes -->
            <div class="overflow-hidden rounded-xl bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Code</th>
                            <th class="px-6 py-3 text-center font-semibold text-gray-700">Statut</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Forfait</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Utilisé par</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Utilisé le</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Expiration</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="!filtered.length">
                            <td colspan="7" class="px-6 py-6 text-center text-gray-400">Aucun code dans ce filtre.</td>
                        </tr>
                        <tr v-for="v in filtered" :key="v.id" class="hover:bg-gray-50">
                            <td class="px-6 py-3 font-mono text-sm font-semibold text-gray-900">{{ v.code }}</td>
                            <td class="px-6 py-3 text-center">
                                <span :class="['rounded-full px-2 py-0.5 text-xs font-semibold', statusClass(v.status)]">
                                    {{ statusLabel(v.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ v.plan?.name ?? 'Tous' }}</td>
                            <td class="px-6 py-3 text-gray-600">
                                {{ v.used_by_user ? `${v.used_by_user.name} (${v.used_by_user.email})` : '—' }}
                            </td>
                            <td class="px-6 py-3 text-gray-600">
                                {{ v.used_at ? new Date(v.used_at).toLocaleDateString('fr-FR') : '—' }}
                            </td>
                            <td class="px-6 py-3 text-gray-600">
                                {{ v.expires_at ? new Date(v.expires_at).toLocaleDateString('fr-FR') : 'Sans expiration' }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <button
                                    v-if="v.status === 'available'"
                                    @click="cancelVoucher(v)"
                                    class="text-xs text-red-600 hover:underline font-semibold"
                                >
                                    Annuler
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
