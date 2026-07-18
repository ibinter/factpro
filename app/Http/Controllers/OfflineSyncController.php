<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Product;
use App\Services\DocumentNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OfflineSyncController — Synchronisation des documents créés hors-ligne (Phase 12).
 *
 * Routes (middleware auth + license) :
 *   POST /offline-sync/document   → flush()
 *   GET  /offline-sync/cache-data → cacheData()
 */
class OfflineSyncController extends Controller
{
    /**
     * Reçoit les documents créés hors-ligne et les persiste.
     *
     * Payload attendu :
     *   { documents: [ { localId, type, customer_id, issue_date, … }, … ] }
     *
     * Réponse :
     *   { results: [ { localId, serverId, status }, … ] }
     */
    public function flush(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'documents'   => ['array'],
            'documents.*' => ['array'],
        ]);

        $companyId = $request->user()->current_company_id;
        $company   = Company::findOrFail($companyId);
        $results   = [];
        $numberSvc = app(DocumentNumberService::class);

        foreach ($validated['documents'] ?? [] as $docData) {
            $localId = $docData['localId'] ?? null;

            try {
                // Champs autorisés (éviter l'injection de company_id ou d'uuid)
                $allowed = [
                    'type', 'status', 'customer_id', 'currency',
                    'issue_date', 'due_date',
                    'notes', 'payment_terms', 'footer',
                    'discount_type', 'discount_value',
                    'subtotal', 'discount_amount', 'tax_amount', 'total',
                    'created_by',
                ];

                $payload = array_intersect_key($docData, array_flip($allowed));
                $payload['company_id']  = $companyId;
                $payload['status']      = $payload['status'] ?? 'draft';
                $payload['issue_date']  = $payload['issue_date'] ?? now()->toDateString();
                $payload['created_by']  = $request->user()->id;

                // Générer le numéro de document via le service dédié
                $type = $payload['type'] ?? 'invoice';
                $payload['number'] = $numberSvc->next($company, $type);

                $doc = Document::create($payload);

                $results[] = [
                    'localId'  => $localId,
                    'serverId' => $doc->id,
                    'status'   => 'created',
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'localId' => $localId,
                    'status'  => 'failed',
                    'error'   => $e->getMessage(),
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Retourne les clients et produits de la société courante
     * pour pré-remplir IndexedDB côté client.
     */
    public function cacheData(Request $request): JsonResponse
    {
        $companyId = $request->user()->current_company_id;

        $customers = Customer::where('company_id', $companyId)
            ->select('id', 'name', 'email', 'phone', 'company_id')
            ->get();

        $products = Product::where('company_id', $companyId)
            ->select('id', 'name', 'price', 'tax_rate', 'unit', 'company_id')
            ->get();

        return response()->json([
            'customers' => $customers,
            'products'  => $products,
            'cachedAt'  => now()->toISOString(),
        ]);
    }
}
