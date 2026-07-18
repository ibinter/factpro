<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    movements: Object,
    filters: Object,
    stats: Object,
    alerts: Array,
    products: Array,
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(n ?? 0);
const fmtDate = (d) =>
    new Date(d).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const TYPE_META = {
    in: { label: 'Entrée', class: 'bg-green-100 text-green-700' },
    out: { label: 'Sortie', class: 'bg-red-100 text-red-700' },
    adjustment: { label: 'Ajustement', class: 'bg-amber-100 text-amber-700' },
    inventory: { label: 'Inventaire', class: 'bg-blue-100 text-blue-700' },
};

const signedQty = (m) => {
    const delta = Number(m.stock_after) - Number(m.stock_before);
    const sign = delta > 0 ? '+' : delta < 0 ? '−' : '';
    return `${sign}${fmt(Math.abs(delta || Number(m.quantity)))}`;
};
const qtyClass = (m) => {
    const delta = Number(m.stock_after) - Number(m.stock_before);
    return delta > 0 ? 'text-green-600' : delta < 0 ? 'text-red-600' : 'text-gray-500';
};

/* ---------------- Filtres ---------------- */
const filterForm = ref({
    product_id: props.filters.product_id ?? '',
    type: props.filters.type ?? '',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
});

let filterTimeout = null;
watch(filterForm, (value) => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        const params = Object.fromEntries(Object.entries(value).filter(([, v]) => v !== '' && v !== null));
        router.get(route('stock.index'), params, { preserveState: true, replace: true });
    }, 300);
}, { deep: true });

/* ---------------- Mouvement manuel ---------------- */
const showModal = ref(false);
const trackedProducts = computed(() => props.products.filter((p) => p.track_stock));

const form = useForm({
    product_id: '',
    type: 'in',
    quantity: 1,
    target: 0,
    unit_cost: null,
    reason: '',
});

const selectedProduct = computed(() => trackedProducts.value.find((p) => p.id === form.product_id));

watch(() => form.product_id, () => {
    if (selectedProduct.value) form.target = Number(selectedProduct.value.stock_quantity);
});

const openModal = () => {
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    form.post(route('stock.adjust'), {
        preserveScroll: true,
        onSuccess: () => { showModal.value = false; form.reset(); },
    });
};
</script>

<template>
    <Head title="Gestion des stocks" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Gestion des stocks</h2>
                <div class="flex flex-wrap gap-2">
                    <PrimaryButton @click="openModal">➕ Mouvement manuel</PrimaryButton>
                    <Link :href="route('stock.inventory')">
                        <SecondaryButton>📋 Inventaire</SecondaryButton>
                    </Link>
                    <Link :href="route('stock.valuation')">
                        <SecondaryButton>💰 Valorisation</SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                <!-- Cartes stats -->
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Produits suivis</div>
                        <div class="mt-1 text-2xl font-bold text-brand-900">{{ fmt(stats.tracked_count) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Valeur du stock (CMUP)</div>
                        <div class="mt-1 text-2xl font-bold text-brand-900">{{ fmt(stats.total_value) }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-5 shadow">
                        <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">Produits sous seuil</div>
                        <div class="mt-1 text-2xl font-bold" :class="stats.alert_count > 0 ? 'text-red-600' : 'text-brand-900'">
                            {{ fmt(stats.alert_count) }}
                        </div>
                    </div>
                </div>

                <!-- Alertes stock bas -->
                <div v-if="alerts.length" class="rounded-lg border border-amber-300 bg-amber-50 p-4">
                    <h3 class="mb-2 text-sm font-bold text-amber-800">⚠ Alertes stock bas</h3>
                    <ul class="space-y-1">
                        <li v-for="alert in alerts" :key="alert.id" class="flex items-center justify-between text-sm text-amber-900">
                            <span class="font-semibold">{{ alert.name }}</span>
                            <span>
                                {{ fmt(alert.stock_quantity) }} restant(s)
                                <span class="text-amber-600">(seuil : {{ fmt(alert.stock_alert_threshold) }})</span>
                            </span>
                        </li>
                    </ul>
                </div>

                <!-- Filtres -->
                <div class="grid gap-3 rounded-lg bg-white p-4 shadow sm:grid-cols-4">
                    <div>
                        <InputLabel value="Produit" />
                        <select v-model="filterForm.product_id" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Tous les produits</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Type" />
                        <select v-model="filterForm.type" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">Tous les types</option>
                            <option value="in">Entrée</option>
                            <option value="out">Sortie</option>
                            <option value="adjustment">Ajustement</option>
                            <option value="inventory">Inventaire</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Du" />
                        <TextInput v-model="filterForm.date_from" type="date" class="mt-1 block w-full text-sm" />
                    </div>
                    <div>
                        <InputLabel value="Au" />
                        <TextInput v-model="filterForm.date_to" type="date" class="mt-1 block w-full text-sm" />
                    </div>
                </div>

                <!-- Tableau des mouvements -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Produit</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3 text-right">Quantité</th>
                                    <th class="px-4 py-3 text-right">Stock avant → après</th>
                                    <th class="px-4 py-3">Motif</th>
                                    <th class="px-4 py-3">Document</th>
                                    <th class="px-4 py-3">Auteur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="m in movements.data" :key="m.id" class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-4 py-3 text-gray-500">{{ fmtDate(m.created_at) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-800">{{ m.product?.name ?? '—' }}</div>
                                        <div v-if="m.product?.sku" class="text-xs text-gray-400">{{ m.product.sku }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full px-2 py-0.5 text-xs font-semibold" :class="TYPE_META[m.type]?.class">
                                            {{ TYPE_META[m.type]?.label ?? m.type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold" :class="qtyClass(m)">{{ signedQty(m) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-gray-600">
                                        {{ fmt(m.stock_before) }} → <span class="font-semibold">{{ fmt(m.stock_after) }}</span>
                                    </td>
                                    <td class="max-w-[200px] truncate px-4 py-3 text-gray-500" :title="m.reason">{{ m.reason ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <Link
                                            v-if="m.document"
                                            :href="route('documents.show', m.document.id)"
                                            class="font-semibold text-brand-600 hover:underline"
                                        >
                                            {{ m.document.number }}
                                        </Link>
                                        <span v-else class="text-gray-400">—</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ m.creator?.name ?? '—' }}</td>
                                </tr>
                                <tr v-if="!movements.data.length">
                                    <td colspan="8" class="px-4 py-10 text-center text-gray-400">Aucun mouvement de stock.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="movements.links.length > 3" class="flex flex-wrap gap-1">
                    <template v-for="link in movements.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="rounded px-3 py-1.5 text-sm"
                            :class="link.active ? 'bg-brand-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100'"
                        />
                        <span v-else v-html="link.label" class="px-3 py-1.5 text-sm text-gray-400" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Modale mouvement manuel -->
        <Modal :show="showModal" @close="showModal = false" max-width="lg">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">Mouvement de stock manuel</h3>

                <div class="space-y-4">
                    <div>
                        <InputLabel value="Produit *" />
                        <select v-model="form.product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="" disabled>Choisir un produit suivi…</option>
                            <option v-for="p in trackedProducts" :key="p.id" :value="p.id">
                                {{ p.name }}{{ p.sku ? ` (${p.sku})` : '' }} — stock : {{ fmt(p.stock_quantity) }} {{ p.unit }}
                            </option>
                        </select>
                        <InputError :message="form.errors.product_id" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Type de mouvement *" />
                        <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="in">Entrée (réception, achat…)</option>
                            <option value="out">Sortie (casse, perte, don…)</option>
                            <option value="adjustment">Ajustement (stock cible)</option>
                        </select>
                        <InputError :message="form.errors.type" class="mt-1" />
                    </div>

                    <div v-if="form.type === 'adjustment'">
                        <InputLabel value="Stock cible *" />
                        <TextInput v-model="form.target" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                        <p v-if="selectedProduct" class="mt-1 text-xs text-gray-400">
                            Stock actuel : {{ fmt(selectedProduct.stock_quantity) }} {{ selectedProduct.unit }}
                        </p>
                        <InputError :message="form.errors.target" class="mt-1" />
                    </div>
                    <div v-else>
                        <InputLabel value="Quantité *" />
                        <TextInput v-model="form.quantity" type="number" step="0.01" min="0.01" class="mt-1 block w-full" />
                        <InputError :message="form.errors.quantity" class="mt-1" />
                    </div>

                    <div v-if="form.type === 'in'">
                        <InputLabel value="Coût unitaire (pour le CMUP)" />
                        <TextInput v-model="form.unit_cost" type="number" step="0.01" min="0" class="mt-1 block w-full" placeholder="Optionnel" />
                        <InputError :message="form.errors.unit_cost" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Motif *" />
                        <TextInput v-model="form.reason" class="mt-1 block w-full" placeholder="Ex : réception fournisseur, casse…" required />
                        <InputError :message="form.errors.reason" class="mt-1" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing || !form.product_id" @click="submit">Enregistrer</PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
