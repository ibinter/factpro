<script setup>
import { computed } from 'vue';
import { useOffline } from '@/Composables/useOffline.js';

const { isOffline, pendingCount, syncing, lastSync, syncNow } = useOffline();

/** Afficher la bannière si hors-ligne OU si des docs attendent la sync */
const visible = computed(() => isOffline.value || pendingCount.value > 0);

const formattedLastSync = computed(() => {
    if (!lastSync.value) return null;
    return new Intl.DateTimeFormat('fr-FR', {
        hour: '2-digit',
        minute: '2-digit',
        day: '2-digit',
        month: '2-digit',
    }).format(lastSync.value);
});
</script>

<template>
    <!-- Bannière hors-ligne / synchronisation -->
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="-translate-y-full opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="-translate-y-full opacity-0"
    >
        <div v-if="visible" class="relative z-50">
            <!-- Bannière principale : mode hors-ligne -->
            <div
                v-if="isOffline"
                class="flex items-center justify-between bg-orange-500 px-4 py-2 text-sm text-white shadow-md"
                role="alert"
                aria-live="polite"
            >
                <div class="flex items-center gap-2">
                    <!-- Icône hors-ligne -->
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 5.636a9 9 0 010 12.728M15.536 8.464a5 5 0 010 7.072M12 12h.01M3 3l18 18" />
                    </svg>
                    <span class="font-semibold">Mode hors-ligne</span>
                    <span class="hidden sm:inline">— Vous travaillez sans connexion. Les documents seront synchronisés automatiquement.</span>
                </div>

                <div v-if="pendingCount > 0" class="flex items-center gap-2">
                    <span class="rounded-full bg-orange-700 px-2 py-0.5 text-xs font-bold">
                        {{ pendingCount }} en attente
                    </span>
                </div>
            </div>

            <!-- Bannière synchronisation en cours -->
            <div
                v-else-if="syncing"
                class="flex items-center justify-center gap-2 bg-blue-600 px-4 py-2 text-sm text-white shadow-md"
                role="status"
                aria-live="polite"
            >
                <!-- Spinner -->
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span class="font-semibold">Synchronisation en cours…</span>
            </div>

            <!-- Bannière documents en attente (connecté mais pas encore synchro) -->
            <div
                v-else-if="pendingCount > 0"
                class="flex items-center justify-between bg-amber-500 px-4 py-2 text-sm text-white shadow-md"
                role="alert"
                aria-live="polite"
            >
                <div class="flex items-center gap-2">
                    <!-- Icône attente -->
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>
                        <span class="font-semibold">{{ pendingCount }} document{{ pendingCount > 1 ? 's' : '' }}</span>
                        en attente de synchronisation
                        <span v-if="formattedLastSync" class="hidden sm:inline text-amber-100">
                            — Dernière sync : {{ formattedLastSync }}
                        </span>
                    </span>
                </div>

                <button
                    type="button"
                    class="rounded bg-amber-700 px-3 py-1 text-xs font-semibold text-white transition hover:bg-amber-800 focus:outline-none focus:ring-2 focus:ring-amber-300"
                    :disabled="syncing"
                    @click="syncNow"
                >
                    Synchroniser maintenant
                </button>
            </div>
        </div>
    </Transition>
</template>
