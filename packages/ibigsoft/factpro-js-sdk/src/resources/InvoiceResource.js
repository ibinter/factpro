/**
 * Ressource Factures — raccourci spécialisé sur les documents de type 'invoice'.
 */
export class InvoiceResource {
    /** @param {import('../FactProClient.js').FactProClient} client */
    constructor(client) {
        this.client = client;
    }

    /**
     * Liste les factures.
     *
     * @param {Object} [params]
     * @param {number}  [params.page=1]
     * @param {string}  [params.status]  - 'draft' | 'final' | 'sent' | 'paid' | 'cancelled'
     * @param {boolean} [params.overdue] - true pour filtrer les factures en retard
     * @returns {Promise<Object>}
     */
    async list({ page = 1, status, overdue } = {}) {
        const query = new URLSearchParams({ page, type: 'invoice' });
        if (status)  query.set('status', status);
        if (overdue) query.set('overdue', '1');

        return this.client.request('GET', `/v1/documents?${query}`);
    }

    /**
     * Récupère une facture par son UUID.
     *
     * @param {string} uuid
     * @returns {Promise<Object>}
     */
    async get(uuid) {
        return this.client.request('GET', `/v1/documents/${uuid}`);
    }

    /**
     * Enregistre un paiement sur une facture.
     *
     * @param {string} uuid
     * @param {Object} options
     * @param {number} options.amount   - Montant payé
     * @param {string} options.method   - Mode de paiement ('cash' | 'bank_transfer' | 'mobile_money' | …)
     * @param {string} [options.date]   - Date du paiement (YYYY-MM-DD, défaut: aujourd'hui)
     * @returns {Promise<Object>}
     */
    async registerPayment(uuid, { amount, method, date }) {
        return this.client.request('POST', `/v1/documents/${uuid}/payments`, { amount, method, date });
    }

    /**
     * Envoie une relance par email pour une facture impayée.
     *
     * @param {string} uuid
     * @returns {Promise<Object>}
     */
    async sendReminder(uuid) {
        return this.client.request('POST', `/v1/documents/${uuid}/reminder`);
    }

    /**
     * Marque manuellement une facture comme payée intégralement.
     *
     * @param {string} uuid
     * @returns {Promise<Object>}
     */
    async markAsPaid(uuid) {
        return this.client.request('POST', `/v1/documents/${uuid}/mark-paid`);
    }
}
