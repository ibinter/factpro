import { FactProError } from './FactProError.js';

/**
 * Erreur de validation (HTTP 422).
 * La propriété `errors` contient le détail champ par champ.
 *
 * @example
 * try {
 *   await client.documents.create({});
 * } catch (e) {
 *   if (e instanceof ValidationError) {
 *     console.log(e.errors); // { customer_id: ['The customer id field is required.'] }
 *   }
 * }
 */
export class ValidationError extends FactProError {
    /**
     * @param {string} message
     * @param {Object.<string, string[]>} errors - Erreurs par champ
     */
    constructor(message = 'Données invalides.', errors = {}) {
        super(message, 422, 'VALIDATION_ERROR');
        this.name = 'ValidationError';
        this.errors = errors;
    }
}
