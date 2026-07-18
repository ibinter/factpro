<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class OpenApiController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'IBIG FactPro API',
                'description' => 'API REST officielle IBIG FactPro — gestion de documents, clients et produits. Réservée aux forfaits BUSINESS+.',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'Support IBIG FactPro',
                    'email' => 'support@ibigfactpro.com',
                ],
            ],
            'servers' => [
                ['url' => rtrim(config('app.url'), '/') . '/api/v1', 'description' => 'Serveur principal'],
            ],
            'security' => [['bearerAuth' => []]],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Sanctum',
                        'description' => 'Token Sanctum généré depuis le tableau de bord FactPro.',
                    ],
                ],
                'schemas' => [
                    'Document' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'string', 'format' => 'uuid'],
                            'type' => ['type' => 'string', 'enum' => ['invoice', 'quote', 'credit_note']],
                            'number' => ['type' => 'string'],
                            'status' => ['type' => 'string', 'enum' => ['draft', 'sent', 'paid', 'cancelled']],
                            'issue_date' => ['type' => 'string', 'format' => 'date'],
                            'due_date' => ['type' => 'string', 'format' => 'date', 'nullable' => true],
                            'currency' => ['type' => 'string'],
                            'total_ht' => ['type' => 'number'],
                            'total_ttc' => ['type' => 'number'],
                            'customer' => ['$ref' => '#/components/schemas/Customer'],
                        ],
                    ],
                    'Customer' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string', 'format' => 'email', 'nullable' => true],
                            'phone' => ['type' => 'string', 'nullable' => true],
                            'address' => ['type' => 'string', 'nullable' => true],
                        ],
                    ],
                    'Product' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'description' => ['type' => 'string', 'nullable' => true],
                            'unit_price' => ['type' => 'number'],
                            'unit' => ['type' => 'string', 'nullable' => true],
                            'tax_rate' => ['type' => 'number', 'nullable' => true],
                        ],
                    ],
                    'Error' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string'],
                            'errors' => ['type' => 'object', 'nullable' => true],
                        ],
                    ],
                ],
            ],
            'paths' => [
                '/documents' => [
                    'get' => [
                        'summary' => 'Lister les documents',
                        'tags' => ['Documents'],
                        'parameters' => [
                            ['name' => 'type', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['invoice', 'quote', 'credit_note']]],
                            ['name' => 'status', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'search', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 15]],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Liste paginée de documents'],
                            '401' => ['description' => 'Non authentifié'],
                        ],
                    ],
                    'post' => [
                        'summary' => 'Créer un document',
                        'tags' => ['Documents'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['type', 'issue_date', 'currency', 'lines'],
                                        'properties' => [
                                            'type' => ['type' => 'string', 'enum' => ['invoice', 'quote', 'credit_note']],
                                            'customer_id' => ['type' => 'integer', 'nullable' => true],
                                            'issue_date' => ['type' => 'string', 'format' => 'date'],
                                            'due_date' => ['type' => 'string', 'format' => 'date', 'nullable' => true],
                                            'currency' => ['type' => 'string'],
                                            'finalize' => ['type' => 'boolean', 'default' => false],
                                            'notes' => ['type' => 'string', 'nullable' => true],
                                            'lines' => [
                                                'type' => 'array',
                                                'items' => [
                                                    'type' => 'object',
                                                    'required' => ['description', 'quantity', 'unit_price'],
                                                    'properties' => [
                                                        'description' => ['type' => 'string'],
                                                        'quantity' => ['type' => 'number'],
                                                        'unit_price' => ['type' => 'number'],
                                                        'tax_rate' => ['type' => 'number', 'nullable' => true],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => ['description' => 'Document créé'],
                            '422' => ['description' => 'Erreur de validation'],
                        ],
                    ],
                ],
                '/documents/{uuid}' => [
                    'get' => [
                        'summary' => 'Récupérer un document',
                        'tags' => ['Documents'],
                        'parameters' => [
                            ['name' => 'uuid', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'format' => 'uuid']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Document trouvé'],
                            '404' => ['description' => 'Document introuvable'],
                        ],
                    ],
                    'put' => [
                        'summary' => 'Mettre à jour un document (draft uniquement)',
                        'tags' => ['Documents'],
                        'parameters' => [
                            ['name' => 'uuid', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'format' => 'uuid']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Document mis à jour'],
                            '404' => ['description' => 'Document introuvable'],
                        ],
                    ],
                    'delete' => [
                        'summary' => 'Supprimer un document (draft uniquement)',
                        'tags' => ['Documents'],
                        'parameters' => [
                            ['name' => 'uuid', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'format' => 'uuid']],
                        ],
                        'responses' => [
                            '204' => ['description' => 'Document supprimé'],
                            '404' => ['description' => 'Document introuvable'],
                        ],
                    ],
                ],
                '/documents/{uuid}/finalize' => [
                    'post' => [
                        'summary' => 'Finaliser (sceller) un document',
                        'tags' => ['Documents'],
                        'parameters' => [
                            ['name' => 'uuid', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'format' => 'uuid']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Document finalisé'],
                            '422' => ['description' => 'Document déjà finalisé'],
                        ],
                    ],
                ],
                '/documents/{uuid}/pdf' => [
                    'get' => [
                        'summary' => 'Télécharger le PDF d\'un document',
                        'tags' => ['Documents'],
                        'parameters' => [
                            ['name' => 'uuid', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string', 'format' => 'uuid']],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Fichier PDF',
                                'content' => ['application/pdf' => ['schema' => ['type' => 'string', 'format' => 'binary']]],
                            ],
                        ],
                    ],
                ],
                '/customers' => [
                    'get' => [
                        'summary' => 'Lister les clients',
                        'tags' => ['Clients'],
                        'parameters' => [
                            ['name' => 'search', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 15]],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Liste des clients'],
                        ],
                    ],
                    'post' => [
                        'summary' => 'Créer un client',
                        'tags' => ['Clients'],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['name'],
                                        'properties' => [
                                            'name' => ['type' => 'string'],
                                            'email' => ['type' => 'string', 'format' => 'email', 'nullable' => true],
                                            'phone' => ['type' => 'string', 'nullable' => true],
                                            'address' => ['type' => 'string', 'nullable' => true],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => ['description' => 'Client créé'],
                            '422' => ['description' => 'Erreur de validation'],
                        ],
                    ],
                ],
                '/customers/{id}' => [
                    'get' => [
                        'summary' => 'Récupérer un client',
                        'tags' => ['Clients'],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Client trouvé'],
                            '404' => ['description' => 'Client introuvable'],
                        ],
                    ],
                    'put' => [
                        'summary' => 'Mettre à jour un client',
                        'tags' => ['Clients'],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Client mis à jour'],
                        ],
                    ],
                    'delete' => [
                        'summary' => 'Supprimer un client',
                        'tags' => ['Clients'],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '204' => ['description' => 'Client supprimé'],
                        ],
                    ],
                ],
                '/products' => [
                    'get' => [
                        'summary' => 'Lister les produits',
                        'tags' => ['Produits'],
                        'parameters' => [
                            ['name' => 'search', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 15]],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Liste des produits'],
                        ],
                    ],
                    'post' => [
                        'summary' => 'Créer un produit',
                        'tags' => ['Produits'],
                        'responses' => [
                            '201' => ['description' => 'Produit créé'],
                        ],
                    ],
                ],
                '/products/{id}' => [
                    'get' => [
                        'summary' => 'Récupérer un produit',
                        'tags' => ['Produits'],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Produit trouvé'],
                            '404' => ['description' => 'Produit introuvable'],
                        ],
                    ],
                    'put' => [
                        'summary' => 'Mettre à jour un produit',
                        'tags' => ['Produits'],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '200' => ['description' => 'Produit mis à jour'],
                        ],
                    ],
                    'delete' => [
                        'summary' => 'Supprimer un produit',
                        'tags' => ['Produits'],
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => [
                            '204' => ['description' => 'Produit supprimé'],
                        ],
                    ],
                ],
                '/me' => [
                    'get' => [
                        'summary' => 'Informations sur l\'utilisateur authentifié',
                        'tags' => ['Utilisateur'],
                        'responses' => [
                            '200' => ['description' => 'Informations utilisateur'],
                        ],
                    ],
                ],
            ],
        ];

        return response()->json($spec);
    }
}
