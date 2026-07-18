/**
 * SyncQueue — File de synchronisation hors-ligne pour FactPro (Phase 12).
 *
 * Responsabilités :
 *   - Mettre en file les documents créés hors-ligne (enqueue)
 *   - Synchroniser avec le serveur au retour du réseau (flush)
 *   - Enregistrer un Background Sync si le navigateur le supporte
 */

import offlineDB from './OfflineDB.js';
import axios from 'axios';

export class SyncQueue {
    /**
     * Ajoute un document à la file (appelé quand hors-ligne ou en erreur réseau).
     *
     * @param {Object} documentData  Données du document (type, customer_id, lines, …)
     * @returns {string} localId UUID identifiant le document dans la file
     */
    async enqueue(documentData) {
        const localId = crypto.randomUUID();
        await offlineDB.addPendingDocument({
            localId,
            ...documentData,
            queuedAt: Date.now(),
        });

        // Enregistrer un Background Sync si le navigateur le supporte
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            try {
                const sw = await navigator.serviceWorker.ready;
                await sw.sync.register('factpro-sync');
            } catch (e) {
                // Background Sync non supporté ou bloqué — pas critique
                console.warn('[SyncQueue] Background Sync registration failed:', e);
            }
        }

        await offlineDB.addSyncLog({
            action: 'enqueue',
            localId,
            type: documentData.type ?? 'unknown',
        });

        return localId;
    }

    /**
     * Synchronise tous les documents en attente avec le serveur.
     * Appelé automatiquement au retour du réseau.
     *
     * @returns {Array} Tableau de résultats {localId, status, serverId?, error?}
     */
    async flush() {
        const pending = await offlineDB.getPendingDocuments();
        const results = [];

        for (const doc of pending) {
            try {
                const response = await axios.post('/offline-sync/document', doc);
                await offlineDB.removePendingDocument(doc.localId);
                await offlineDB.addSyncLog({
                    action: 'synced',
                    localId: doc.localId,
                    serverId: response.data.id ?? response.data.serverId ?? null,
                });
                results.push({
                    localId: doc.localId,
                    status: 'synced',
                    serverId: response.data.id ?? response.data.serverId ?? null,
                });
            } catch (error) {
                await offlineDB.addSyncLog({
                    action: 'failed',
                    localId: doc.localId,
                    error: error.message,
                });
                results.push({
                    localId: doc.localId,
                    status: 'failed',
                    error: error.message,
                });
            }
        }

        return results;
    }

    /**
     * Retourne le nombre de documents en attente de synchronisation.
     *
     * @returns {number}
     */
    async count() {
        const pending = await offlineDB.getPendingDocuments();
        return pending.length;
    }
}

export default new SyncQueue();
