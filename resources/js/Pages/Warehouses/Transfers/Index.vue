<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    transfers: Object,
})

const statusLabel = {
    draft: 'Brouillon',
    in_transit: 'En transit',
    received: 'Reçu',
    cancelled: 'Annulé',
}

const statusClass = {
    draft: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    in_transit: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
    received: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
}

const formatDate = (d) => d ? new Date(d).toLocaleDateString('fr-FR') : '—'
</script>

<template>
    <Head title="Transferts de stock" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Transferts de stock
                </h2>
                <div class="flex gap-3">
                    <Link
                        :href="route('warehouses.index')"
                        class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                    >
                        Entrepôts
                    </Link>
                    <Link
                        :href="route('warehouse-transfers.create')"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition"
                    >
                        + Nouveau transfert
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-3 text-left">Référence</th>
                                    <th class="px-6 py-3 text-left">De</th>
                                    <th class="px-6 py-3 text-left">Vers</th>
                                    <th class="px-6 py-3 text-center">Lignes</th>
                                    <th class="px-6 py-3 text-center">Statut</th>
                                    <th class="px-6 py-3 text-left">Date</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr
                                    v-for="t in transfers.data"
                                    :key="t.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/30"
                                >
                                    <td class="px-6 py-3 font-mono text-gray-800 dark:text-gray-200 font-medium text-xs">
                                        {{ t.reference }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                        {{ t.from_warehouse?.name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-gray-700 dark:text-gray-300">
                                        {{ t.to_warehouse?.name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-center text-gray-500 dark:text-gray-400">
                                        {{ t.lines_count }}
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusClass[t.status]]">
                                            {{ statusLabel[t.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-500 dark:text-gray-400">
                                        {{ formatDate(t.transferred_at ?? t.created_at) }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <Link
                                            :href="route('warehouse-transfers.show', t.id)"
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs"
                                        >
                                            Voir
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="transfers.data.length === 0">
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                        Aucun transfert enregistré.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transfers.last_page > 1" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex gap-2 flex-wrap">
                        <Link
                            v-for="link in transfers.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            v-html="link.label"
                            class="px-3 py-1 text-sm rounded border"
                            :class="link.active
                                ? 'bg-indigo-600 text-white border-indigo-600'
                                : 'border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'"
                        />
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
