/**
 * Données de test réutilisables pour les tests E2E Playwright
 * FactPro — Phase 12
 */

export const testCustomer = {
    name: 'Client E2E Test',
    email: 'e2e@test.factpro',
    phone: '+225 07 00 00 00',
    address: '12 Avenue de la Paix',
    city: 'Abidjan',
    country: 'CI',
}

export const testProduct = {
    name: 'Service E2E Test',
    description: 'Produit créé par les tests automatisés',
    unit_price: '50000',
    tax_rate: '18',
    unit: 'heure',
}

export const testInvoice = {
    type: 'invoice',
    due_date: '2026-12-31',
    notes: 'Facture créée par test automatisé E2E',
}

export const testQuote = {
    type: 'quote',
    valid_until: '2026-12-31',
    notes: 'Devis créé par test automatisé E2E',
}

export const demoCredentials = {
    email: 'demo@factpro.test',
    password: 'Demo@2026',
}

export const adminCredentials = {
    email: 'admin@ibigsoft.com',
    password: 'Admin@Factpro2026',
}

/**
 * Génère un email unique pour éviter les doublons entre runs de tests.
 * @param {string} prefix
 * @returns {string}
 */
export function uniqueEmail(prefix = 'e2e') {
    return `${prefix}-${Date.now()}@test.factpro`
}

/**
 * Génère un nom unique pour éviter les doublons.
 * @param {string} base
 * @returns {string}
 */
export function uniqueName(base = 'E2E Test') {
    return `${base} ${Date.now()}`
}
