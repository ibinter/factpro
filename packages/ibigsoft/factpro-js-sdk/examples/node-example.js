/**
 * Exemple Node.js — @ibigsoft/factpro-sdk
 * Prérequis : Node.js 18+
 *
 * Usage :
 *   node examples/node-example.js
 */

import { FactProClient, ValidationError, AuthError } from '../src/index.js';

const client = new FactProClient({
    apiKey:  process.env.FACTPRO_API_KEY || 'votre-cle-api',
    baseUrl: process.env.FACTPRO_URL     || 'https://app.factpro.ibigsoft.com',
});

async function main() {
    try {
        // 1. Vérifier la connexion
        const me = await client.me();
        console.log(`Connecté en tant que : ${me.data?.user?.email}`);

        // 2. Lister les clients
        const customers = await client.customers.list({ page: 1 });
        console.log(`${customers.data?.length ?? 0} clients trouvés`);

        // 3. Créer un client
        const newCustomer = await client.customers.create({
            name:  'Société Exemple SARL',
            email: 'contact@exemple.com',
            phone: '+225 07 00 00 00 00',
            type:  'company',
        });
        console.log(`Client créé : ID ${newCustomer.data?.id}`);

        // 4. Créer un produit
        const product = await client.products.create({
            name:       'Prestation de conseil',
            unit_price: 150000,
            unit:       'heure',
            tax_rate:   18,
        });
        console.log(`Produit créé : ID ${product.data?.id}`);

        // 5. Créer une facture
        const invoice = await client.documents.create({
            type:        'invoice',
            customer_id: newCustomer.data?.id,
            issue_date:  new Date().toISOString().slice(0, 10),
            currency:    'XOF',
            items: [
                {
                    product_id: product.data?.id,
                    quantity:   2,
                    unit_price: 150000,
                },
            ],
        });
        console.log(`Facture créée : UUID ${invoice.data?.uuid}`);

        // 6. Finaliser la facture
        await client.documents.finalize(invoice.data?.uuid);
        console.log('Facture finalisée');

        // 7. Télécharger le PDF
        const pdfBytes = await client.documents.downloadPdf(invoice.data?.uuid);
        const { writeFileSync } = await import('fs');
        writeFileSync('facture.pdf', Buffer.from(pdfBytes));
        console.log(`PDF sauvegardé (${pdfBytes.length} octets)`);

        // 8. Lister les factures en attente
        const pending = await client.invoices.list({ status: 'final' });
        console.log(`${pending.data?.length ?? 0} factures finalisées`);

    } catch (err) {
        if (err instanceof AuthError) {
            console.error('Clé API invalide ou expirée :', err.message);
        } else if (err instanceof ValidationError) {
            console.error('Erreurs de validation :', err.errors);
        } else {
            console.error('Erreur :', err.message);
        }
        process.exit(1);
    }
}

main();
