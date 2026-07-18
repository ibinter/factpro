/**
 * useOffline — Composable Vue 3 pour la gestion du mode hors-ligne (Phase 12).
 *
 * Expose :
 *   - isOffline      : ref<boolean> — état réseau courant
 *   - pendingCount   : ref<number>  — documents en attente de sync
 *   - syncing        : ref<boolean> — synchronisation en cours
 *   - lastSync       : ref<Date|null> — date de la dernière sync réussie
 *   - syncNow()      : déclenche la synchronisation manuelle
 */

import { ref, onMounted, onUnmounted } from 'vue';
import offlineDB from '../Services/OfflineDB.js';
import syncQueue from '../Services/SyncQueue.js';

export function useOffline() {
    const isOffline = ref(!navigator.onLine);
    const pendingCount = ref(0);
    const syncing = ref(false);
    const lastSync = ref(null);

    async function updatePendingCount() {
        try {
            pendingCount.value = await syncQueue.count();
        } catch {
            pendingCount.value = 0;
        }
    }

    async function syncNow() {
        if (syncing.value) return;
        syncing.value = true;
        try {
            const results = await syncQueue.flush();
            await updatePendingCount();
            await offlineDB.setLastSync(Date.now());
            lastSync.value = new Date();
            return results;
        } finally {
            syncing.value = false;
        }
    }

    function handleOnline() {
        isOffline.value = false;
        syncNow();
    }

    function handleOffline() {
        isOffline.value = true;
    }

    // Écouter les messages du Service Worker (Background Sync)
    function handleServiceWorkerMessage(event) {
        if (event.data?.type === 'BACKGROUND_SYNC' && event.data?.tag === 'factpro-sync') {
            syncNow();
        }
    }

    onMounted(async () => {
        try {
            await offlineDB.open();
            await updatePendingCount();
            lastSync.value = await offlineDB.getLastSync();
        } catch (e) {
            console.warn('[useOffline] IndexedDB init failed:', e);
        }

        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', handleServiceWorkerMessage);
        }
    });

    onUnmounted(() => {
        window.removeEventListener('online', handleOnline);
        window.removeEventListener('offline', handleOffline);

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.removeEventListener('message', handleServiceWorkerMessage);
        }
    });

    return { isOffline, pendingCount, syncing, lastSync, syncNow };
}
