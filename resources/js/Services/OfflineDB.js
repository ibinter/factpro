/**
 * OfflineDB — Wrapper IndexedDB pour FactPro hors-ligne (Phase 12).
 *
 * Stores :
 *   - customers        : keyPath 'id', index 'company_id'
 *   - products         : keyPath 'id', index 'company_id'
 *   - pendingDocuments : keyPath 'localId'
 *   - syncLog          : keyPath 'id', autoIncrement
 */

const DB_NAME = 'factpro-offline';
const DB_VERSION = 1;

export class OfflineDB {
    constructor() {
        this.db = null;
    }

    async open() {
        if (this.db) return this.db;

        return new Promise((resolve, reject) => {
            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Store clients
                if (!db.objectStoreNames.contains('customers')) {
                    const customersStore = db.createObjectStore('customers', { keyPath: 'id' });
                    customersStore.createIndex('company_id', 'company_id', { unique: false });
                }

                // Store produits
                if (!db.objectStoreNames.contains('products')) {
                    const productsStore = db.createObjectStore('products', { keyPath: 'id' });
                    productsStore.createIndex('company_id', 'company_id', { unique: false });
                }

                // File de documents en attente de synchronisation
                if (!db.objectStoreNames.contains('pendingDocuments')) {
                    db.createObjectStore('pendingDocuments', { keyPath: 'localId' });
                }

                // Journal de synchronisation
                if (!db.objectStoreNames.contains('syncLog')) {
                    db.createObjectStore('syncLog', { keyPath: 'id', autoIncrement: true });
                }

                // Métadonnées (lastSync, etc.)
                if (!db.objectStoreNames.contains('meta')) {
                    db.createObjectStore('meta', { keyPath: 'key' });
                }
            };

            request.onsuccess = (event) => {
                this.db = event.target.result;
                resolve(this.db);
            };

            request.onerror = (event) => {
                reject(event.target.error);
            };
        });
    }

    /** Exécute une transaction et retourne le résultat de la requête. */
    _run(storeName, mode, callback) {
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction(storeName, mode);
            const store = tx.objectStore(storeName);
            const request = callback(store);
            if (request && request.onsuccess !== undefined) {
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error);
            } else {
                tx.oncomplete = () => resolve();
                tx.onerror = () => reject(tx.error);
            }
        });
    }

    /** Stocke un tableau de clients (remplace les existants). */
    async cacheCustomers(customers) {
        await this.open();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('customers', 'readwrite');
            const store = tx.objectStore('customers');
            store.clear();
            for (const customer of customers) {
                store.put(customer);
            }
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }

    /** Lit les clients depuis le cache local. */
    async getCustomers() {
        await this.open();
        return this._run('customers', 'readonly', (store) => store.getAll());
    }

    /** Stocke un tableau de produits (remplace les existants). */
    async cacheProducts(products) {
        await this.open();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('products', 'readwrite');
            const store = tx.objectStore('products');
            store.clear();
            for (const product of products) {
                store.put(product);
            }
            tx.oncomplete = () => resolve();
            tx.onerror = () => reject(tx.error);
        });
    }

    /** Lit les produits depuis le cache local. */
    async getProducts() {
        await this.open();
        return this._run('products', 'readonly', (store) => store.getAll());
    }

    /** Ajoute un document à la file de synchronisation. */
    async addPendingDocument(docData) {
        await this.open();
        return this._run('pendingDocuments', 'readwrite', (store) => store.put(docData));
    }

    /** Lit tous les documents en attente. */
    async getPendingDocuments() {
        await this.open();
        return this._run('pendingDocuments', 'readonly', (store) => store.getAll());
    }

    /** Supprime un document de la file après synchronisation réussie. */
    async removePendingDocument(localId) {
        await this.open();
        return this._run('pendingDocuments', 'readwrite', (store) => store.delete(localId));
    }

    /** Lit le timestamp de la dernière synchronisation. */
    async getLastSync() {
        await this.open();
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('meta', 'readonly');
            const store = tx.objectStore('meta');
            const request = store.get('lastSync');
            request.onsuccess = () => resolve(request.result ? new Date(request.result.value) : null);
            request.onerror = () => reject(request.error);
        });
    }

    /** Met à jour le timestamp de la dernière synchronisation. */
    async setLastSync(timestamp) {
        await this.open();
        return this._run('meta', 'readwrite', (store) =>
            store.put({ key: 'lastSync', value: timestamp })
        );
    }

    /** Ajoute une entrée dans le journal de synchronisation. */
    async addSyncLog(entry) {
        await this.open();
        return this._run('syncLog', 'readwrite', (store) =>
            store.add({ ...entry, timestamp: Date.now() })
        );
    }
}

export default new OfflineDB();
