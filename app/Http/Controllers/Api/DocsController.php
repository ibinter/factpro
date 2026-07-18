<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class DocsController extends Controller
{
    /** GET /api/v1/docs — mini-documentation publique de l'API (cahier §20). */
    public function __invoke(): JsonResponse
    {
        $base = url('/api/v1');

        return response()->json([
            'name' => 'IBIG FactPro — API REST publique',
            'version' => 'v1',
            'base_url' => $base,
            'authentication' => [
                'type' => 'Bearer token (Laravel Sanctum)',
                'header' => 'Authorization: Bearer VOTRE_TOKEN',
                'obtain' => 'Générez une clé API depuis FactPro : menu Clés API (/api-tokens). Forfaits BUSINESS (1000 req/h) et ENTERPRISE (illimité) uniquement.',
            ],
            'rate_limits' => [
                'business' => '1000 requêtes / heure',
                'enterprise' => 'illimité',
            ],
            'pagination' => 'Les listes sont paginées (?page=, ?per_page= max 100). Réponse : { data: [...], links: {...}, meta: {...} }.',
            'endpoints' => [
                ['method' => 'GET', 'path' => '/me', 'description' => 'Utilisateur, société courante et forfait.'],
                ['method' => 'GET', 'path' => '/customers', 'description' => 'Liste des clients.', 'params' => ['search', 'page', 'per_page']],
                ['method' => 'POST', 'path' => '/customers', 'description' => 'Créer un client.', 'body' => ['type (individual|company)', 'name*', 'contact_name', 'email', 'phone', 'address', 'city', 'country (2)', 'tax_id', 'currency (3)', 'notes']],
                ['method' => 'GET', 'path' => '/customers/{id}', 'description' => 'Détail d\'un client.'],
                ['method' => 'PUT', 'path' => '/customers/{id}', 'description' => 'Modifier un client.'],
                ['method' => 'DELETE', 'path' => '/customers/{id}', 'description' => 'Supprimer un client.'],
                ['method' => 'GET', 'path' => '/products', 'description' => 'Liste des produits/services.', 'params' => ['search', 'page', 'per_page']],
                ['method' => 'POST', 'path' => '/products', 'description' => 'Créer un produit.', 'body' => ['type (product|service)', 'name*', 'sku', 'barcode', 'description', 'unit', 'price*', 'cost', 'tax_rate*', 'track_stock', 'stock_quantity', 'is_active']],
                ['method' => 'GET', 'path' => '/products/{id}', 'description' => 'Détail d\'un produit.'],
                ['method' => 'PUT', 'path' => '/products/{id}', 'description' => 'Modifier un produit.'],
                ['method' => 'DELETE', 'path' => '/products/{id}', 'description' => 'Supprimer un produit.'],
                ['method' => 'GET', 'path' => '/documents', 'description' => 'Liste des documents.', 'params' => ['type (invoice, quote, …)', 'status', 'search', 'page', 'per_page']],
                ['method' => 'POST', 'path' => '/documents', 'description' => 'Créer un document (devis, facture, …).', 'body' => ['type*', 'customer_id', 'issue_date*', 'due_date', 'currency* (3)', 'reference', 'notes', 'terms', 'finalize (bool : sceller immédiatement)', 'lines*[] {product_id, description*, quantity*, unit_price*, tax_rate, discount_percent, unit}']],
                ['method' => 'GET', 'path' => '/documents/{uuid}', 'description' => 'Détail d\'un document avec ses lignes.'],
                ['method' => 'GET', 'path' => '/documents/{uuid}/pdf', 'description' => 'PDF du document (binaire, QR anti-falsification inclus).'],
            ],
            'example' => [
                'curl' => 'curl -H "Authorization: Bearer VOTRE_TOKEN" -H "Accept: application/json" '.$base.'/customers',
                'create_invoice' => 'curl -X POST -H "Authorization: Bearer VOTRE_TOKEN" -H "Content-Type: application/json" -d \'{"type":"invoice","issue_date":"2026-07-16","currency":"XOF","lines":[{"description":"Prestation","quantity":1,"unit_price":50000,"tax_rate":18}],"finalize":true}\' '.$base.'/documents',
            ],
            'errors' => [
                '401' => 'Token manquant ou invalide.',
                '403' => 'Licence inactive ou forfait insuffisant (BUSINESS minimum).',
                '404' => 'Ressource introuvable ou hors de votre société.',
                '422' => 'Erreur de validation ou limite du forfait atteinte.',
                '429' => 'Quota horaire dépassé (voir en-tête Retry-After).',
            ],
        ]);
    }
}
