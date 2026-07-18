<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarcodeLookupController extends Controller
{
    /**
     * Recherche un produit par son code-barres (barcode), SKU ou référence.
     * GET /barcode/lookup?code=3760168090128
     */
    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:255'],
        ]);

        $code = $request->input('code');

        $companyId = $request->user()->current_company_id
            ?? $request->user()->companies()->first()?->id;

        if (! $companyId) {
            return response()->json(['found' => false, 'code' => $code, 'error' => 'No company'], 422);
        }

        $product = Product::where('company_id', $companyId)
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)
                  ->orWhere('sku', $code)
                  ->orWhere('reference', $code);
            })
            ->first();

        if (! $product) {
            return response()->json(['found' => false, 'code' => $code], 404);
        }

        return response()->json([
            'found'   => true,
            'product' => [
                'id'       => $product->id,
                'name'     => $product->name,
                'sku'      => $product->sku,
                'barcode'  => $product->barcode,
                'price'    => (float) $product->price,
                'tax_rate' => (float) $product->tax_rate,
                'stock'    => $product->stock_quantity !== null ? (float) $product->stock_quantity : null,
                'unit'     => $product->unit ?? 'unité',
            ],
        ]);
    }

    /**
     * Assigne ou met à jour le code-barres d'un produit.
     * POST /barcode/assign
     */
    public function assign(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer'],
            'barcode'    => ['required', 'string', 'max:255'],
        ]);

        $companyId = $request->user()->current_company_id
            ?? $request->user()->companies()->first()?->id;

        if (! $companyId) {
            return response()->json(['error' => 'No company'], 422);
        }

        // Vérifier que le produit appartient à l'entreprise
        $product = Product::where('company_id', $companyId)
            ->where('id', $request->input('product_id'))
            ->firstOrFail();

        // Vérifier l'unicité du code-barres au sein de l'entreprise
        $duplicate = Product::where('company_id', $companyId)
            ->where('barcode', $request->input('barcode'))
            ->where('id', '!=', $product->id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'error'   => 'duplicate',
                'message' => 'Ce code-barres est déjà utilisé par un autre produit.',
            ], 422);
        }

        $product->update(['barcode' => $request->input('barcode')]);

        return response()->json([
            'success' => true,
            'product' => [
                'id'      => $product->id,
                'name'    => $product->name,
                'barcode' => $product->barcode,
            ],
        ]);
    }
}
