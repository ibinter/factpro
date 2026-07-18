<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductApiController extends Controller
{
    /** GET /api/v1/products — liste paginée (?search=, ?per_page=). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = Product::where('company_id', $request->user()->current_company_id)
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%")))
            ->orderBy('name')
            ->paginate(min((int) $request->integer('per_page', 15) ?: 15, 100))
            ->withQueryString();

        return ProductResource::collection($products);
    }

    /** POST /api/v1/products */
    public function store(Request $request, LicenseService $licenses): JsonResponse
    {
        $companyId = $request->user()->current_company_id;

        $count = Product::where('company_id', $companyId)->count();
        if ($licenses->limitReached($request->user(), 'products', $count)) {
            return response()->json([
                'message' => 'Limite de produits atteinte pour votre forfait.',
            ], 422);
        }

        $product = Product::create([
            ...$this->validateData($request),
            'company_id' => $companyId,
        ]);

        return (new ProductResource($product))->response()->setStatusCode(201);
    }

    /** GET /api/v1/products/{id} */
    public function show(Request $request, int $id): ProductResource
    {
        return new ProductResource($this->find($request, $id));
    }

    /** PUT /api/v1/products/{id} */
    public function update(Request $request, int $id): ProductResource
    {
        $product = $this->find($request, $id);
        $product->update($this->validateData($request));

        return new ProductResource($product->fresh());
    }

    /** DELETE /api/v1/products/{id} */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->find($request, $id)->delete();

        return response()->json(['message' => 'Produit supprimé.']);
    }

    /** Scope société courante — 404 hors périmètre. */
    private function find(Request $request, int $id): Product
    {
        return Product::where('company_id', $request->user()->current_company_id)
            ->findOrFail($id);
    }

    /** Mêmes règles que le contrôleur web (ProductController). */
    private function validateData(Request $request): array
    {
        return $request->validate([
            'type' => 'required|in:product,service',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:60',
            'barcode' => 'nullable|string|max:60',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:20',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'track_stock' => 'boolean',
            'stock_quantity' => 'nullable|numeric|min:0',
            'stock_alert_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
    }
}
