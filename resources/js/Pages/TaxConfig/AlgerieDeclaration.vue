<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    declaration: { type: Object, default: null },
    month: { type: Number, default: new Date().getMonth() + 1 },
    year: { type: Number, default: new Date().getFullYear() },
});

const selectedMonth = ref(props.month);
const selectedYear  = ref(props.year);

const months = [
    { value: 1, label: 'Janvier' }, { value: 2, label: 'Février' },
    { value: 3, label: 'Mars' },    { value: 4, label: 'Avril' },
    { value: 5, label: 'Mai' },     { value: 6, label: 'Juin' },
    { value: 7, label: 'Juillet' }, { value: 8, label: 'Août' },
    { value: 9, label: 'Septembre' }, { value: 10, label: 'Octobre' },
    { value: 11, label: 'Novembre' }, { value: 12, label: 'Décembre' },
];

function reload() {
    router.get(route('tax-config.algerie'), {
        month: selectedMonth.value,
        year: selectedYear.value,
    }, { preserveState: false });
}

const d = computed(() => props.declaration ?? {});
const fmt = (n) => new Intl.NumberFormat('fr-DZ', { minimumFractionDigits: 2 }).format(Number(n ?? 0));
</script>

<template>
    <Head title="Déclaration G50 Algérie" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                Déclaration G50 — Direction Générale des Impôts (DGI-DZ)
            </h2>
        </template>

        <div class="py-8 max-w-3xl mx-auto px-4 space-y-6">

            <!-- Sélecteur période -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-4">Période G50</h3>
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mois</label>
                        <select
                            v-model.number="selectedMonth"
                            class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm"
                        >
                            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Année</label>
                        <input
                            v-model.number="selectedYear"
                            type="number" min="2020" max="2099"
                            class="w-24 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm"
                        />
                    </div>
                    <button
                        @click="reload"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700"
                    >
                        Calculer
                    </button>
                    <a
                        :href="route('tax-config.api.algerie') + '?month=' + selectedMonth + '&year=' + selectedYear"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700"
                        target="_blank"
                    >
                        Brouillon G50 (JSON)
                    </a>
                </div>
            </div>

            <!-- Formulaire G50 simplifié -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-1">
                    Formulaire G50 — Déclaration mensuelle des impôts et taxes
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    Brouillon simplifié — à valider et déposer sur le portail Jibayatic DGI Algérie
                </p>

                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b-2 border-gray-300 dark:border-gray-600">
                            <th class="py-2 pr-4 font-medium text-gray-600 dark:text-gray-400">Rubrique G50</th>
                            <th class="py-2 text-right font-medium text-gray-600 dark:text-gray-400">Montant (DZD)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                            <td class="py-2 pr-4 font-medium text-gray-800 dark:text-gray-200" colspan="2">
                                Section TVA
                            </td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 pr-4 pl-4 text-gray-800 dark:text-gray-200">Chiffre d'affaires HT</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt(d.ca_ht) }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 pr-4 pl-4 text-gray-800 dark:text-gray-200">TVA collectée (19% / 9%)</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt(d.tva_collectee) }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 pr-4 pl-4 text-gray-800 dark:text-gray-200">TVA déductible</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt(d.tva_deductible) }}</td>
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-600">
                            <td class="py-2 pr-4 pl-4 font-medium text-gray-800 dark:text-gray-200">TVA nette à reverser</td>
                            <td class="py-2 text-right font-medium text-gray-700 dark:text-gray-300">{{ fmt(d.tva_nette) }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
                            <td class="py-2 pr-4 font-medium text-gray-800 dark:text-gray-200" colspan="2">
                                Section TAP (Taxe sur l'Activité Professionnelle — 2%)
                            </td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 pr-4 pl-4 text-gray-800 dark:text-gray-200">Base TAP (CA HT)</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt(d.ca_ht) }}</td>
                        </tr>
                        <tr class="border-b border-gray-200 dark:border-gray-600">
                            <td class="py-2 pr-4 pl-4 font-medium text-gray-800 dark:text-gray-200">TAP à payer (2%)</td>
                            <td class="py-2 text-right font-medium text-gray-700 dark:text-gray-300">{{ fmt(d.tap_a_payer) }}</td>
                        </tr>
                        <tr class="font-bold text-gray-900 dark:text-gray-100 text-base border-t-2 border-gray-300 dark:border-gray-600">
                            <td class="pt-3 pr-4">Total à verser au Trésor</td>
                            <td class="pt-3 text-right text-indigo-600 dark:text-indigo-400">{{ fmt(d.total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Notes légales DZ -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 text-sm text-blue-800 dark:text-blue-200 space-y-1">
                <p class="font-medium">Mentions obligatoires sur factures — Algérie (Code des Impôts Directs)</p>
                <ul class="list-disc list-inside space-y-1 mt-2">
                    <li><strong>NIF</strong> (Numéro d'Identification Fiscale) — 15 chiffres — émetteur ET acheteur professionnel.</li>
                    <li><strong>RC</strong> (Registre du Commerce) — format : <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">{wilaya}/{code}/B/{année}/{numéro}</code></li>
                    <li><strong>AI</strong> (Article d'Imposition).</li>
                    <li>Taux et montant TVA + TAP.</li>
                </ul>
                <p class="mt-2 text-xs opacity-75">
                    TVA DZ : <strong>19%</strong> (normal) / <strong>9%</strong> (réduit — médicaments, équipements médicaux, tourisme).
                    TAP : <strong>2%</strong> sur CA HT.
                    IBS : <strong>23%</strong> (production) / <strong>26%</strong> (autres).
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
