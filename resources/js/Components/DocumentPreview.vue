<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    formData: { type: Object, required: true },
});

// ── État du panel ─────────────────────────────────────────────────────────────
const open       = ref(false);
const loading    = ref(false);
const errorMsg   = ref('');
const pdfBlobUrl = ref('');

// Libérer l'URL objet précédente pour éviter les fuites mémoire
function revokePrev() {
    if (pdfBlobUrl.value) {
        URL.revokeObjectURL(pdfBlobUrl.value);
        pdfBlobUrl.value = '';
    }
}

// ── Appel API ─────────────────────────────────────────────────────────────────
let debounceTimer = null;

async function fetchPreview() {
    loading.value = true;
    errorMsg.value = '';
    revokePrev();
    try {
        // Récupérer le token CSRF depuis le cookie ou la meta
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ?? '';

        const res = await fetch('/documents/preview', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/pdf',
            },
            body: JSON.stringify(props.formData),
        });

        if (!res.ok) {
            const text = await res.text();
            throw new Error('Erreur serveur (' + res.status + ')');
        }

        const blob = await res.blob();
        pdfBlobUrl.value = URL.createObjectURL(blob);
    } catch (e) {
        errorMsg.value = e.message || 'Impossible de générer l\'aperçu.';
    } finally {
        loading.value = false;
    }
}

// Ouvrir le panel et charger l'aperçu immédiatement
function openPanel() {
    open.value = true;
    fetchPreview();
}

function closePanel() {
    open.value = false;
    revokePrev();
    clearTimeout(debounceTimer);
}

// Recharger automatiquement quand les données changent (debounce 2 s)
watch(
    () => props.formData,
    () => {
        if (!open.value) return;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchPreview, 2000);
    },
    { deep: true }
);
</script>

<template>
    <!-- Bouton flottant -->
    <button
        type="button"
        @click="openPanel"
        class="inline-flex items-center gap-2 rounded-xl border border-brand-300 bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-700 shadow-sm transition hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-brand-400"
        title="Aperçu PDF en temps réel"
    >
        <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        Aperçu PDF
    </button>

    <!-- Panel slide-over -->
    <Teleport to="body">
        <Transition name="slide-over">
            <div v-if="open" class="fixed inset-0 z-[9998] flex">

                <!-- Fond semi-transparent -->
                <div
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm"
                    @click="closePanel"
                ></div>

                <!-- Panel latéral droit -->
                <div class="relative ml-auto flex h-full w-full max-w-2xl flex-col bg-white shadow-2xl">

                    <!-- En-tête -->
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-brand-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Aperçu PDF</h2>
                                <p class="text-xs text-gray-400">Se rafraîchit automatiquement (2 s après modif.)</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Bouton rafraîchir manuel -->
                            <button
                                type="button"
                                @click="fetchPreview"
                                :disabled="loading"
                                class="rounded-lg border border-gray-200 bg-gray-50 p-1.5 text-gray-500 transition hover:bg-gray-100 disabled:opacity-50"
                                title="Rafraîchir maintenant"
                            >
                                <svg class="h-4 w-4" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                            </button>
                            <button
                                type="button"
                                @click="closePanel"
                                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                                title="Fermer"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Contenu -->
                    <div class="relative flex-1 overflow-hidden bg-gray-100">

                        <!-- Spinner de chargement -->
                        <div v-if="loading" class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-3 bg-gray-100">
                            <svg class="h-8 w-8 animate-spin text-brand-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <p class="text-sm font-medium text-gray-500">Génération de l'aperçu…</p>
                        </div>

                        <!-- Message d'erreur -->
                        <div v-else-if="errorMsg" class="absolute inset-0 z-10 flex flex-col items-center justify-center gap-4 p-8">
                            <svg class="h-12 w-12 text-red-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            <p class="text-center text-sm font-medium text-gray-700">{{ errorMsg }}</p>
                            <button type="button" @click="fetchPreview"
                                class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                                Réessayer
                            </button>
                        </div>

                        <!-- Iframe PDF -->
                        <iframe
                            v-if="pdfBlobUrl && !loading"
                            :src="pdfBlobUrl"
                            class="h-full w-full border-0"
                            title="Aperçu PDF"
                        ></iframe>
                    </div>

                    <!-- Pied de panel -->
                    <div class="border-t border-gray-100 bg-gray-50 px-5 py-3 text-center">
                        <p class="text-[11px] text-gray-400">
                            Aperçu non définitif — Numéro et QR code assignés à la sauvegarde.
                        </p>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.slide-over-enter-active,
.slide-over-leave-active {
    transition: opacity 0.2s ease;
}
.slide-over-enter-active .relative,
.slide-over-leave-active .relative {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-over-enter-from { opacity: 0; }
.slide-over-enter-from .relative { transform: translateX(100%); }
.slide-over-leave-to { opacity: 0; }
.slide-over-leave-to .relative { transform: translateX(100%); }
</style>
