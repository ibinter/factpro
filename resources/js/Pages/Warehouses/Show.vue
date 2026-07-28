<script setup>
import { ref } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    warehouse: Object,
    stocks: Object,
})

const showModal = ref(false)
const adjustForm = useForm({
    product_id: '',
    quantity: '',
    reason: '',
})

const openModal = () => {
    adjustForm.reset()
    showModal.value = true
}

const submitAdjust = () => {
    adjustForm.post(route('warehouses.adjust-stock', props.warehouse.id), {
        onSuccess: () => { showModal.value = false }
    })
}

const formatNum = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 3, maximumFractionDigits: 3 }).format(v ?? 0)
const formatCurrency = (v) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2 }).format(v ?? 0)
</script>

<template>
    <Head :title="'Entrepôt ' + warehouse.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ warehouse.name }}
                        <span class="text-sm font-mono text-gray-400 ml-2">{{ warehouse.code }}</span>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ [warehouse.city, warehouse.manager_name].filter(Boolean).join(' · ') }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="openModal"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition"
                    >
                        Ajuster stock
                    </button>
                    <Link
                        :href="route('warehouses.edit', warehouse.id)"
                        class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                    >
                        Modifier
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="font-medium text-gray-800 dark:text-gray-200">Stocks en entrepôt</h3>
                        <span class="text-sm text-gray-400 dark:text-gray-500">{{ stocks.total }} produit(s)</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <tr>
                                    <th class="px-6 py-3 text-left">Produit</th>
                                    <th class="px-6 py-3 text-left">SKU</th>
                                    <th class="px-6 py-3 text-right">Quantité</th>
                                    <th class="px-6 py-3 text-right">Qté min</th>
                                    <th class="px-6 py-3 text-right">Valeur</th>
                                    <th class="px-6 py-3 text-center">Alerte</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr
                                    v-for="stock in stocks.data"
                                    :key="stock.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/30"
                                >
                                    <td class="px-6 py-3 text-gray-800 dark:text-gray-200 font-medium">
                                        {{ stock.product?.name ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 font-mono text-gray-400 dark:text-gray-500 text-xs">
                                        {{ stock.product?.sku ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-right" :class="stock.is_low ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-700 dark:text-gray-300'">
                                        {{ formatNum(stock.quantity) }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-gray-500 dark:text-gray-400">
                                        {{ formatNum(stock.min_quantity) }}
                                    </td>
                                    <td class="px-6 py-3 text-right text-gray-700 dark:text-gray-300">
                                        {{ formatCurrency(stock.value) }}
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span v-if="stock.is_low" class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 font-medium">
                                            Stock bas
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="stocks.data.length === 0">
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-400 dark:text-gray-500">
                                        Aucun stock enregistré dans cet entrepôt.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="stocks.last_page > 1" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex gap-2">
                        <Link
                            v-for="link in stocks.links"
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

        <!-- Modal ajuster stock -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 text-lg mb-4">Ajuster le stock</h3>

                <form @submit.prevent="submitAdjust" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Produit</label>
                        <select
                            v-model="adjustForm.product_id"
                            required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm"
                        >
                            <option value="">Sélectionner un produit</option>
                            <option v-for="s in stocks.data" :key="s.product_id" :value="s.product_id">
                                {{ s.product?.name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Delta (+ ajout / - retrait)</label>
                        <input
                            v-model="adjustForm.quantity"
                            type="number"
                            step="0.001"
                            required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Raison</label>
                        <input
                            v-model="adjustForm.reason"
                            type="text"
                            required
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm"
                            placeholder="Inventaire, correction, ..."
                        />
                    </div>
                    <div v-if="adjustForm.errors.product_id || adjustForm.errors.quantity || adjustForm.errors.reason" class="text-red-600 text-sm">
                        {{ adjustForm.errors.product_id || adjustForm.errors.quantity || adjustForm.errors.reason }}
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button
                            type="submit"
                            :disabled="adjustForm.processing"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 rounded-lg transition disabled:opacity-50"
                        >
                            Enregistrer
                        </button>
                        <button
                            type="button"
                            @click="showModal = false"
                            class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium py-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition"
                        >
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
