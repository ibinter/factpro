<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $products = Product::where('company_id', $company->id)
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('barcode', 'like', "%{$s}%")))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request, LicenseService $licenses): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        if ($licenses->limitReached($request->user(), 'products', $company->products()->count())) {
            return back()->with('error', 'Limite de produits atteinte pour votre forfait. Passez au forfait supérieur.');
        }

        $data = $this->validateData($request);
        Product::create([...$data, 'company_id' => $company->id]);

        return back()->with('success', 'Produit créé avec succès.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->company_id === $request->user()->current_company_id, 403);

        $product->update($this->validateData($request));

        return back()->with('success', 'Produit mis à jour.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->company_id === $request->user()->current_company_id, 403);

        $product->delete();

        return back()->with('success', 'Produit supprimé.');
    }

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
