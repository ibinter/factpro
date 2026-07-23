<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    document: Object,
    offers: Array,
});

const statusLabel = (s) => ({
    pending: 'En attente', viewed: 'Consulté', responded: 'Offre reçue',
    selected: 'Retenu', rejected: 'Rejeté',
}[s] ?? s);

const statusClass = (s) => ({
    pending: 'bg-gray-100 text-gray-600',
    viewed: 'bg-yellow-100 text-yellow-700',
    responded: 'bg-blue-100 text-blue-700',
    selected: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-600',
}[s] ?? 'bg-gray-100 text-gray-600');

const fmt = (n) => n != null ? new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(n) : '—';

const selectOffer = (id) => {
    router.post(route('supplier.select', id));
};
</script>

<template>
    <Head title="Comparatif fournisseurs" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">
                Comparatif des offres — {{ document.number }}
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Fournisseur</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Email</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-300">Prix proposé</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-300">Délai (j)</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500 dark:text-gray-300">Remarques</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-300">Statut</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            <tr v-if="offers.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucune invitation envoyée.</td>
                            </tr>
                            <tr v-for="offer in offers" :key="offer.id"
                                :class="offer.status === 'selected' ? 'bg-green-50 dark:bg-green-900/20' : ''">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ offer.supplier_name }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ offer.supplier_email }}</td>
                                <td class="px-4 py-3 text-right font-bold text-gray-800 dark:text-white">
                                    {{ offer.quoted_price != null ? fmt(offer.quoted_price) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">{{ offer.delivery_days ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ offer.supplier_notes ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', statusClass(offer.status)]">
                                        {{ statusLabel(offer.status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button v-if="offer.status === 'responded'"
                                            @click="selectOffer(offer.id)"
                                            class="text-xs text-green-700 bg-green-100 hover:bg-green-200 px-3 py-1 rounded-full font-medium transition">
                                        Sélectionner
                                    </button>
                                    <span v-else-if="offer.status === 'selected'" class="text-xs text-green-700 font-semibold">Retenu ✓</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
