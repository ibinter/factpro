# @ibigsoft/factpro-sdk

SDK JavaScript officiel pour l'[API FactPro](https://app.factpro.ibigsoft.com).  
Compatible **Node.js 18+** (fetch natif) et **navigateurs modernes** (ES2020+).

## Installation

```bash
npm install @ibigsoft/factpro-sdk
```

## Quick Start

```javascript
import { FactProClient } from '@ibigsoft/factpro-sdk';

const client = new FactProClient({
    apiKey: 'votre-cle-api-sanctum',
});

// Lister les factures en attente
const invoices = await client.invoices.list({ status: 'final' });
console.log(`${invoices.data.length} factures`);

// Créer un client
const customer = await client.customers.create({
    name: 'ACME Corp',
    email: 'contact@acme.com',
    type: 'company',
});

// Créer et finaliser une facture
const doc = await client.documents.create({
    type: 'invoice',
    customer_id: customer.data.id,
    items: [{ description: 'Conseil', quantity: 1, unit_price: 50000 }],
});
await client.documents.finalize(doc.data.uuid);
```

## Configuration

| Option    | Type   | Défaut                              | Description              |
|-----------|--------|-------------------------------------|--------------------------|
| `apiKey`  | string | —                                   | Token Sanctum (requis)   |
| `baseUrl` | string | `https://app.factpro.ibigsoft.com`  | URL de votre instance    |
| `timeout` | number | `10000`                             | Timeout en ms            |
| `retries` | number | `3`                                 | Retries sur 429/503      |

## Ressources disponibles

- `client.documents` — Devis, factures, avoirs, bons de livraison
- `client.customers` — Clients et prospects
- `client.products`  — Produits et services
- `client.invoices`  — Raccourci factures avec paiements

## Gestion des erreurs

```javascript
import { FactProClient, AuthError, ValidationError, FactProError } from '@ibigsoft/factpro-sdk';

try {
    await client.documents.create({});
} catch (e) {
    if (e instanceof ValidationError) {
        console.log(e.errors); // { customer_id: ['required'] }
    } else if (e instanceof AuthError) {
        console.log('Token invalide');
    } else if (e instanceof FactProError) {
        console.log(e.status, e.message);
    }
}
```

## Exemples

- [`examples/node-example.js`](examples/node-example.js) — Flux complet Node.js
- [`examples/browser-example.html`](examples/browser-example.html) — Demo navigateur standalone
- [`examples/woocommerce-snippet.js`](examples/woocommerce-snippet.js) — Intégration WooCommerce

## Licence

MIT
