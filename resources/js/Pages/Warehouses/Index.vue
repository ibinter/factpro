<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    warehouses: Array,
})

const statusColor = (w) => {
    if (!w.is_active) return 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300'
    return 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
}

const formatCurrency = (val) =>
    new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0 }).format(val ?? 0)
</script>

<template>
    <Head title="Entrepôts" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Entrepôts
                </h2>
                <Link
                    :href="route('warehouses.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition"
                >
                    + Nouvel entrepôt
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div v-if="warehouses.length === 0" class="text-center py-16 text-gray-500 dark:text-gray-400">
                    Aucun entrepôt pour cette entreprise.
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="w in warehouses"
                        :key="w.id"
                        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 flex flex-col gap-3 hover:shadow-md transition"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-gray-100 text-lg">{{ w.name }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 font-mono">{{ w.code }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span
                                    v-if="w.is_default"
                                    class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300 font-medium"
                                >
                                    Par défaut
                                </span>
                                <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusColor(w)]">
                                    {{ w.is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                        </div>

                        <div class="text-sm text-gray-500 dark:text-gray-400 space-y-0.5">
                            <p v-if="w.city">📍 {{ w.city }}</p>
                            <p v-if="w.manager_name">👤 {{ w.manager_name }}</p>
                            <p v-if="w.phone">📞 {{ w.phone }}</p>
                        </div>

                        <div class="mt-auto pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-between text-sm">
                            <div>
                                <p class="text-gray-400 dark:text-gray-500 text-xs">Produits</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ w.stocks_count }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-gray-400 dark:text-gray-500 text-xs">Valeur totale</p>
                                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ formatCurrency(w.total_value) }}</p>
                            </div>
                        </div>

                        <Link
                            :href="route('warehouses.show', w.id)"
                            class="block text-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline mt-1"
                        >
                            Voir les stocks →
                        </Link>
                    </div>
                </div>

                <div class="mt-8">
                    <Link
                        :href="route('warehouse-transfers.index')"
                        class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                    >
                        Voir les transferts de stock →
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
