/**
 * Ressource Produits / Services.
 */
export class ProductResource {
    /** @param {import('../FactProClient.js').FactProClient} client */
    constructor(client) {
        this.client = client;
    }

    /**
     * Liste les produits.
     *
     * @param {Object} [params]
     * @param {number} [params.page=1]
     * @param {string} [params.search]
     * @param {string} [params.category]
     * @returns {Promise<Object>}
     */
    async list({ page = 1, search, category } = {}) {
        const query = new URLSearchParams({ page });
        if (search)   query.set('search', search);
        if (category) query.set('category', category);

        return this.client.request('GET', `/v1/products?${query}`);
    }

    /**
     * Récupère un produit par son ID.
     *
     * @param {number} id
     * @returns {Promise<Object>}
     */
    async get(id) {
        return this.client.request('GET', `/v1/products/${id}`);
    }

    /**
     * Crée un nouveau produit ou service.
     *
     * @param {Object} data
     * @param {string} data.name
     * @param {number} data.unit_price
     * @param {string} [data.description]
     * @param {string} [data.unit]       - Unité (ex: 'pièce', 'heure', 'kg')
     * @param {number} [data.tax_rate]   - Taux TVA en % (ex: 18)
     * @returns {Promise<Object>}
     */
    async create(data) {
        return this.client.request('POST', '/v1/products', data);
    }

    /**
     * Met à jour un produit.
     *
     * @param {number} id
     * @param {Object} data
     * @returns {Promise<Object>}
     */
    async update(id, data) {
        return this.client.request('PUT', `/v1/products/${id}`, data);
    }

    /**
     * Ajuste le stock d'un produit.
     *
     * @param {number} id
     * @param {Object} options
     * @param {number} options.quantity  - Quantité à ajouter (positif) ou retirer (négatif)
     * @param {string} [options.reason]  - Motif de l'ajustement
     * @returns {Promise<Object>}
     */
    async adjustStock(id, { quantity, reason }) {
        return this.client.request('POST', `/v1/products/${id}/stock`, { quantity, reason });
    }
}
