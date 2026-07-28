<script setup>
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    warehouses: Array,
    products: Array,
})

const form = useForm({
    from_warehouse_id: '',
    to_warehouse_id: '',
    notes: '',
    lines: [{ product_id: '', quantity_sent: '' }],
})

const addLine = () => {
    form.lines.push({ product_id: '', quantity_sent: '' })
}

const removeLine = (index) => {
    if (form.lines.length > 1) {
        form.lines.splice(index, 1)
    }
}

const destinationWarehouses = computed(() =>
    props.warehouses.filter(w => w.id !== Number(form.from_warehouse_id))
)

const submit = () => {
    form.post(route('warehouse-transfers.store'))
}

const productName = (id) => props.products.find(p => p.id === Number(id))?.name ?? ''
</script>

<template>
    <Head title="Nouveau transfert de stock" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Nouveau transfert de stock
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Entrepôts -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-medium text-gray-800 dark:text-gray-200 mb-4">Entrepôts</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Entrepôt source <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.from_warehouse_id"
                                    required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm"
                                >
                                    <option value="">Sélectionner...</option>
                                    <option v-for="w in warehouses" :key="w.id" :value="w.id">
                                        {{ w.name }} ({{ w.code }})
                                    </option>
                                </select>
                                <p v-if="form.errors.from_warehouse_id" class="text-red-500 text-xs mt-1">{{ form.errors.from_warehouse_id }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Entrepôt destination <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.to_warehouse_id"
                                    required
                                    :disabled="!form.from_warehouse_id"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm disabled:opacity-50"
                                >
                                    <option value="">Sélectionner...</option>
                                    <option v-for="w in destinationWarehouses" :key="w.id" :value="w.id">
                                        {{ w.name }} ({{ w.code }})
                                    </option>
                                </select>
                                <p v-if="form.errors.to_warehouse_id" class="text-red-500 text-xs mt-1">{{ form.errors.to_warehouse_id }}</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea
                                v-model="form.notes"
                                rows="2"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm"
                                placeholder="Remarques optionnelles..."
                            />
                        </div>
                    </div>

                    <!-- Lignes de transfert -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-medium text-gray-800 dark:text-gray-200">Produits à transférer</h3>
                            <button
                                type="button"
                                @click="addLine"
                                class="inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-medium"
                            >
                                + Ajouter une ligne
                            </button>
                        </div>

                        <p v-if="form.errors.lines" class="text-red-500 text-xs mb-3">{{ form.errors.lines }}</p>

                        <div class="space-y-3">
                            <div
                                v-for="(line, idx) in form.lines"
                                :key="idx"
                                class="flex gap-3 items-start"
                            >
                                <div class="flex-1">
                                    <select
                                        v-model="line.product_id"
                                        required
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm"
                                    >
                                        <option value="">Produit...</option>
                                        <option v-for="p in products" :key="p.id" :value="p.id">
                                            {{ p.name }}{{ p.sku ? ' — ' + p.sku : '' }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors[`lines.${idx}.product_id`]" class="text-red-500 text-xs mt-0.5">
                                        {{ form.errors[`lines.${idx}.product_id`] }}
                                    </p>
                                </div>

                                <div class="w-36">
                                    <input
                                        v-model="line.quantity_sent"
                                        type="number"
                                        step="0.001"
                                        min="0.001"
                                        required
                                        placeholder="Quantité"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm"
                                    />
                                    <p v-if="form.errors[`lines.${idx}.quantity_sent`]" class="text-red-500 text-xs mt-0.5">
                                        {{ form.errors[`lines.${idx}.quantity_sent`] }}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    @click="removeLine(idx)"
                                    :disabled="form.lines.length === 1"
                                    class="mt-1 text-red-400 hover:text-red-600 disabled:opacity-30 text-lg leading-none"
                                    title="Supprimer"
                                >
                                    ×
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3 justify-end">
                        <a
                            :href="route('warehouse-transfers.index')"
                            class="px-5 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition"
                        >
                            Annuler
                        </a>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition disabled:opacity-50"
                        >
                            Créer le transfert
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
