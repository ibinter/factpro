ï»¿<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Product;
use App\Services\BarcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PublicProductController extends Controller
{
    public function __construct(private BarcodeService $barcodes) {}

    /** Page publique produit (Inertia sans auth). */
    public function show(string $companySlug, string $productSlug): InertiaResponse
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $product = Product::where('company_id', $company->id)
            ->where('public_slug', $productSlug)
            ->where('public_page_enabled', true)
            ->firstOrFail();

        $publicUrl = route('public.product.show', [$companySlug, $productSlug]);
        $qrCode = $this->barcodes->qrPngDataUri($publicUrl);

        $stockStatus = 'available';
        if ($product->track_stock) {
            if ((float) $product->stock_quantity <= 0) {
                $stockStatus = 'out_of_stock';
            } elseif ((float) $product->stock_quantity <= 5) {
                $stockStatus = 'low_stock';
            }
        }

        return Inertia::render('Public/ProductPage', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'reference' => $product->reference ?? $product->sku,
                'category' => $product->category ?? null,
                'price' => (float) $product->price,
                'tax_rate' => (float) ($product->tax_rate ?? 0),
                'description' => $product->public_description ?: $product->description,
                'public_images' => $product->public_images ?? [],
                'allow_online_order' => (bool) $product->allow_online_order,
                'minimum_order_qty' => (int) $product->minimum_order_qty,
                'stock_status' => $stockStatus,
                'unit' => $product->unit ?? null,
            ],
            'company' => [
                'name' => $company->name,
                'logo' => $company->logo ?? null,
                'address' => $company->address ?? null,
                'phone' => $company->phone ?? null,
                'email' => $company->email ?? null,
                'slug' => $companySlug,
            ],
            'qrCode' => $qrCode,
            'publicUrl' => $publicUrl,
        ]);
    }

    /** API JSON produit pour intï¿½gration externe. */
    public function api(string $companySlug, string $productSlug): JsonResponse
    {
        $company = Company::where('slug', $companySlug)->firstOrFail();

        $product = Product::where('company_id', $company->id)
            ->where('public_slug', $productSlug)
            ->where('public_page_enabled', true)
            ->firstOrFail();

        $stockStatus = 'available';
        if ($product->track_stock) {
            if ((float) $product->stock_quantity <= 0) {
                $stockStatus = 'out_of_stock';
            } elseif ((float) $product->stock_quantity <= 5) {
                $stockStatus = 'low_stock';
            }
        }

        return response()->json([
            'product' => [
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'tax_rate' => (float) ($product->tax_rate ?? 0),
                'description' => $product->public_description ?: $product->description,
                'public_images' => $product->public_images ?? [],
                'allow_online_order' => (bool) $product->allow_online_order,
                'minimum_order_qty' => (int) $product->minimum_order_qty,
                'stock_status' => $stockStatus,
                'unit' => $product->unit ?? null,
            ],
            'company' => [
                'name' => $company->name,
                'slug' => $companySlug,
            ],
            'public_url' => route('public.product.show', [$companySlug, $productSlug]),
        ]);
    }

    /** Active la page publique pour un produit. */
    public function enablePublic(Request $request, Product $product): \Illuminate\Http\RedirectResponse
    {
        abort_unless($product->company_id === $request->user()->current_company_id, 403);

        $data = $request->validate([
            'public_description' => ['nullable', 'string'],
            'allow_online_order' => ['sometimes', 'boolean'],
            'minimum_order_qty' => ['sometimes', 'integer', 'min:1'],
        ]);

        $slug = $this->generateSlug($product);

        $product->update([
            'public_page_enabled' => true,
            'public_slug' => $slug,
            'public_description' => $data['public_description'] ?? $product->public_description,
            'allow_online_order' => $data['allow_online_order'] ?? $product->allow_online_order,
            'minimum_order_qty' => $data['minimum_order_qty'] ?? $product->minimum_order_qty,
        ]);

        return back()->with('success', 'Page publique activï¿½e.');
    }

    /** Dï¿½sactive la page publique pour un produit. */
    public function disablePublic(Request $request, Product $product): \Illuminate\Http\RedirectResponse
    {
        abort_unless($product->company_id === $request->user()->current_company_id, 403);

        $product->update(['public_page_enabled' => false]);

        return back()->with('success', 'Page publique dï¿½sactivï¿½e.');
    }

    /** Gï¿½nï¿½re/met ï¿½ jour le slug automatiquement depuis le nom. */
    private function generateSlug(Product $product): string
    {
        // Include company_id scope for uniqueness per company
        $base = Str::slug($product->name);

        $slug = $base;
        $count = 1;

        while (
            Product::where('public_slug', $slug)
                ->where('id', '!=', $product->id)
                ->exists()
        ) {
            $slug = $base.'-'.$count;
            $count++;
        }

        return $slug;
    }
}

