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
    router.get(route('tax-config.senegal'), {
        month: selectedMonth.value,
        year: selectedYear.value,
    }, { preserveState: false });
}

const d = computed(() => props.declaration ?? {});
const fmt = (n) => new Intl.NumberFormat('fr-SN', { minimumFractionDigits: 0 }).format(Number(n ?? 0));
</script>

<template>
    <Head title="Déclaration TVA Sénégal" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                Déclaration TVA Sénégal — Formulaire DGID
            </h2>
        </template>

        <div class="py-8 max-w-3xl mx-auto px-4 space-y-6">

            <!-- Sélecteur période -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-4">Période de déclaration</h3>
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
                        :href="route('tax-config.api.senegal') + '?month=' + selectedMonth + '&year=' + selectedYear"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700"
                        target="_blank"
                    >
                        Export JSON (brouillon)
                    </a>
                </div>
            </div>

            <!-- Tableau récapitulatif DGID -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-5">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Récapitulatif Déclaration TVA — Direction Générale des Impôts et Domaines (DGID)
                </h3>

                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-4 font-medium text-gray-600 dark:text-gray-400">Rubrique</th>
                            <th class="py-2 text-right font-medium text-gray-600 dark:text-gray-400">Montant (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 pr-4 text-gray-800 dark:text-gray-200">Chiffre d'affaires TTC</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt(d.ca_ttc) }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 pr-4 text-gray-800 dark:text-gray-200">TVA collectée (18%)</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt(d.tva_collectee) }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 pr-4 text-gray-800 dark:text-gray-200">TVA déductible sur achats</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt(d.tva_deductible) }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 pr-4 text-gray-800 dark:text-gray-200">Retenue à la source (prestataires non-résidents 20%)</td>
                            <td class="py-2 text-right text-gray-700 dark:text-gray-300">{{ fmt(d.ras_prestataires) }}</td>
                        </tr>
                        <tr class="font-semibold text-gray-900 dark:text-gray-100 border-t border-gray-200 dark:border-gray-700">
                            <td class="pt-3 pr-4">TVA nette à payer</td>
                            <td class="pt-3 text-right text-indigo-600 dark:text-indigo-400">{{ fmt(d.tva_nette) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Notes légales -->
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-4 text-sm text-amber-800 dark:text-amber-200 space-y-1">
                <p class="font-medium">Mentions obligatoires sur factures — Sénégal (CGI art. 360)</p>
                <ul class="list-disc list-inside space-y-1 mt-2">
                    <li><strong>NINEA</strong> (Numéro d'Identification National des Entreprises et Associations) — 9 chiffres + lettre — obligatoire sur chaque facture.</li>
                    <li><strong>RCCM</strong> — format : <code class="bg-amber-100 dark:bg-amber-800 px-1 rounded">SN-DKR-{année}-B-{numéro}</code></li>
                    <li>Mention "TVA non applicable" ou le taux appliqué.</li>
                    <li>Numéro séquentiel (obligation DGI — numérotation continue).</li>
                </ul>
                <p class="mt-2 text-xs opacity-75">
                    Taux normal TVA SN : <strong>18%</strong> — Taux zéro : exportations, médicaments, produits alimentaires de base.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
