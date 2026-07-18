/**
 * Ressource Documents — devis, factures, avoirs, etc.
 */
export class DocumentResource {
    /** @param {import('../FactProClient.js').FactProClient} client */
    constructor(client) {
        this.client = client;
    }

    /**
     * Liste les documents avec filtres optionnels.
     *
     * @param {Object} [params]
     * @param {number} [params.page=1]
     * @param {string} [params.type]        - 'invoice' | 'quote' | 'credit_note' | 'delivery_note'
     * @param {string} [params.status]      - 'draft' | 'final' | 'sent' | 'paid' | 'cancelled'
     * @param {number} [params.customer_id]
     * @param {string} [params.search]
     * @returns {Promise<Object>}
     */
    async list({ page = 1, type, status, customer_id, search } = {}) {
        const query = new URLSearchParams({ page });
        if (type)        query.set('type', type);
        if (status)      query.set('status', status);
        if (customer_id) query.set('customer_id', customer_id);
        if (search)      query.set('search', search);

        return this.client.request('GET', `/v1/documents?${query}`);
    }

    /**
     * Récupère un document par son UUID.
     *
     * @param {string} uuid
     * @returns {Promise<Object>}
     */
    async get(uuid) {
        return this.client.request('GET', `/v1/documents/${uuid}`);
    }

    /**
     * Crée un nouveau document.
     *
     * @param {Object} data
     * @param {string} data.type          - 'invoice' | 'quote' | 'credit_note' | 'delivery_note'
     * @param {number} data.customer_id
     * @param {Array}  data.items
     * @param {string} [data.issue_date]  - Format YYYY-MM-DD
     * @param {string} [data.currency]    - Code ISO (ex: 'XOF', 'EUR')
     * @returns {Promise<Object>}
     */
    async create(data) {
        return this.client.request('POST', '/v1/documents', data);
    }

    /**
     * Met à jour un document (brouillon uniquement).
     *
     * @param {string} uuid
     * @param {Object} data
     * @returns {Promise<Object>}
     */
    async update(uuid, data) {
        return this.client.request('PUT', `/v1/documents/${uuid}`, data);
    }

    /**
     * Finalise un document (passage de draft → final).
     *
     * @param {string} uuid
     * @returns {Promise<Object>}
     */
    async finalize(uuid) {
        return this.client.request('POST', `/v1/documents/${uuid}/finalize`);
    }

    /**
     * Envoie un document par email.
     *
     * @param {string} uuid
     * @param {Object} [options]
     * @param {string} [options.email]   - Email destinataire (par défaut : email du client)
     * @param {string} [options.message] - Message personnalisé
     * @returns {Promise<Object>}
     */
    async send(uuid, { email, message } = {}) {
        const data = {};
        if (email)   data.email = email;
        if (message) data.message = message;
        return this.client.request('POST', `/v1/documents/${uuid}/send`, data);
    }

    /**
     * Télécharge le PDF d'un document.
     * Retourne un Uint8Array (compatible Node.js et navigateur).
     *
     * @param {string} uuid
     * @returns {Promise<Uint8Array>}
     *
     * @example <caption>Navigateur</caption>
     * const bytes = await client.documents.downloadPdf('uuid-...');
     * const blob = new Blob([bytes], { type: 'application/pdf' });
     * const url = URL.createObjectURL(blob);
     * window.open(url);
     *
     * @example <caption>Node.js</caption>
     * const bytes = await client.documents.downloadPdf('uuid-...');
     * fs.writeFileSync('facture.pdf', Buffer.from(bytes));
     */
    async downloadPdf(uuid) {
        return this.client.request('GET', `/v1/documents/${uuid}/pdf`);
    }
}
