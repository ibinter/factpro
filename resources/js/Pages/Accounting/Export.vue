<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    from: { type: String, default: '' },
    to: { type: String, default: '' },
    formats: { type: Array, default: () => [] },
});

const selectedFormat = ref('sage');
const from = ref(props.from);
const to = ref(props.to);
const previewLines = ref([]);
const previewLoading = ref(false);
const previewError = ref('');

/* ---- Aide contextuelle par format ---- */
const formatHelp = {
    sage: {
        title: 'Sage 100 — Import Paramétrable',
        steps: [
            'Dans Sage 100, allez dans Comptabilité > Traitements > Import des écritures.',
            'Choisissez le format « Import paramétrable » (CSV).',
            'Sélectionnez le fichier .txt téléchargé.',
            'Mappez les colonnes selon l'ordre du fichier (JournalCode, EcritureDate, CompteNum…).',
            'Lancez l'import et vérifiez les journaux.',
        ],
    },
    quickbooks: {
        title: 'QuickBooks — Intuit Interchange Format (IIF)',
        steps: [
            'Dans QuickBooks, allez dans Fichier > Utilitaires > Importer > Fichiers IIF.',
            'Sélectionnez le fichier .iif téléchargé.',
            'Confirmez l'import et vérifiez les transactions dans le journal.',
        ],
    },
    pennylane: {
        title: 'Pennylane — Import JSON',
        steps: [
            'Connectez-vous à votre espace Pennylane.',
            'Allez dans Comptabilité > Écritures > Importer.',
            'Sélectionnez le fichier .json téléchargé.',
            'Vérifiez les écritures importées et validez.',
        ],
    },
};

const currentHelp = computed(() => formatHelp[selectedFormat.value] ?? null);

const downloadUrl = computed(() => {
    return route('accounting.export.' + selectedFormat.value);
});

/* ---- Prévisualisation ---- */
async function previewExport() {
    previewLines.value = [];
    previewError.value = '';
    previewLoading.value = true;
    try {
        const res = await axios.post(route('accounting.export.preview'), {
            from: from.value,
            to: to.value,
            format: selectedFormat.value,
        });
        previewLines.value = res.data.lines ?? [];
    } catch (e) {
        previewError.value = e.response?.data?.message ?? 'Erreur lors de la prévisualisation.';
    } finally {
        previewLoading.value = false;
    }
}

/* ---- Téléchargement via formulaire POST ---- */
function downloadExport() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = downloadUrl.value;

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    form.appendChild(csrfInput);

    const fromInput = document.createElement('input');
    fromInput.type = 'hidden';
    fromInput.name = 'from';
    fromInput.value = from.value;
    form.appendChild(fromInput);

    const toInput = document.createElement('input');
    toInput.type = 'hidden';
    toInput.name = 'to';
    toInput.value = to.value;
    form.appendChild(toInput);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}
</script>

<template>
    <Head title="Export comptable" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Export comptable — Logiciels tiers</h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">

                <!-- Formulaire d'export -->
                <div class="rounded-lg bg-white p-6 shadow space-y-5">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Paramètres d'export</h3>

                    <!-- Format -->
                    <div>
                        <InputLabel value="Format d'export" class="mb-1" />
                        <div class="flex flex-wrap gap-3">
                            <label
                                v-for="f in formats"
                                :key="f.value"
                                class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition"
                                :class="selectedFormat === f.value
                                    ? 'border-brand-600 bg-brand-50 text-brand-700'
                                    : 'border-gray-300 text-gray-700 hover:border-brand-400'"
                            >
                                <input
                                    type="radio"
                                    :value="f.value"
                                    v-model="selectedFormat"
                                    class="accent-brand-600"
                                />
                                {{ f.label }}
                            </label>
                        </div>
                    </div>

                    <!-- Période -->
                    <div class="flex flex-wrap gap-4">
                        <div>
                            <InputLabel value="Du" class="text-xs" />
                            <TextInput v-model="from" type="date" class="mt-1 text-sm" />
                        </div>
                        <div>
                            <InputLabel value="Au" class="text-xs" />
                            <TextInput v-model="to" type="date" class="mt-1 text-sm" />
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-3 pt-1">
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-600 hover:text-brand-600 disabled:opacity-50"
                            :disabled="previewLoading"
                            @click="previewExport"
                        >
                            {{ previewLoading ? 'Chargement…' : 'Prévisualiser' }}
                        </button>
                        <PrimaryButton type="button" @click="downloadExport">
                            Télécharger
                        </PrimaryButton>
                    </div>

                    <!-- Note plan comptable -->
                    <p class="text-xs text-gray-400">
                        Plan comptable par défaut utilisé (411xxx clients, 701000 ventes, 445710 TVA…).
                        Contactez le support pour personnaliser.
                    </p>
                </div>

                <!-- Prévisualisation -->
                <div v-if="previewError" class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ previewError }}
                </div>

                <div v-if="previewLines.length" class="rounded-lg bg-white shadow overflow-hidden">
                    <div class="border-b px-4 py-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                            Aperçu — {{ previewLines.length }} premières ligne(s)
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <pre class="p-4 text-xs text-gray-800 bg-gray-50 leading-relaxed whitespace-pre-wrap break-all">{{ previewLines.join('\n') }}</pre>
                    </div>
                </div>

                <!-- Aide contextuelle -->
                <div v-if="currentHelp" class="rounded-lg bg-blue-50 border border-blue-200 p-5">
                    <h4 class="text-sm font-semibold text-blue-800 mb-3">{{ currentHelp.title }}</h4>
                    <ol class="list-decimal list-inside space-y-1.5">
                        <li
                            v-for="(step, i) in currentHelp.steps"
                            :key="i"
                            class="text-sm text-blue-700"
                        >
                            {{ step }}
                        </li>
                    </ol>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
