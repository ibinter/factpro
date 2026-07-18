<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Array,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(n ?? 0);

const form = useForm({
    items: props.products.map((p) => ({
        product_id: p.id,
        counted: Number(p.stock_quantity),
    })),
});

const gap = (index) => {
    const theoretical = Number(props.products[index].stock_quantity);
    const counted = Number(form.items[index].counted);
    if (Number.isNaN(counted)) return 0;
    return Math.round((counted - theoretical) * 100) / 100;
};

const gapCount = computed(() => form.items.filter((_, i) => gap(i) !== 0).length);

const confirming = ref(false);

const submit = () => {
    form.post(route('stock.inventory.apply'), {
        onFinish: () => (confirming.value = false),
    });
};
</script>

<template>
    <Head title="Inventaire" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">📋 Inventaire physique</h2>
                <Link :href="route('stock.index')">
                    <SecondaryButton>← Retour aux stocks</SecondaryButton>
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
                <p class="text-sm text-gray-500">
                    Saisissez le stock réellement compté pour chaque produit. Seuls les écarts par rapport au stock
                    théorique généreront un mouvement d'inventaire.
                </p>

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Produit</th>
                                <th class="px-6 py-3">SKU</th>
                                <th class="px-6 py-3 text-right">Stock théorique</th>
                                <th class="px-6 py-3 text-right">Stock compté</th>
                                <th class="px-6 py-3 text-right">Écart</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="(product, index) in products" :key="product.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-gray-800">{{ product.name }}</div>
                                    <div class="text-xs text-gray-400">{{ product.unit }}</div>
                                </td>
                                <td class="px-6 py-3 text-gray-500">{{ product.sku ?? '—' }}</td>
                                <td class="px-6 py-3 text-right text-gray-600">{{ fmt(product.stock_quantity) }}</td>
                                <td class="px-6 py-3 text-right">
                                    <input
                                        v-model.number="form.items[index].counted"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-28 rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                    />
                                </td>
                                <td class="px-6 py-3 text-right font-semibold" :class="gap(index) > 0 ? 'text-green-600' : gap(index) < 0 ? 'text-red-600' : 'text-gray-400'">
                                    {{ gap(index) > 0 ? '+' : '' }}{{ fmt(gap(index)) }}
                                </td>
                            </tr>
                            <tr v-if="!products.length">
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">Aucun produit avec suivi de stock.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="products.length" class="flex items-center justify-between rounded-lg bg-white p-4 shadow">
                    <div class="text-sm text-gray-600">
                        <span class="font-bold" :class="gapCount > 0 ? 'text-amber-600' : 'text-green-600'">{{ gapCount }}</span>
                        écart(s) détecté(s) sur {{ products.length }} produit(s)
                    </div>
                    <PrimaryButton :disabled="form.processing" @click="confirming = true">Appliquer l'inventaire</PrimaryButton>
                </div>
            </div>
        </div>

        <Modal :show="confirming" @close="confirming = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Appliquer l'inventaire ?</h3>
                <p class="mt-2 text-sm text-gray-500">
                    <span class="font-bold">{{ gapCount }}</span> écart(s) seront corrigé(s) : le stock de chaque produit
                    concerné sera ajusté à la quantité comptée. Cette opération créera des mouvements d'inventaire traçables.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirming = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submit">Confirmer</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
