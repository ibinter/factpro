<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogSettingsController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;
        $products = Product::where('company_id', $company->id)->orderBy('name')->get();

        return Inertia::render('Settings/Catalog', compact('company', 'products'));
    }

    public function update(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'catalog_enabled'      => 'boolean',
            'catalog_slug'         => 'nullable|string|max:100|alpha_dash|unique:companies,catalog_slug,' . $company->id,
            'catalog_title'        => 'nullable|string|max:255',
            'catalog_description'  => 'nullable|string|max:2000',
            'catalog_show_prices'  => 'boolean',
            'catalog_allow_orders' => 'boolean',
            'catalog_cover_color'  => 'nullable|string|max:20',
        ]);

        $company->update($validated);

        return redirect()->back()->with('success', 'Paramètres du catalogue mis à jour.');
    }

    public function toggleProduct(Request $request, Product $product)
    {
        $company = $request->user()->currentCompany;

        abort_if($product->company_id !== $company->id, 403);

        $field = $request->validate([
            'field' => 'required|in:catalog_visible,catalog_featured',
        ])['field'];

        $product->update([$field => !$product->$field]);

        return response()->json([
            'visible'  => $product->catalog_visible,
            'featured' => $product->catalog_featured,
        ]);
    }
}
