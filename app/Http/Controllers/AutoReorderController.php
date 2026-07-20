<?php

namespace App\Http\Controllers;

use App\Models\AutoReorderRule;
use App\Models\Document;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\AutoReorderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AutoReorderController extends Controller
{
    public function __construct(private AutoReorderService $service) {}

    /** GET /stock/auto-reorder */
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $rules = AutoReorderRule::where('company_id', $company->id)
            ->with(['product', 'supplier', 'lastDocument'])
            ->orderByDesc('created_at')
            ->get();

        $lowStock = $this->service->getLowStockProducts($company->id)
            ->map(fn ($rule) => [
                'rule_id'           => $rule->id,
                'product_id'        => $rule->product->id,
                'product_name'      => $rule->product->name,
                'sku'               => $rule->product->sku,
                'stock_quantity'    => (float) $rule->product->stock_quantity,
                'trigger_threshold' => $rule->trigger_threshold,
                'order_quantity'    => $rule->order_quantity,
                'supplier_name'     => $rule->supplier?->name,
                'supplier_id'       => $rule->supplier_id,
                'in_cooldown'       => $rule->isInCooldown(),
            ]);

        $products  = Product::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'stock_quantity', 'reorder_point', 'reorder_quantity', 'cost']);

        $suppliers = Supplier::where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Stock/AutoReorder', [
            'rules'     => $rules,
            'lowStock'  => $lowStock,
            'products'  => $products,
            'suppliers' => $suppliers,
        ]);
    }

    /** POST /stock/auto-reorder */
    public function store(Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'product_id'        => ['required', 'integer', 'exists:products,id'],
            'supplier_id'       => ['nullable', 'integer', 'exists:suppliers,id'],
            'trigger_threshold' => ['required', 'integer', 'min:0'],
            'order_quantity'    => ['required', 'integer', 'min:1'],
            'is_active'         => ['boolean'],
            'cooldown_hours'    => ['integer', 'min:1', 'max:8760'],
            'auto_approve'      => ['boolean'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ]);

        AutoReorderRule::create([
            ...$data,
            'company_id' => $company->id,
        ]);

        return redirect()->route('stock.auto-reorder.index')->with('success', 'Règle de réapprovisionnement créée.');
    }

    /** PUT /stock/auto-reorder/{rule} */
    public function update(AutoReorderRule $rule, Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;
        abort_if($rule->company_id !== $company->id, 403);

        $data = $request->validate([
            'product_id'        => ['sometimes', 'integer', 'exists:products,id'],
            'supplier_id'       => ['nullable', 'integer', 'exists:suppliers,id'],
            'trigger_threshold' => ['sometimes', 'integer', 'min:0'],
            'order_quantity'    => ['sometimes', 'integer', 'min:1'],
            'is_active'         => ['boolean'],
            'cooldown_hours'    => ['integer', 'min:1', 'max:8760'],
            'auto_approve'      => ['boolean'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ]);

        $rule->update($data);

        return redirect()->route('stock.auto-reorder.index')->with('success', 'Règle mise à jour.');
    }

    /** DELETE /stock/auto-reorder/{rule} */
    public function destroy(AutoReorderRule $rule, Request $request): RedirectResponse
    {
        abort_if($rule->company_id !== $request->user()->currentCompany->id, 403);
        $rule->delete();

        return redirect()->route('stock.auto-reorder.index')->with('success', 'Règle supprimée.');
    }

    /** POST /stock/auto-reorder/{rule}/trigger — déclenchement manuel */
    public function trigger(AutoReorderRule $rule, Request $request): RedirectResponse
    {
        abort_if($rule->company_id !== $request->user()->currentCompany->id, 403);

        $document = $this->service->createPurchaseOrder($rule);

        return redirect()->route('stock.auto-reorder.index')->with('success', 'Bon de commande '.$document->number.' créé.');
    }

    /** GET /stock/auto-reorder/simulate */
    public function simulate(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;
        $result  = $this->service->simulate($company->id);

        return response()->json($result);
    }

    /** GET /stock/auto-reorder/history */
    public function history(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany;

        $history = AutoReorderRule::where('company_id', $company->id)
            ->whereNotNull('last_triggered_at')
            ->with(['product', 'supplier', 'lastDocument'])
            ->orderByDesc('last_triggered_at')
            ->get()
            ->map(fn ($rule) => [
                'rule_id'            => $rule->id,
                'product_name'       => $rule->product->name,
                'order_quantity'     => $rule->order_quantity,
                'supplier_name'      => $rule->supplier?->name,
                'last_triggered_at'  => $rule->last_triggered_at?->toISOString(),
                'document_number'    => $rule->lastDocument?->number,
                'document_status'    => $rule->lastDocument?->status,
                'document_id'        => $rule->last_document_id,
            ]);

        return response()->json($history);
    }
}
