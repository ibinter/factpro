<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use Inertia\Inertia;

class PublicCatalogController extends Controller
{
    public function show(string $slug)
    {
        $company = Company::where('catalog_slug', $slug)
            ->where('catalog_enabled', true)
            ->firstOrFail();

        $products = Product::where('company_id', $company->id)
            ->where('catalog_visible', true)
            ->orderBy('catalog_featured', 'desc')
            ->orderBy('catalog_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Public/Catalog', compact('company', 'products'));
    }
}
