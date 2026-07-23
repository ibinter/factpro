<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Product;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim($request->string('q'));
        $company = $request->user()->currentCompany;

        if (strlen($q) < 2 || !$company) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Customers
        $customers = Customer::where('company_id', $company->id)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'email', 'type']);

        foreach ($customers as $c) {
            $results[] = [
                'type'  => 'client',
                'icon'  => '👤',
                'label' => $c->name,
                'sub'   => $c->email,
                'url'   => "/customers/{$c->id}",
            ];
        }

        // Documents
        $docs = Document::where('company_id', $company->id)
            ->where(function ($query) use ($q) {
                $query->where('number', 'like', "%{$q}%")
                      ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$q}%"));
            })
            ->with('customer:id,name')
            ->limit(5)
            ->get(['id', 'number', 'type', 'total', 'status', 'customer_id']);

        foreach ($docs as $d) {
            $results[] = [
                'type'  => $d->type,
                'icon'  => match($d->type) { 'invoice' => '🧾', 'quote' => '📋', default => '📄' },
                'label' => $d->number . ' — ' . ($d->customer?->name ?? ''),
                'sub'   => ucfirst($d->status) . ' · ' . number_format($d->total, 0, ',', ' ') . ' FCFA',
                'url'   => "/documents/{$d->id}",
            ];
        }

        // Products
        $products = Product::where('company_id', $company->id)
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%");
            })
            ->limit(5)
            ->get(['id', 'name', 'sku', 'price']);

        foreach ($products as $p) {
            $results[] = [
                'type'  => 'product',
                'icon'  => '📦',
                'label' => $p->name,
                'sub'   => ($p->sku ? 'SKU: '.$p->sku.' · ' : '') . number_format($p->price, 0, ',', ' ') . ' FCFA',
                'url'   => "/products/{$p->id}",
            ];
        }

        return response()->json(['results' => array_slice($results, 0, 15)]);
    }
}
