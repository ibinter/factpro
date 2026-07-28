<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferLine;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WarehouseTransferController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $transfers = WarehouseTransfer::where('company_id', $company->id)
            ->with(['fromWarehouse', 'toWarehouse'])
            ->withCount('lines')
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Warehouses/Transfers/Index', [
            'transfers' => $transfers,
        ]);
    }

    public function create(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $warehouses = Warehouse::where('company_id', $company->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'code']);

        $products = Product::where('company_id', $company->id)
            ->get(['id', 'name', 'sku', 'unit']);

        return Inertia::render('Warehouses/Transfers/Create', [
            'warehouses' => $warehouses,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity_sent' => 'required|numeric|min:0.001',
        ]);

        $this->authorizeWarehouseIds($request, $validated['from_warehouse_id'], $validated['to_warehouse_id']);

        $reference = 'TRF-' . strtoupper(uniqid());

        $transfer = WarehouseTransfer::create([
            'company_id' => $company->id,
            'from_warehouse_id' => $validated['from_warehouse_id'],
            'to_warehouse_id' => $validated['to_warehouse_id'],
            'reference' => $reference,
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
        ]);

        foreach ($validated['lines'] as $line) {
            WarehouseTransferLine::create([
                'transfer_id' => $transfer->id,
                'product_id' => $line['product_id'],
                'quantity_sent' => $line['quantity_sent'],
            ]);
        }

        return redirect()->route('warehouse-transfers.show', $transfer)->with('success', 'Transfert créé avec succès.');
    }

    public function show(Request $request, WarehouseTransfer $warehouseTransfer): Response
    {
        $this->authorizeTransfer($request, $warehouseTransfer);

        $warehouseTransfer->load(['fromWarehouse', 'toWarehouse', 'lines.product']);

        return Inertia::render('Warehouses/Transfers/Show', [
            'transfer' => $warehouseTransfer,
        ]);
    }

    public function ship(Request $request, WarehouseTransfer $warehouseTransfer)
    {
        $this->authorizeTransfer($request, $warehouseTransfer);

        abort_if($warehouseTransfer->status !== 'draft', 422, 'Ce transfert ne peut pas être expédié.');

        $warehouseTransfer->update([
            'status' => 'in_transit',
            'transferred_at' => now(),
        ]);

        return back()->with('success', 'Transfert marqué comme expédié.');
    }

    public function receive(Request $request, WarehouseTransfer $warehouseTransfer)
    {
        $this->authorizeTransfer($request, $warehouseTransfer);

        abort_if($warehouseTransfer->status !== 'in_transit', 422, 'Ce transfert ne peut pas être réceptionné.');

        $validated = $request->validate([
            'lines' => 'required|array',
            'lines.*.id' => 'required|exists:warehouse_transfer_lines,id',
            'lines.*.quantity_received' => 'required|numeric|min:0',
        ]);

        $warehouseTransfer->load('lines');

        foreach ($validated['lines'] as $lineData) {
            WarehouseTransferLine::where('id', $lineData['id'])
                ->where('transfer_id', $warehouseTransfer->id)
                ->update(['quantity_received' => $lineData['quantity_received']]);
        }

        $warehouseTransfer->load('lines');
        $warehouseTransfer->execute();

        return back()->with('success', 'Transfert réceptionné et stocks mis à jour.');
    }

    private function authorizeTransfer(Request $request, WarehouseTransfer $transfer): void
    {
        abort_if($transfer->company_id !== $request->user()->currentCompany->id, 403);
    }

    private function authorizeWarehouseIds(Request $request, int $fromId, int $toId): void
    {
        $companyId = $request->user()->currentCompany->id;
        $count = Warehouse::where('company_id', $companyId)->whereIn('id', [$fromId, $toId])->count();
        abort_if($count < 2, 403);
    }
}
