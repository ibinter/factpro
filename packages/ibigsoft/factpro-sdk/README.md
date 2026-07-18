# IBIG FactPro PHP SDK

SDK PHP officiel pour l'API REST IBIG FactPro.

## Installation

```bash
composer require ibigsoft/factpro-sdk
```

## Usage

```php
$client = new \FactPro\FactProClient('votre-token-api');

// Lister les factures
$invoices = $client->documents()->list(['type' => 'invoice']);

// Créer une facture
$invoice = $client->documents()->create([
    'type' => 'invoice',
    'customer_id' => 1,
    'issue_date' => '2025-01-15',
    'currency' => 'XOF',
    'lines' => [
        [
            'description' => 'Prestation de service',
            'quantity' => 1,
            'unit_price' => 50000,
            'tax_rate' => 18,
        ]
    ]
]);

// Finaliser (sceller)
$client->documents()->finalize($invoice['data']['id']);

// Télécharger le PDF
$pdf = $client->documents()->pdf($invoice['data']['id']);
file_put_contents('facture.pdf', $pdf);

// Gestion des clients
$customers = $client->customers()->list();
$customer = $client->customers()->create(['name' => 'Acme Corp', 'email' => 'contact@acme.com']);
$client->customers()->update($customer['data']['id'], ['phone' => '+225 00 00 00 00']);

// Produits
$products = $client->products()->list();
```

## Gestion des erreurs

```php
use FactPro\Exceptions\AuthException;
use FactPro\Exceptions\ValidationException;
use FactPro\Exceptions\FactProException;

try {
    $client->documents()->list();
} catch (AuthException $e) {
    // Token invalide ou expiré
} catch (ValidationException $e) {
    $errors = $e->getErrors();
} catch (FactProException $e) {
    $code = $e->getStatusCode();
}
```

## Prérequis

- PHP 8.1+
- Forfait BUSINESS+ sur IBIG FactPro
- Token API généré depuis le tableau de bord
