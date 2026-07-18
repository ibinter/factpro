<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    rows: Array,
    total: Number,
    top10: Array,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(n ?? 0);

const ABC_CLASSES = {
    A: 'bg-brand-600 text-white',
    B: 'bg-gold-400 text-brand-900',
    C: 'bg-gray-200 text-gray-600',
};
</script>

<template>
    <Head title="Valorisation du stock" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">💰 Valorisation du stock (CMUP)</h2>
                <Link :href="route('stock.index')">
                    <SecondaryButton>← Retour aux stocks</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                <!-- Total -->
                <div class="rounded-lg bg-brand-900 p-5 text-white shadow">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gold-400">Valeur totale du stock</div>
                    <div class="mt-1 text-3xl font-bold">{{ fmt(total) }}</div>
                    <div class="mt-1 text-xs text-gray-300">
                        Coût moyen unitaire pondéré (CMUP) × quantités en stock — {{ rows.length }} produit(s) suivi(s)
                    </div>
                </div>

                <!-- Top 10 -->
                <div v-if="top10.length" class="rounded-lg bg-white p-4 shadow">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500">Top 10 par valeur (analyse ABC)</h3>
                    <div class="flex flex-wrap gap-2">
                        <div v-for="row in top10" :key="row.id" class="flex items-center gap-2 rounded-full bg-gray-50 px-3 py-1.5 text-sm">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold" :class="ABC_CLASSES[row.abc_class]">
                                {{ row.abc_class }}
                            </span>
                            <span class="font-semibold text-gray-800">{{ row.name }}</span>
                            <span class="text-gray-500">{{ fmt(row.value) }}</span>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-400">
                        Classe A = 80 % de la valeur cumulée · B = 15 % · C = 5 % restants.
                    </p>
                </div>

                <!-- Tableau -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Produit</th>
                                    <th class="px-4 py-3">SKU</th>
                                    <th class="px-4 py-3 text-center">Classe</th>
                                    <th class="px-4 py-3 text-right">Quantité</th>
                                    <th class="px-4 py-3 text-right">CMUP</th>
                                    <th class="px-4 py-3 text-right">Valeur</th>
                                    <th class="px-4 py-3 text-right">Prix de vente</th>
                                    <th class="px-4 py-3 text-right">Marge théorique</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="row in rows" :key="row.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold text-gray-800">{{ row.name }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ row.sku ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold" :class="ABC_CLASSES[row.abc_class]">
                                            {{ row.abc_class }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600">{{ fmt(row.quantity) }} {{ row.unit }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">{{ fmt(row.cost) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-brand-900">{{ fmt(row.value) }}</td>
                                    <td class="px-4 py-3 text-right text-gray-600">{{ fmt(row.price) }}</td>
                                    <td class="px-4 py-3 text-right" :class="row.margin_percent !== null && row.margin_percent < 0 ? 'text-red-600' : 'text-green-600'">
                                        {{ row.margin_percent !== null ? `${fmt(row.margin_percent)} %` : '—' }}
                                    </td>
                                </tr>
                                <tr v-if="!rows.length">
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">Aucun produit avec suivi de stock.</td>
                                </tr>
                            </tbody>
                            <tfoot v-if="rows.length" class="bg-gray-50">
                                <tr>
                                    <td colspan="5" class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">
                                        Total général
                                    </td>
                                    <td class="px-4 py-3 text-right text-base font-bold text-brand-900">{{ fmt(total) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
