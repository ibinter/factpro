/**
 * Ressource Clients (customers).
 */
export class CustomerResource {
    /** @param {import('../FactProClient.js').FactProClient} client */
    constructor(client) {
        this.client = client;
    }

    /**
     * Liste les clients.
     *
     * @param {Object} [params]
     * @param {number} [params.page=1]
     * @param {string} [params.search]
     * @param {string} [params.type]   - 'individual' | 'company'
     * @returns {Promise<Object>}
     */
    async list({ page = 1, search, type } = {}) {
        const query = new URLSearchParams({ page });
        if (search) query.set('search', search);
        if (type)   query.set('type', type);

        return this.client.request('GET', `/v1/customers?${query}`);
    }

    /**
     * Récupère un client par son ID.
     *
     * @param {number} id
     * @returns {Promise<Object>}
     */
    async get(id) {
        return this.client.request('GET', `/v1/customers/${id}`);
    }

    /**
     * Crée un nouveau client.
     *
     * @param {Object} data
     * @param {string} data.name
     * @param {string} [data.email]
     * @param {string} [data.phone]
     * @param {string} [data.type]    - 'individual' | 'company'
     * @returns {Promise<Object>}
     */
    async create(data) {
        return this.client.request('POST', '/v1/customers', data);
    }

    /**
     * Met à jour un client.
     *
     * @param {number} id
     * @param {Object} data
     * @returns {Promise<Object>}
     */
    async update(id, data) {
        return this.client.request('PUT', `/v1/customers/${id}`, data);
    }

    /**
     * Liste les factures d'un client.
     *
     * @param {number} id
     * @returns {Promise<Object>}
     */
    async getInvoices(id) {
        return this.client.request('GET', `/v1/customers/${id}/invoices`);
    }

    /**
     * Retourne le solde en attente d'un client (total des factures non réglées).
     *
     * @param {number} id
     * @returns {Promise<Object>}
     */
    async getBalance(id) {
        return this.client.request('GET', `/v1/customers/${id}/balance`);
    }
}
