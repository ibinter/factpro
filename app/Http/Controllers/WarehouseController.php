<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $warehouses = Warehouse::where('company_id', $company->id)
            ->withCount('stocks')
            ->get()
            ->map(function ($warehouse) {
                $totalValue = WarehouseStock::where('warehouse_id', $warehouse->id)
                    ->join('products', 'products.id', '=', 'warehouse_stocks.product_id')
                    ->selectRaw('SUM(warehouse_stocks.quantity * products.price) as total')
                    ->value('total') ?? 0;

                $warehouse->total_value = round($totalValue, 2);
                return $warehouse;
            });

        return Inertia::render('Warehouses/Index', [
            'warehouses' => $warehouses,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Warehouses/Create');
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:warehouses,code,NULL,id,company_id,' . $company->id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['is_default'])) {
            Warehouse::where('company_id', $company->id)->update(['is_default' => false]);
        }

        $warehouse = Warehouse::create(array_merge($validated, ['company_id' => $company->id]));

        return redirect()->route('warehouses.show', $warehouse)->with('success', 'Entrepôt créé avec succès.');
    }

    public function show(Request $request, Warehouse $warehouse): Response
    {
        $this->authorizeWarehouse($request, $warehouse);

        $stocks = WarehouseStock::where('warehouse_id', $warehouse->id)
            ->with('product')
            ->paginate(30);

        $stocks->getCollection()->transform(function ($stock) {
            $stock->is_low = $stock->isLowStock();
            $stock->value = round($stock->quantity * ($stock->product->price ?? 0), 2);
            return $stock;
        });

        return Inertia::render('Warehouses/Show', [
            'warehouse' => $warehouse,
            'stocks' => $stocks,
        ]);
    }

    public function edit(Request $request, Warehouse $warehouse): Response
    {
        $this->authorizeWarehouse($request, $warehouse);

        return Inertia::render('Warehouses/Edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $company = $request->user()->currentCompany;
        $this->authorizeWarehouse($request, $warehouse);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:warehouses,code,' . $warehouse->id . ',id,company_id,' . $company->id,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['is_default'])) {
            Warehouse::where('company_id', $company->id)
                ->where('id', '!=', $warehouse->id)
                ->update(['is_default' => false]);
        }

        $warehouse->update($validated);

        return redirect()->route('warehouses.show', $warehouse)->with('success', 'Entrepôt mis à jour.');
    }

    public function destroy(Request $request, Warehouse $warehouse)
    {
        $this->authorizeWarehouse($request, $warehouse);

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Entrepôt supprimé.');
    }

    public function adjustStock(Request $request, Warehouse $warehouse)
    {
        $this->authorizeWarehouse($request, $warehouse);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric',
            'reason' => 'required|string|max:255',
        ]);

        $warehouse->adjustStock($validated['product_id'], $validated['quantity']);

        return back()->with('success', 'Stock ajusté avec succès.');
    }

    private function authorizeWarehouse(Request $request, Warehouse $warehouse): void
    {
        abort_if($warehouse->company_id !== $request->user()->currentCompany->id, 403);
    }
}
