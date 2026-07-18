import { makeRequest } from './utils/request.js';
import { DocumentResource } from './resources/DocumentResource.js';
import { CustomerResource } from './resources/CustomerResource.js';
import { ProductResource } from './resources/ProductResource.js';
import { InvoiceResource } from './resources/InvoiceResource.js';

/**
 * Client principal du SDK JavaScript FactPro.
 * Compatible Node.js 18+ et navigateurs modernes (ES2020+).
 *
 * @example
 * import { FactProClient } from '@ibigsoft/factpro-sdk';
 *
 * const client = new FactProClient({ apiKey: 'votre-cle-api' });
 *
 * const invoices = await client.invoices.list({ status: 'pending' });
 * console.log(invoices.data);
 */
export class FactProClient {
    /**
     * @param {Object} config
     * @param {string} config.apiKey                                            - Clé API Sanctum
     * @param {string} [config.baseUrl='https://app.factpro.ibigsoft.com']     - URL de base
     * @param {number} [config.timeout=10000]                                   - Timeout en ms
     * @param {number} [config.retries=3]                                       - Nombre de retries sur erreur réseau/429/503
     */
    constructor({
        apiKey,
        baseUrl = 'https://app.factpro.ibigsoft.com',
        timeout = 10000,
        retries = 3,
    }) {
        if (!apiKey) {
            throw new Error('FactProClient : apiKey est requis.');
        }
        this.apiKey  = apiKey;
        this.baseUrl = baseUrl.replace(/\/$/, '');
        this.timeout = timeout;
        this.retries = retries;
        this._initResources();
    }

    /** @private */
    _initResources() {
        /** @type {DocumentResource} */
        this.documents = new DocumentResource(this);
        /** @type {CustomerResource} */
        this.customers = new CustomerResource(this);
        /** @type {ProductResource} */
        this.products  = new ProductResource(this);
        /** @type {InvoiceResource} */
        this.invoices  = new InvoiceResource(this);
    }

    /**
     * Effectue une requête HTTP vers l'API FactPro.
     * Utilisé en interne par les ressources.
     *
     * @param {string} method         - GET | POST | PUT | PATCH | DELETE
     * @param {string} path           - Chemin API (ex: '/v1/documents')
     * @param {Object|null} [data]    - Corps JSON (ignoré pour GET/DELETE)
     * @returns {Promise<any>}
     * @throws {AuthError} Sur HTTP 401
     * @throws {ValidationError} Sur HTTP 422
     * @throws {FactProError} Sur toute autre erreur
     */
    async request(method, path, data = null) {
        const url = `${this.baseUrl}/api${path}`;
        return makeRequest({
            method,
            url,
            apiKey:  this.apiKey,
            timeout: this.timeout,
            retries: this.retries,
            data,
        });
    }

    /**
     * Teste la connexion et retourne les informations du compte.
     *
     * @returns {Promise<Object>} Informations de l'utilisateur et de l'entreprise
     */
    async me() {
        return this.request('GET', '/v1/me');
    }
}
