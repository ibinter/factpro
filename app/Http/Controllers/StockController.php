<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Gestion des stocks avancée (cahier des charges §8) :
 * mouvements, ajustements manuels, inventaire, valorisation CMUP.
 */
class StockController extends Controller
{
    public function __construct(private StockService $stock)
    {
    }

    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $movements = StockMovement::where('company_id', $company->id)
            ->with([
                'product:id,name,sku',
                'creator:id,name',
                'document:id,number,type',
            ])
            ->when($request->product_id, fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->date_from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->date_to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest('created_at')->latest('id')
            ->paginate(20)
            ->withQueryString();

        $tracked = Product::where('company_id', $company->id)
            ->where('track_stock', true)
            ->get(['id', 'name', 'stock_quantity', 'stock_alert_threshold', 'cost']);

        $alerts = $tracked
            ->filter(fn ($p) => $p->stock_alert_threshold !== null
                && (float) $p->stock_quantity <= (float) $p->stock_alert_threshold)
            ->sortBy('name')
            ->values()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'stock_quantity' => (float) $p->stock_quantity,
                'stock_alert_threshold' => (float) $p->stock_alert_threshold,
            ]);

        return Inertia::render('Stock/Index', [
            'movements' => $movements,
            'filters' => $request->only('product_id', 'type', 'date_from', 'date_to'),
            'stats' => [
                'tracked_count' => $tracked->count(),
                'total_value' => round($tracked->sum(fn ($p) => (float) $p->stock_quantity * (float) $p->cost), 2),
                'alert_count' => $alerts->count(),
            ],
            'alerts' => $alerts,
            'products' => Product::where('company_id', $company->id)
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'stock_quantity', 'unit', 'track_stock']),
        ]);
    }

    /** Mouvement manuel : entrée, sortie ou ajustement à un stock cible. */
    public function adjust(Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'product_id' => ['required', Rule::exists('products', 'id')
                ->where('company_id', $company->id)
                ->where('track_stock', true)
                ->whereNull('deleted_at')],
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'nullable|required_unless:type,adjustment|numeric|min:0.01',
            'target' => 'nullable|required_if:type,adjustment|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'reason' => 'required|string|max:255',
        ]);

        $product = Product::where('company_id', $company->id)->findOrFail($data['product_id']);

        $attrs = ['reason' => $data['reason'], 'created_by' => $request->user()->id];

        if ($data['type'] === 'adjustment') {
            $this->stock->record($product, 'adjustment', 0, [...$attrs, 'target' => (float) $data['target']]);
        } else {
            if ($data['type'] === 'in' && isset($data['unit_cost']) && $data['unit_cost'] !== null) {
                $attrs['unit_cost'] = (float) $data['unit_cost'];
            }
            $this->stock->record($product, $data['type'], (float) $data['quantity'], $attrs);
        }

        return back()->with('success', 'Mouvement de stock enregistré.');
    }

    /** Écran de saisie de l'inventaire physique. */
    public function inventory(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        return Inertia::render('Stock/Inventory', [
            'products' => Product::where('company_id', $company->id)
                ->where('track_stock', true)
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'unit', 'stock_quantity']),
        ]);
    }

    /** Applique l'inventaire : un mouvement `inventory` par écart constaté. */
    public function applyInventory(Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', Rule::exists('products', 'id')
                ->where('company_id', $company->id)
                ->where('track_stock', true)
                ->whereNull('deleted_at')],
            'items.*.counted' => 'required|numeric|min:0',
        ]);

        $applied = 0;

        foreach ($data['items'] as $item) {
            $product = Product::where('company_id', $company->id)->find($item['product_id']);

            if (! $product || (float) $item['counted'] === (float) $product->stock_quantity) {
                continue;
            }

            $this->stock->record($product, 'inventory', 0, [
                'target' => (float) $item['counted'],
                'reason' => 'Inventaire du '.now()->format('d/m/Y'),
                'created_by' => $request->user()->id,
            ]);
            $applied++;
        }

        return redirect()->route('stock.index')
            ->with('success', $applied > 0
                ? "Inventaire appliqué : {$applied} écart(s) corrigé(s)."
                : 'Inventaire validé : aucun écart constaté.');
    }

    /** Valorisation CMUP + analyse ABC simplifiée. */
    public function valuation(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $products = Product::where('company_id', $company->id)
            ->where('track_stock', true)
            ->get(['id', 'name', 'sku', 'unit', 'stock_quantity', 'cost', 'price']);

        $rows = $products->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sku' => $p->sku,
            'unit' => $p->unit,
            'quantity' => (float) $p->stock_quantity,
            'cost' => (float) $p->cost,
            'value' => round((float) $p->stock_quantity * (float) $p->cost, 2),
            'price' => (float) $p->price,
            'margin_percent' => (float) $p->price > 0
                ? round(((float) $p->price - (float) $p->cost) / (float) $p->price * 100, 1)
                : null,
        ])->sortByDesc('value')->values();

        $total = round($rows->sum('value'), 2);

        // Analyse ABC : A = 80 % de la valeur cumulée, B = 15 %, C = 5 %
        $cumulative = 0.0;
        $rows = $rows->map(function ($row) use (&$cumulative, $total) {
            $cumulative += $row['value'];
            $share = $total > 0 ? $cumulative / $total : 1;
            $row['abc_class'] = $share <= 0.80 ? 'A' : ($share <= 0.95 ? 'B' : 'C');

            return $row;
        });

        return Inertia::render('Stock/Valuation', [
            'rows' => $rows,
            'total' => $total,
            'top10' => $rows->take(10)->values(),
        ]);
    }
}
