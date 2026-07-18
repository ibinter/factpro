<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    products: Object,
    filters: Object,
});

const page = usePage();
const defaultTax = computed(() => Number(page.props.company?.default_tax_rate ?? 18));

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

const search = ref(props.filters.search ?? '');
let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('products.index'), { search: value }, { preserveState: true, replace: true });
    }, 350);
});

const showModal = ref(false);
const editing = ref(null);
const confirmingDelete = ref(null);

const form = useForm({
    type: 'product',
    name: '',
    sku: '',
    barcode: '',
    description: '',
    unit: 'unité',
    price: 0,
    cost: 0,
    tax_rate: defaultTax.value,
    track_stock: false,
    stock_quantity: 0,
    stock_alert_threshold: null,
    is_active: true,
});

const openCreate = () => {
    editing.value = null;
    form.reset();
    form.tax_rate = defaultTax.value;
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (product) => {
    editing.value = product;
    Object.keys(form.data()).forEach((key) => {
        if (product[key] !== undefined && product[key] !== null) form[key] = product[key];
    });
    form.clearErrors();
    showModal.value = true;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => { showModal.value = false; form.reset(); },
    };
    if (editing.value) {
        form.put(route('products.update', editing.value.id), options);
    } else {
        form.post(route('products.store'), options);
    }
};

const destroy = () => {
    router.delete(route('products.destroy', confirmingDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => (confirmingDelete.value = null),
    });
};
</script>

<template>
    <Head title="Produits & Services" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Produits & Services</h2>
                <div class="flex items-center gap-2">
                    <a :href="route('import.index')" class="inline-flex items-center gap-1 rounded-md border border-indigo-300 bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100 transition">
                        📥 Importer CSV
                    </a>
                    <PrimaryButton @click="openCreate">+ Nouveau produit</PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                <input
                    v-model="search"
                    type="search"
                    placeholder="Rechercher (nom, SKU, code-barres)…"
                    class="w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
                />

                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Produit</th>
                                <th class="px-6 py-3">SKU</th>
                                <th class="px-6 py-3 text-right">Prix</th>
                                <th class="px-6 py-3 text-right">TVA %</th>
                                <th class="px-6 py-3 text-right">Stock</th>
                                <th class="px-6 py-3">Statut</th>
                                <th class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="font-semibold text-gray-800">{{ product.name }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ product.type === 'service' ? 'Service' : 'Produit' }} · {{ product.unit }}
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-gray-500">{{ product.sku ?? '—' }}</td>
                                <td class="px-6 py-3 text-right font-semibold">{{ fmt(product.price) }}</td>
                                <td class="px-6 py-3 text-right">{{ Number(product.tax_rate) }}</td>
                                <td class="px-6 py-3 text-right">
                                    <span v-if="product.track_stock" :class="Number(product.stock_quantity) <= Number(product.stock_alert_threshold ?? 0) ? 'font-bold text-red-600' : ''">
                                        {{ Number(product.stock_quantity) }}
                                    </span>
                                    <span v-else class="text-gray-400">—</span>
                                </td>
                                <td class="px-6 py-3">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                    >
                                        {{ product.is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <button class="text-sm font-semibold text-brand-600 hover:underline" @click="openEdit(product)">Modifier</button>
                                    <button class="ml-3 text-sm font-semibold text-red-500 hover:underline" @click="confirmingDelete = product">Supprimer</button>
                                </td>
                            </tr>
                            <tr v-if="!products.data.length">
                                <td colspan="7" class="px-6 py-10 text-center text-gray-400">Aucun produit trouvé.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="products.links.length > 3" class="flex flex-wrap gap-1">
                    <template v-for="link in products.links" :key="link.label">
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

        <Modal :show="showModal" @close="showModal = false" max-width="2xl">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-800">
                    {{ editing ? 'Modifier le produit' : 'Nouveau produit / service' }}
                </h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Type" />
                        <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="product">Produit</option>
                            <option value="service">Service</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Nom *" />
                        <TextInput v-model="form.name" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.name" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="SKU / Référence" />
                        <TextInput v-model="form.sku" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Code-barres (EAN-13)" />
                        <TextInput v-model="form.barcode" class="mt-1 block w-full" />
                    </div>
                    <div class="sm:col-span-2">
                        <InputLabel value="Description" />
                        <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                    </div>
                    <div>
                        <InputLabel value="Prix de vente *" />
                        <TextInput v-model="form.price" type="number" step="0.01" min="0" class="mt-1 block w-full" required />
                        <InputError :message="form.errors.price" class="mt-1" />
                    </div>
                    <div>
                        <InputLabel value="Coût d'achat" />
                        <TextInput v-model="form.cost" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="TVA (%)" />
                        <TextInput v-model="form.tax_rate" type="number" step="0.1" min="0" max="100" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Unité" />
                        <TextInput v-model="form.unit" class="mt-1 block w-full" placeholder="unité, kg, heure…" />
                    </div>
                    <div class="flex items-center gap-6 sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" v-model="form.track_stock" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                            Suivre le stock
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" v-model="form.is_active" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                            Actif au catalogue
                        </label>
                    </div>
                    <template v-if="form.track_stock">
                        <div>
                            <InputLabel value="Quantité en stock" />
                            <TextInput v-model="form.stock_quantity" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Seuil d'alerte" />
                            <TextInput v-model="form.stock_alert_threshold" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                        </div>
                    </template>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showModal = false">Annuler</SecondaryButton>
                    <PrimaryButton :disabled="form.processing" @click="submit">
                        {{ editing ? 'Enregistrer' : 'Créer le produit' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="!!confirmingDelete" @close="confirmingDelete = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800">Supprimer ce produit ?</h3>
                <p class="mt-2 text-sm text-gray-500">« {{ confirmingDelete?.name }} » sera retiré du catalogue.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="confirmingDelete = null">Annuler</SecondaryButton>
                    <DangerButton @click="destroy">Supprimer</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
