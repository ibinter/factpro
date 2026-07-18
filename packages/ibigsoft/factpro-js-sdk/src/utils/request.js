import { AuthError } from '../errors/AuthError.js';
import { ValidationError } from '../errors/ValidationError.js';
import { FactProError } from '../errors/FactProError.js';

/**
 * Délai exponentiel entre les retries.
 * @param {number} attempt - Numéro du retry (0-based)
 */
function wait(attempt) {
    return new Promise(resolve => setTimeout(resolve, Math.pow(2, attempt) * 1000));
}

/**
 * Effectue une requête HTTP vers l'API FactPro avec retry automatique,
 * gestion du timeout et parsing des erreurs.
 *
 * @param {Object} options
 * @param {string} options.method      - Méthode HTTP (GET, POST, PUT, DELETE, PATCH)
 * @param {string} options.url         - URL complète
 * @param {string} options.apiKey      - Clé API Bearer
 * @param {number} options.timeout     - Timeout en ms
 * @param {number} options.retries     - Nombre de retries
 * @param {Object|null} [options.data] - Corps JSON à envoyer
 * @returns {Promise<any>}
 */
export async function makeRequest({ method, url, apiKey, timeout, retries, data = null }) {
    const headers = {
        'Authorization': `Bearer ${apiKey}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    };

    let attempt = 0;

    while (true) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), timeout);

        let response;

        try {
            const fetchOptions = {
                method,
                headers,
                signal: controller.signal,
            };

            if (data !== null && method !== 'GET' && method !== 'DELETE') {
                fetchOptions.body = JSON.stringify(data);
            }

            response = await fetch(url, fetchOptions);
        } catch (err) {
            clearTimeout(timer);
            // Erreur réseau ou timeout (AbortError)
            if (attempt < retries - 1) {
                attempt++;
                await wait(attempt - 1);
                continue;
            }
            throw new FactProError(
                err.name === 'AbortError'
                    ? `Timeout après ${timeout}ms`
                    : `Erreur réseau : ${err.message}`,
                null,
                'NETWORK_ERROR'
            );
        }

        clearTimeout(timer);

        // Retry sur 429 et 503
        if ((response.status === 429 || response.status === 503) && attempt < retries - 1) {
            attempt++;
            await wait(attempt - 1);
            continue;
        }

        // Réponse 204 No Content (pas de corps)
        if (response.status === 204) {
            return null;
        }

        // Téléchargement binaire (PDF, etc.)
        const contentType = response.headers.get('Content-Type') || '';
        if (contentType.includes('application/pdf') || contentType.includes('application/octet-stream')) {
            if (!response.ok) {
                throw new FactProError(`Erreur ${response.status}`, response.status);
            }
            // En Node.js 18+ et browser : ArrayBuffer → Uint8Array
            const buffer = await response.arrayBuffer();
            return new Uint8Array(buffer);
        }

        // Réponse JSON
        let json;
        try {
            json = await response.json();
        } catch {
            throw new FactProError(`Réponse non-JSON du serveur (${response.status})`, response.status);
        }

        if (response.status === 401) {
            throw new AuthError(json.message || 'Non autorisé');
        }

        if (response.status === 422) {
            throw new ValidationError(
                json.message || 'Données invalides',
                json.errors || {}
            );
        }

        if (!response.ok) {
            throw new FactProError(
                json.message || `Erreur HTTP ${response.status}`,
                response.status,
                json.code || null
            );
        }

        return json;
    }
}
