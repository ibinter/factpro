import { FactProError } from './FactProError.js';

/**
 * Erreur d'authentification (HTTP 401).
 * Indique un token invalide ou expiré.
 */
export class AuthError extends FactProError {
    constructor(message = 'Authentification échouée. Vérifiez votre clé API.') {
        super(message, 401, 'AUTH_ERROR');
        this.name = 'AuthError';
    }
}
