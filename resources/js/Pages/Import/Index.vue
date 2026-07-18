<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    hasBusiness: Boolean,
});

// ─── Onglets ────────────────────────────────────────────────────────────────
const activeTab = ref('customers');

// ─── État par onglet ─────────────────────────────────────────────────────────
const state = ref({
    customers: { step: 1, file: null, preview: null, columnMap: {}, result: null, loading: false, error: null },
    products:  { step: 1, file: null, preview: null, columnMap: {}, result: null, loading: false, error: null },
});

const s = computed(() => state.value[activeTab.value]);

// ─── Champs cibles ────────────────────────────────────────────────────────────
const customerFields = [
    { key: 'name',    label: 'Nom *' },
    { key: 'email',   label: 'Email' },
    { key: 'phone',   label: 'Téléphone' },
    { key: 'address', label: 'Adresse' },
    { key: 'city',    label: 'Ville' },
    { key: 'country', label: 'Pays (2 lettres)' },
    { key: 'tax_id',  label: 'SIRET/RCCM' },
];

const productFields = [
    { key: 'name',          label: 'Nom *' },
    { key: 'sku',           label: 'Référence' },
    { key: 'description',   label: 'Description' },
    { key: 'price',         label: 'Prix HT' },
    { key: 'unit',          label: 'Unité' },
    { key: 'tax_rate',      label: 'TVA %' },
    { key: 'stock_quantity',label: 'Stock initial' },
];

const targetFields = computed(() =>
    activeTab.value === 'customers' ? customerFields : productFields
);

// ─── Drag & drop ─────────────────────────────────────────────────────────────
const isDragging = ref(false);

function onDrop(e) {
    isDragging.value = false;
    const file = e.dataTransfer?.files?.[0] ?? null;
    if (file) setFile(file);
}

function onFileInput(e) {
    const file = e.target.files?.[0] ?? null;
    if (file) setFile(file);
}

function setFile(file) {
    s.value.file = file;
    s.value.error = null;
}

// ─── Upload (étape 1 → 2) ────────────────────────────────────────────────────
async function upload() {
    if (! s.value.file) return;

    s.value.loading = true;
    s.value.error   = null;

    const form = new FormData();
    form.append('file', s.value.file);

    const url = activeTab.value === 'customers'
        ? route('import.customers.upload')
        : route('import.products.upload');

    try {
        const { data } = await axios.post(url, form, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        s.value.preview = data;

        // Auto-mapping : tenter de faire correspondre les en-têtes aux champs
        const autoMap = {};
        const aliases = {
            customers: {
                nom: 'name', name: 'name',
                email: 'email', courriel: 'email',
                'téléphone': 'phone', telephone: 'phone', phone: 'phone',
                adresse: 'address', address: 'address',
                ville: 'city', city: 'city',
                pays: 'country', country: 'country',
                siret: 'tax_id', rccm: 'tax_id', 'siret/rccm': 'tax_id', 'tva n°': 'tax_id',
            },
            products: {
                nom: 'name', name: 'name',
                référence: 'sku', reference: 'sku', sku: 'sku', ref: 'sku',
                description: 'description',
                'prix ht': 'price', prix: 'price', price: 'price',
                unité: 'unit', unite: 'unit', unit: 'unit',
                'tva %': 'tax_rate', tva: 'tax_rate', 'tax_rate': 'tax_rate',
                'stock initial': 'stock_quantity', stock: 'stock_quantity',
            },
        };
        const map = aliases[activeTab.value];

        data.headers.forEach((h, idx) => {
            const key = map[h.toLowerCase().trim()];
            if (key) autoMap[key] = idx;
        });

        s.value.columnMap = autoMap;
        s.value.step      = 2;
    } catch (err) {
        s.value.error = err.response?.data?.message
            ?? err.response?.data?.error
            ?? 'Erreur lors du chargement du fichier.';
    } finally {
        s.value.loading = false;
    }
}

// ─── Import (étape 3 → 4) ─────────────────────────────────────────────────────
async function executeImport() {
    s.value.loading = true;
    s.value.error   = null;

    const url = activeTab.value === 'customers'
        ? route('import.customers.execute')
        : route('import.products.execute');

    try {
        const { data } = await axios.post(url, {
            tmp_path:   s.value.preview.tmp_path,
            column_map: s.value.columnMap,
        });
        s.value.result = data;
        s.value.step   = 4;
    } catch (err) {
        s.value.error = err.response?.data?.message
            ?? err.response?.data?.error
            ?? "Erreur lors de l'import.";
    } finally {
        s.value.loading = false;
    }
}

// ─── Reset ────────────────────────────────────────────────────────────────────
function reset() {
    state.value[activeTab.value] = {
        step: 1, file: null, preview: null, columnMap: {}, result: null, loading: false, error: null,
    };
}

// ─── Template CSV ─────────────────────────────────────────────────────────────
function downloadTemplate() {
    const url = activeTab.value === 'customers'
        ? route('import.templates.customers')
        : route('import.templates.products');
    window.location.href = url;
}

// ─── Prévisualisation 5 premières lignes ─────────────────────────────────────
const previewRows = computed(() => (s.value.preview?.preview ?? []).slice(0, 5));
</script>

<template>
    <Head title="Import CSV" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                📥 Import CSV
            </h2>
        </template>

        <div class="py-8 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Upsell si non-BUSINESS+ -->
            <div v-if="!hasBusiness"
                 class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl p-6 text-center">
                <p class="text-2xl mb-2">🔒</p>
                <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200 mb-1">
                    Fonctionnalité BUSINESS+
                </h3>
                <p class="text-amber-700 dark:text-amber-300 mb-4 text-sm">
                    L'import en masse de clients et produits est réservé aux forfaits BUSINESS et ENTERPRISE.
                </p>
                <a :href="route('billing.plans')"
                   class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                    Passer au forfait supérieur
                </a>
            </div>

            <!-- Interface principale -->
            <div v-else>

                <!-- Onglets -->
                <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-700">
                    <button
                        v-for="tab in [{ id: 'customers', label: '👥 Clients' }, { id: 'products', label: '📦 Produits' }]"
                        :key="tab.id"
                        @click="activeTab = tab.id; reset()"
                        :class="[
                            'px-5 py-2.5 font-medium text-sm rounded-t-lg transition',
                            activeTab === tab.id
                                ? 'bg-white dark:bg-gray-800 border border-b-white dark:border-gray-700 dark:border-b-gray-800 text-indigo-600 dark:text-indigo-400'
                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <!-- Étapes breadcrumb -->
                <div class="flex items-center gap-2 text-sm mb-8 text-gray-500 dark:text-gray-400">
                    <span v-for="(step, idx) in ['Fichier', 'Mapping', 'Prévisualisation', 'Résultats']" :key="idx"
                          :class="[
                              'px-3 py-1 rounded-full text-xs font-medium',
                              s.step === idx + 1 ? 'bg-indigo-600 text-white' :
                              s.step > idx + 1 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' :
                              'bg-gray-100 dark:bg-gray-700 text-gray-500'
                          ]">
                        {{ idx + 1 }}. {{ step }}
                    </span>
                </div>

                <!-- Erreur globale -->
                <div v-if="s.error"
                     class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg text-red-700 dark:text-red-400 text-sm">
                    {{ s.error }}
                </div>

                <!-- Loading bar -->
                <div v-if="s.loading" class="mb-4">
                    <div class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-600 rounded-full animate-pulse w-3/4"></div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-center">Traitement en cours…</p>
                </div>

                <!-- ── Étape 1 : Upload ─────────────────────────────────── -->
                <div v-if="s.step === 1">
                    <div
                        class="border-2 border-dashed rounded-xl p-12 text-center transition cursor-pointer"
                        :class="isDragging
                            ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20'
                            : 'border-gray-300 dark:border-gray-600 hover:border-indigo-400'"
                        @dragover.prevent="isDragging = true"
                        @dragleave="isDragging = false"
                        @drop.prevent="onDrop"
                    >
                        <div class="text-4xl mb-3">📄</div>
                        <p class="text-gray-600 dark:text-gray-300 font-medium mb-1">
                            Glissez votre fichier CSV ici
                        </p>
                        <p class="text-gray-400 dark:text-gray-500 text-sm mb-4">ou</p>
                        <label class="cursor-pointer inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2 rounded-lg transition text-sm">
                            Choisir un fichier
                            <input type="file" accept=".csv,.txt" class="hidden" @change="onFileInput">
                        </label>
                        <p v-if="s.file" class="mt-3 text-sm text-green-600 dark:text-green-400">
                            ✅ {{ s.file.name }} ({{ (s.file.size / 1024).toFixed(1) }} Ko)
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">Max 2 Mo — format CSV UTF-8</p>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <button
                            v-if="s.file"
                            @click="upload"
                            :disabled="s.loading"
                            class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-medium px-6 py-2 rounded-lg transition text-sm">
                            Analyser le fichier →
                        </button>
                        <button
                            @click="downloadTemplate"
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                            ⬇️ Télécharger le template CSV
                        </button>
                    </div>
                </div>

                <!-- ── Étape 2 : Mapping ────────────────────────────────── -->
                <div v-if="s.step === 2 && s.preview">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">
                        Correspondance des colonnes
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        {{ s.preview.total }} ligne(s) détectée(s).
                        Faites correspondre chaque colonne de votre fichier avec un champ FactPro.
                    </p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Colonne CSV</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Exemple</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Champ FactPro</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="(header, idx) in s.preview.headers" :key="idx"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-2 font-medium text-gray-800 dark:text-gray-200">{{ header }}</td>
                                    <td class="px-4 py-2 text-gray-500 dark:text-gray-400 text-xs">
                                        {{ s.preview.preview[0]?.[idx] ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <select
                                            class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-2 py-1"
                                            :value="Object.entries(s.columnMap).find(([, v]) => v === idx)?.[0] ?? ''"
                                            @change="e => {
                                                const field = e.target.value;
                                                // Retirer l'ancien mapping pour cet index
                                                Object.keys(s.columnMap).forEach(k => { if (s.columnMap[k] === idx) delete s.columnMap[k]; });
                                                if (field) s.columnMap[field] = idx;
                                            }"
                                        >
                                            <option value="">— Ignorer —</option>
                                            <option v-for="f in targetFields" :key="f.key" :value="f.key">
                                                {{ f.label }}
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex gap-3">
                        <button @click="s.step = 3" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg transition text-sm">
                            Prévisualiser →
                        </button>
                        <button @click="reset" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            ← Recommencer
                        </button>
                    </div>
                </div>

                <!-- ── Étape 3 : Prévisualisation ───────────────────────── -->
                <div v-if="s.step === 3 && s.preview">
                    <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-1">
                        Prévisualisation (5 premières lignes)
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Vérifiez que les données semblent correctes avant de lancer l'import.
                    </p>

                    <div class="overflow-x-auto mb-4">
                        <table class="min-w-full text-sm bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th v-for="(field, key) in s.columnMap" :key="key"
                                        class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">
                                        {{ targetFields.find(f => f.key === key)?.label ?? key }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                <tr v-for="(row, i) in previewRows" :key="i"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td v-for="(colIdx, key) in s.columnMap" :key="key"
                                        class="px-4 py-2 text-gray-700 dark:text-gray-300">
                                        {{ row[colIdx] ?? '—' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="s.preview.errors?.length" class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                        <p class="text-xs font-semibold text-yellow-700 dark:text-yellow-400 mb-1">Avertissements :</p>
                        <ul class="text-xs text-yellow-600 dark:text-yellow-300 list-disc list-inside">
                            <li v-for="(e, i) in s.preview.errors" :key="i">{{ e }}</li>
                        </ul>
                    </div>

                    <div class="flex gap-3">
                        <button
                            @click="executeImport"
                            :disabled="s.loading"
                            class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white font-medium px-6 py-2 rounded-lg transition text-sm">
                            ✅ Lancer l'import ({{ s.preview.total }} lignes)
                        </button>
                        <button @click="s.step = 2" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">
                            ← Modifier le mapping
                        </button>
                    </div>
                </div>

                <!-- ── Étape 4 : Résultats ──────────────────────────────── -->
                <div v-if="s.step === 4 && s.result">
                    <div v-if="s.result.queued" class="text-center py-12">
                        <p class="text-4xl mb-3">⏳</p>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Import en cours…</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">{{ s.result.message }}</p>
                    </div>
                    <div v-else>
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-4">Résultats de l'import</h3>
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl p-4 text-center">
                                <p class="text-3xl font-bold text-green-700 dark:text-green-400">{{ s.result.imported }}</p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">Importé(s)</p>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-xl p-4 text-center">
                                <p class="text-3xl font-bold text-yellow-700 dark:text-yellow-400">{{ s.result.skipped }}</p>
                                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Ignoré(s)</p>
                            </div>
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl p-4 text-center">
                                <p class="text-3xl font-bold text-red-700 dark:text-red-400">{{ s.result.errors?.length ?? 0 }}</p>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">Erreur(s)</p>
                            </div>
                        </div>

                        <div v-if="s.result.errors?.length" class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg">
                            <p class="text-xs font-semibold text-red-700 dark:text-red-400 mb-2">Détail des erreurs :</p>
                            <ul class="text-xs text-red-600 dark:text-red-300 list-disc list-inside space-y-0.5">
                                <li v-for="(e, i) in s.result.errors" :key="i">{{ e }}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-4">
                        <button @click="reset" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2 rounded-lg transition text-sm">
                            📥 Nouvel import
                        </button>
                        <a :href="activeTab === 'customers' ? route('customers.index') : route('products.index')"
                           class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline self-center">
                            Voir la liste →
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
