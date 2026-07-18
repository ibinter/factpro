/**
 * Erreur de base du SDK FactPro.
 */
export class FactProError extends Error {
    /**
     * @param {string} message
     * @param {number} [status]
     * @param {string} [code]
     */
    constructor(message, status = null, code = null) {
        super(message);
        this.name = 'FactProError';
        this.status = status;
        this.code = code;
    }
}
