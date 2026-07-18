<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    taxConfig: { type: Object, default: null },
    regimes: { type: Object, default: () => ({}) },
    vatSummary: { type: Object, default: null },
});

/* ---- État local du formulaire ---- */
const isEditing = ref(!props.taxConfig);
const saving = ref(false);
const flash = ref('');

const regimeKeys = computed(() => Object.keys(props.regimes));

const form = ref({
    tax_regime: props.taxConfig?.tax_regime ?? 'ohada_ci',
    country: props.taxConfig?.country ?? 'CI',
    tva_rates: props.taxConfig?.tva_rates ?? [],
    has_tps: props.taxConfig?.has_tps ?? false,
    tps_rate: props.taxConfig?.tps_rate ?? 1.0,
    has_oca: props.taxConfig?.has_oca ?? false,
    oca_rate: props.taxConfig?.oca_rate ?? 0.5,
    has_timbre: props.taxConfig?.has_timbre ?? false,
    timbre_amount: props.taxConfig?.timbre_amount ?? 0,
    declaration_frequency: props.taxConfig?.declaration_frequency ?? 'monthly',
});

const regimeLabel = computed(() => props.regimes[form.value.tax_regime]?.name ?? form.value.tax_regime);

function applyRegime(regime) {
    const def = props.regimes[regime];
    if (!def) return;
    form.value.tax_regime = regime;
    form.value.country = def.country ?? '';
    form.value.tva_rates = (def.tva ?? []).map(r => ({
        rate: r,
        label: r === 0 ? 'Exonéré' : `TVA ${r}%`,
    }));
    form.value.has_tps = def.tps ?? false;
    form.value.tps_rate = def.tps_rate ?? 1.0;
    form.value.has_oca = def.oca ?? false;
    form.value.oca_rate = def.oca_rate ?? 0.5;
}

function addRate() {
    form.value.tva_rates.push({ rate: 0, label: 'Exonéré' });
}
function removeRate(i) {
    form.value.tva_rates.splice(i, 1);
}

function save() {
    saving.value = true;
    const method = props.taxConfig ? 'put' : 'post';
    const url = props.taxConfig
        ? route('tax-config.update', props.taxConfig.id)
        : route('tax-config.store');

    router[method](url, form.value, {
        onSuccess: () => {
            isEditing.value = false;
            flash.value = 'Configuration fiscale enregistrée.';
            saving.value = false;
        },
        onError: () => { saving.value = false; },
    });
}

const exportUrl = computed(() => route('tax-config.export'));

const totalTaxDue = computed(() => props.vatSummary?.total_tax_due ?? 0);
const tvaRows = computed(() => props.vatSummary?.tva_collected ?? []);

const fmt = (n) => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2 }).format(Number(n ?? 0));
</script>

<template>
    <Head title="Fiscalité" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                    Fiscalité multi-pays
                </h2>
                <a
                    :href="exportUrl"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700"
                >
                    Exporter CSV
                </a>
            </div>
        </template>

        <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">

            <!-- Flash message -->
            <div v-if="flash" class="p-3 bg-green-50 border border-green-300 text-green-800 rounded text-sm">
                {{ flash }}
            </div>

            <!-- === Section Régime fiscal === -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Régime fiscal</h3>
                    <button
                        v-if="!isEditing && taxConfig"
                        @click="isEditing = true"
                        class="text-sm text-indigo-600 hover:text-indigo-800"
                    >
                        Modifier
                    </button>
                </div>

                <!-- Lecture seule -->
                <div v-if="!isEditing && taxConfig" class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                    <p><span class="font-medium">Régime :</span> {{ regimeLabel }}</p>
                    <p><span class="font-medium">Pays :</span> {{ taxConfig.country }}</p>
                    <p><span class="font-medium">Taux TVA :</span>
                        {{ (taxConfig.tva_rates ?? []).map(r => r.label).join(', ') }}
                    </p>
                    <p v-if="taxConfig.has_tps"><span class="font-medium">TPS :</span> {{ taxConfig.tps_rate }}%</p>
                    <p v-if="taxConfig.has_oca"><span class="font-medium">OCA :</span> {{ taxConfig.oca_rate }}%</p>
                    <p v-if="taxConfig.has_timbre"><span class="font-medium">Timbre :</span> {{ taxConfig.timbre_amount }}</p>
                    <p><span class="font-medium">Déclaration :</span>
                        {{ taxConfig.declaration_frequency === 'monthly' ? 'Mensuelle' : 'Trimestrielle' }}
                    </p>
                </div>

                <!-- Formulaire d'édition -->
                <form v-else @submit.prevent="save" class="space-y-5">
                    <!-- Sélecteur de régime -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Régime fiscal</label>
                        <select
                            v-model="form.tax_regime"
                            @change="applyRegime(form.tax_regime)"
                            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm"
                        >
                            <option v-for="key in regimeKeys" :key="key" :value="key">
                                {{ regimes[key].name }}
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">{{ regimes[form.tax_regime]?.name }}</p>
                    </div>

                    <!-- Pays -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Code pays (2 lettres)</label>
                        <input
                            v-model="form.country"
                            maxlength="2"
                            class="w-24 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm uppercase"
                        />
                    </div>

                    <!-- Taux TVA -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Taux TVA disponibles (%)</label>
                        <div class="space-y-2">
                            <div v-for="(r, i) in form.tva_rates" :key="i" class="flex items-center gap-2">
                                <input
                                    v-model.number="r.rate"
                                    type="number" min="0" max="100" step="0.01"
                                    class="w-24 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm"
                                    placeholder="Taux"
                                />
                                <input
                                    v-model="r.label"
                                    class="flex-1 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm"
                                    placeholder="Libellé"
                                />
                                <button type="button" @click="removeRate(i)" class="text-red-500 hover:text-red-700 text-sm">✕</button>
                            </div>
                        </div>
                        <button type="button" @click="addRate" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">
                            + Ajouter un taux
                        </button>
                    </div>

                    <!-- TPS -->
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" v-model="form.has_tps" class="rounded border-gray-300" />
                            TPS activée
                        </label>
                        <div v-if="form.has_tps" class="flex items-center gap-1">
                            <input
                                v-model.number="form.tps_rate"
                                type="number" min="0" max="100" step="0.01"
                                class="w-20 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm"
                            />
                            <span class="text-sm text-gray-500">%</span>
                        </div>
                    </div>

                    <!-- OCA -->
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" v-model="form.has_oca" class="rounded border-gray-300" />
                            OCA activée
                        </label>
                        <div v-if="form.has_oca" class="flex items-center gap-1">
                            <input
                                v-model.number="form.oca_rate"
                                type="number" min="0" max="100" step="0.01"
                                class="w-20 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm"
                            />
                            <span class="text-sm text-gray-500">%</span>
                        </div>
                    </div>

                    <!-- Timbre -->
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" v-model="form.has_timbre" class="rounded border-gray-300" />
                            Timbre fiscal
                        </label>
                        <div v-if="form.has_timbre" class="flex items-center gap-1">
                            <input
                                v-model.number="form.timbre_amount"
                                type="number" min="0" step="0.01"
                                class="w-24 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm"
                            />
                            <span class="text-sm text-gray-500">montant unitaire</span>
                        </div>
                    </div>

                    <!-- Fréquence -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fréquence de déclaration</label>
                        <select
                            v-model="form.declaration_frequency"
                            class="border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm text-sm"
                        >
                            <option value="monthly">Mensuelle</option>
                            <option value="quarterly">Trimestrielle</option>
                        </select>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button
                            type="submit"
                            :disabled="saving"
                            class="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 disabled:opacity-60"
                        >
                            {{ saving ? 'Enregistrement…' : 'Enregistrer' }}
                        </button>
                        <button
                            v-if="taxConfig"
                            type="button"
                            @click="isEditing = false"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
                        >
                            Annuler
                        </button>
                    </div>
                </form>
            </div>

            <!-- === Section Déclaration courante === -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Déclaration courante (mois en cours)</h3>

                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-4 font-medium text-gray-600 dark:text-gray-400">Taxe</th>
                            <th class="py-2 pr-4 font-medium text-gray-600 dark:text-gray-400 text-right">Base HT</th>
                            <th class="py-2 font-medium text-gray-600 dark:text-gray-400 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in tvaRows" :key="row.rate" class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-1.5 pr-4 text-gray-800 dark:text-gray-200">TVA {{ row.rate }}%</td>
                            <td class="py-1.5 pr-4 text-right text-gray-700 dark:text-gray-300">{{ fmt(row.base) }}</td>
                            <td class="py-1.5 text-right text-gray-700 dark:text-gray-300">{{ fmt(row.tva) }}</td>
                        </tr>
                        <tr v-if="vatSummary?.tps_collected" class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-1.5 pr-4 text-gray-800 dark:text-gray-200">TPS</td>
                            <td class="py-1.5 pr-4"></td>
                            <td class="py-1.5 text-right text-gray-700 dark:text-gray-300">{{ fmt(vatSummary.tps_collected) }}</td>
                        </tr>
                        <tr v-if="vatSummary?.oca_collected" class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-1.5 pr-4 text-gray-800 dark:text-gray-200">OCA</td>
                            <td class="py-1.5 pr-4"></td>
                            <td class="py-1.5 text-right text-gray-700 dark:text-gray-300">{{ fmt(vatSummary.oca_collected) }}</td>
                        </tr>
                        <tr v-if="vatSummary?.timbre_total" class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-1.5 pr-4 text-gray-800 dark:text-gray-200">Timbre fiscal</td>
                            <td class="py-1.5 pr-4"></td>
                            <td class="py-1.5 text-right text-gray-700 dark:text-gray-300">{{ fmt(vatSummary.timbre_total) }}</td>
                        </tr>
                        <tr class="font-semibold text-gray-900 dark:text-gray-100">
                            <td class="pt-3 pr-4">Total taxes dues</td>
                            <td class="pt-3 pr-4"></td>
                            <td class="pt-3 text-right">{{ fmt(totalTaxDue) }}</td>
                        </tr>
                    </tbody>
                </table>

                <p v-if="!vatSummary || vatSummary.documents_count === 0" class="mt-4 text-sm text-gray-500">
                    Aucun document finalisé ce mois.
                </p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
