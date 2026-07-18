/**
 * Exemple WooCommerce → FactPro
 *
 * Ce snippet montre comment envoyer une commande WooCommerce
 * vers FactPro sous forme de bon de commande (purchase order).
 *
 * Contexte : script Node.js déclenché par un webhook WooCommerce
 * (ex: action "woocommerce_new_order" via un plugin webhook).
 *
 * Prérequis :
 *   - Node.js 18+
 *   - npm install @ibigsoft/factpro-sdk
 *   - Variables d'environnement FACTPRO_API_KEY et FACTPRO_URL
 */

import { FactProClient, ValidationError } from '@ibigsoft/factpro-sdk';

const client = new FactProClient({
    apiKey:  process.env.FACTPRO_API_KEY,
    baseUrl: process.env.FACTPRO_URL || 'https://app.factpro.ibigsoft.com',
});

/**
 * Convertit une commande WooCommerce en bon de commande FactPro.
 *
 * @param {Object} wooOrder - Objet commande WooCommerce (webhook payload)
 * @returns {Promise<Object>} Le document FactPro créé
 */
export async function syncWooOrderToFactPro(wooOrder) {
    // 1. Trouver ou créer le client dans FactPro
    let customerId;

    try {
        const existing = await client.customers.list({
            search: wooOrder.billing.email,
        });

        if (existing.data && existing.data.length > 0) {
            customerId = existing.data[0].id;
        } else {
            const newCustomer = await client.customers.create({
                name:    `${wooOrder.billing.first_name} ${wooOrder.billing.last_name}`.trim(),
                email:   wooOrder.billing.email,
                phone:   wooOrder.billing.phone || null,
                address: wooOrder.billing.address_1 || null,
                city:    wooOrder.billing.city    || null,
                type:    'individual',
            });
            customerId = newCustomer.data.id;
        }
    } catch (err) {
        console.error('Erreur lors de la recherche/création du client :', err.message);
        throw err;
    }

    // 2. Mapper les lignes WooCommerce vers les items FactPro
    const items = wooOrder.line_items.map(line => ({
        description: line.name,
        quantity:    line.quantity,
        unit_price:  parseFloat(line.price),
        tax_rate:    line.tax_class === 'standard' ? 18 : 0,
    }));

    // 3. Créer le bon de commande dans FactPro
    let document;
    try {
        document = await client.documents.create({
            type:        'invoice',   // Ou 'quote' selon votre flux
            customer_id: customerId,
            issue_date:  new Date().toISOString().slice(0, 10),
            currency:    wooOrder.currency || 'XOF',
            reference:   `WOO-${wooOrder.id}`,  // Référence croisée
            notes:       `Commande WooCommerce #${wooOrder.number}`,
            items,
        });
    } catch (err) {
        if (err instanceof ValidationError) {
            console.error('Erreurs de validation FactPro :', err.errors);
        }
        throw err;
    }

    // 4. Finaliser et envoyer si la commande est payée
    if (wooOrder.status === 'processing' || wooOrder.status === 'completed') {
        await client.documents.finalize(document.data.uuid);
        console.log(`Document finalisé : ${document.data.uuid}`);

        // Enregistrer le paiement
        if (wooOrder.date_paid) {
            await client.invoices.registerPayment(document.data.uuid, {
                amount: parseFloat(wooOrder.total),
                method: mapPaymentMethod(wooOrder.payment_method),
                date:   wooOrder.date_paid.slice(0, 10),
            });
            console.log('Paiement enregistré');
        }
    }

    console.log(`Commande WOO #${wooOrder.number} → FactPro ${document.data.uuid}`);
    return document;
}

/**
 * Convertit une méthode de paiement WooCommerce en code FactPro.
 * @param {string} wooMethod
 * @returns {string}
 */
function mapPaymentMethod(wooMethod) {
    const map = {
        'bacs':         'bank_transfer',
        'cheque':       'check',
        'cod':          'cash',
        'stripe':       'card',
        'paypal':       'paypal',
        'cinetpay':     'mobile_money',
        'orange_money': 'mobile_money',
        'wave':         'mobile_money',
    };
    return map[wooMethod] || 'other';
}

// --- Point d'entrée webhook (exemple Express) ---
// const express = require('express');
// const app = express();
// app.use(express.json());
// app.post('/webhook/woocommerce/order', async (req, res) => {
//     try {
//         const doc = await syncWooOrderToFactPro(req.body);
//         res.json({ success: true, uuid: doc.data.uuid });
//     } catch (err) {
//         res.status(500).json({ error: err.message });
//     }
// });
// app.listen(3000);
