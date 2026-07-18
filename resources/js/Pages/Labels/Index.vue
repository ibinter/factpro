<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, reactive } from 'vue';

const props = defineProps({
    hasAccess: Boolean,
    products: { type: Array, default: () => [] },
    formats: { type: Array, default: () => [] },
    currency: { type: String, default: 'XOF' },
});

const fmt = (n) => new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(n ?? 0);

/* ---- Sélection produits ---- */
const selected = reactive({}); // id -> { checked, quantity }
props.products.forEach((p) => {
    selected[p.id] = { checked: false, quantity: 1 };
});

const allChecked = computed({
    get: () => props.products.length > 0 && props.products.every((p) => selected[p.id]?.checked),
    set: (value) => props.products.forEach((p) => (selected[p.id].checked = value)),
});

const selectedItems = computed(() =>
    props.products
        .filter((p) => selected[p.id]?.checked)
        .map((p) => ({ product_id: p.id, quantity: Math.max(1, Math.min(500, parseInt(selected[p.id].quantity) || 1)) })),
);

/* ---- Options ---- */
const format = ref('avery-l7160');
const custom = reactive({ width_mm: 70, height_mm: 35, cols: 3, rows: 8 });
const options = reactive({
    show_name: true,
    show_price: true,
    show_barcode: true,
    show_qr: false,
    show_sku: true,
    guides: false,
});

const perPage = computed(() => {
    if (format.value === 'custom') {
        return Math.max(1, (parseInt(custom.cols) || 1) * (parseInt(custom.rows) || 1));
    }
    return props.formats.find((f) => f.key === format.value)?.per_page ?? 1;
});

const totalLabels = computed(() => selectedItems.value.reduce((sum, item) => sum + item.quantity, 0));
const totalPages = computed(() => Math.ceil(totalLabels.value / perPage.value) || 0);

/* ---- Génération PDF (axios blob → nouvel onglet) ---- */
const generating = ref(false);
const error = ref('');

const generate = () => {
    if (!selectedItems.value.length || generating.value) return;
    generating.value = true;
    error.value = '';

    const payload = {
        format: format.value,
        items: selectedItems.value,
        ...options,
    };
    if (format.value === 'custom') {
        payload.width_mm = custom.width_mm;
        payload.height_mm = custom.height_mm;
        payload.cols = custom.cols;
        payload.rows = custom.rows;
    }

    window.axios
        .post(route('labels.pdf'), payload, { responseType: 'blob' })
        .then((response) => {
            const url = URL.createObjectURL(new Blob([response.data], { type: 'application/pdf' }));
            window.open(url, '_blank');
        })
        .catch(async (e) => {
            if (e.response?.data instanceof Blob) {
                try {
                    const json = JSON.parse(await e.response.data.text());
                    error.value = json.message || 'Erreur lors de la génération du PDF.';
                } catch {
                    error.value = 'Erreur lors de la génération du PDF.';
                }
            } else {
                error.value = 'Erreur lors de la génération du PDF.';
            }
        })
        .finally(() => (generating.value = false));
};
</script>

<template>
    <Head title="Étiquettes & codes-barres" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Étiquettes & codes-barres</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Upsell si forfait insuffisant -->
                <div v-if="!hasAccess" class="mx-auto max-w-2xl rounded-lg bg-white p-8 text-center shadow">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-gold-400/20 text-3xl">🏷️</div>
                    <h3 class="text-lg font-semibold text-brand-900">Étiquettes disponibles à partir du forfait BUSINESS</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Imprimez en masse vos étiquettes produits (codes-barres EAN/Code 128, QR, nom et prix)
                        sur planches Avery ou en format personnalisé, avec les forfaits BUSINESS et ENTERPRISE.
                    </p>
                    <Link :href="route('billing.plans')" class="mt-6 inline-block">
                        <PrimaryButton>Voir les forfaits</PrimaryButton>
                    </Link>
                </div>

                <!-- Composeur d'étiquettes -->
                <div v-else class="grid gap-6 lg:grid-cols-3">
                    <!-- Gauche : produits -->
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden rounded-lg bg-white shadow">
                            <div class="flex items-center justify-between border-b px-4 py-3">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                    <input type="checkbox" v-model="allChecked" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    Tout sélectionner
                                </label>
                                <span class="text-sm text-gray-500">{{ products.length }} produit(s) actif(s)</span>
                            </div>
                            <div class="max-h-[32rem] overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead class="sticky top-0 bg-gray-50 text-left text-xs uppercase text-gray-500">
                                        <tr>
                                            <th class="w-10 px-4 py-2"></th>
                                            <th class="px-2 py-2">Produit</th>
                                            <th class="px-2 py-2">SKU / Code-barres</th>
                                            <th class="px-2 py-2 text-right">Prix</th>
                                            <th class="w-24 px-4 py-2 text-center">Qté</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50">
                                            <td class="px-4 py-2">
                                                <input type="checkbox" v-model="selected[product.id].checked" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" />
                                            </td>
                                            <td class="px-2 py-2">
                                                <div class="font-medium text-gray-800">{{ product.name }}</div>
                                                <span v-if="product.track_stock" class="mt-0.5 inline-block rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">
                                                    Stock : {{ fmt(product.stock_quantity) }} {{ product.unit }}
                                                </span>
                                            </td>
                                            <td class="px-2 py-2 text-xs text-gray-500">
                                                <div>{{ product.sku || '—' }}</div>
                                                <div v-if="product.barcode" class="font-mono">{{ product.barcode }}</div>
                                            </td>
                                            <td class="px-2 py-2 text-right font-semibold text-gray-800">{{ fmt(product.price) }} {{ currency }}</td>
                                            <td class="px-4 py-2 text-center">
                                                <input
                                                    type="number" min="1" max="500"
                                                    v-model.number="selected[product.id].quantity"
                                                    class="w-20 rounded-md border-gray-300 text-center text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
                                                />
                                            </td>
                                        </tr>
                                        <tr v-if="!products.length">
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">Aucun produit actif. Créez d'abord vos produits.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Droite : options -->
                    <div class="space-y-4">
                        <div class="rounded-lg bg-white p-4 shadow">
                            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Format de planche</h3>
                            <div class="space-y-2">
                                <label
                                    v-for="f in formats" :key="f.key"
                                    class="flex cursor-pointer items-start gap-3 rounded-md border p-3 transition"
                                    :class="format === f.key ? 'border-brand-600 bg-brand-600/5 ring-1 ring-brand-600' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <input type="radio" v-model="format" :value="f.key" class="mt-0.5 border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    <span>
                                        <span class="block text-sm font-medium text-gray-800">{{ f.label }}</span>
                                        <span class="block text-xs text-gray-500">{{ f.description }}</span>
                                    </span>
                                </label>
                                <label
                                    class="flex cursor-pointer items-start gap-3 rounded-md border p-3 transition"
                                    :class="format === 'custom' ? 'border-brand-600 bg-brand-600/5 ring-1 ring-brand-600' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <input type="radio" v-model="format" value="custom" class="mt-0.5 border-gray-300 text-brand-600 focus:ring-brand-500" />
                                    <span>
                                        <span class="block text-sm font-medium text-gray-800">Personnalisé</span>
                                        <span class="block text-xs text-gray-500">Dimensions et grille libres (A4)</span>
                                    </span>
                                </label>
                            </div>

                            <div v-if="format === 'custom'" class="mt-3 grid grid-cols-2 gap-3">
                                <div>
                                    <InputLabel value="Largeur (mm)" class="text-xs" />
                                    <TextInput v-model.number="custom.width_mm" type="number" min="20" max="210" class="mt-1 w-full text-sm" />
                                </div>
                                <div>
                                    <InputLabel value="Hauteur (mm)" class="text-xs" />
                                    <TextInput v-model.number="custom.height_mm" type="number" min="15" max="297" class="mt-1 w-full text-sm" />
                                </div>
                                <div>
                                    <InputLabel value="Colonnes" class="text-xs" />
                                    <TextInput v-model.number="custom.cols" type="number" min="1" max="6" class="mt-1 w-full text-sm" />
                                </div>
                                <div>
                                    <InputLabel value="Lignes" class="text-xs" />
                                    <TextInput v-model.number="custom.rows" type="number" min="1" max="15" class="mt-1 w-full text-sm" />
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg bg-white p-4 shadow">
                            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Contenu des étiquettes</h3>
                            <div class="space-y-2 text-sm text-gray-700">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" v-model="options.show_name" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" /> Nom du produit
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" v-model="options.show_price" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" /> Prix
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" v-model="options.show_barcode" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" /> Code-barres (EAN / Code 128)
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" v-model="options.show_qr" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" /> QR code produit
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" v-model="options.show_sku" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" /> SKU sous le code
                                </label>
                                <label class="mt-2 flex items-center gap-2 border-t pt-2">
                                    <input type="checkbox" v-model="options.guides" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" /> Guides de découpe (pointillés)
                                </label>
                            </div>
                            <p v-if="format === 'avery-l7160' && options.show_qr" class="mt-2 text-xs text-amber-600">
                                Étiquettes trop petites pour le QR sur ce format : nom, prix et code-barres seulement.
                            </p>
                        </div>

                        <div class="rounded-lg bg-brand-900 p-4 text-white shadow">
                            <div class="flex items-center justify-between text-sm">
                                <span>Étiquettes sélectionnées</span>
                                <span class="text-lg font-bold text-gold-400">{{ totalLabels }}</span>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-sm">
                                <span>Pages estimées ({{ perPage }}/page)</span>
                                <span class="font-semibold">{{ totalPages }}</span>
                            </div>
                            <button
                                type="button"
                                class="mt-4 w-full rounded-md bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-500 disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="!totalLabels || generating"
                                @click="generate"
                            >
                                <span v-if="generating">Génération…</span>
                                <span v-else>🖨 Générer le PDF</span>
                            </button>
                            <p v-if="error" class="mt-2 text-xs text-red-300">{{ error }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
