<?php

namespace App\Http\Controllers;

use App\Models\Repair;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RepairController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $query = Repair::where('company_id', $company->id)
            ->with('customer')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model_name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $repairs = $query->paginate(20)->withQueryString();

        return Inertia::render('SAV/Index', [
            'repairs'  => $repairs,
            'filters'  => $request->only(['status', 'priority', 'search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $company = $request->user()->currentCompany;
        $customers = Customer::where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        return Inertia::render('SAV/Create', [
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $validated = $request->validate([
            'customer_id'       => 'nullable|exists:customers,id',
            'customer_name'     => 'nullable|string|max:255',
            'device_type'       => 'required|in:smartphone,tablet,computer,printer,tv,other',
            'brand'             => 'nullable|string|max:255',
            'model_name'        => 'nullable|string|max:255',
            'serial_number'     => 'nullable|string|max:255',
            'issue_description' => 'required|string',
            'priority'          => 'required|in:low,normal,high,urgent',
            'deposit_amount'    => 'nullable|numeric|min:0',
            'promised_at'       => 'nullable|date',
            'customer_notes'    => 'nullable|string',
            'technician_name'   => 'nullable|string|max:255',
        ]);

        $repair = Repair::create([
            'company_id'        => $company->id,
            'customer_id'       => $validated['customer_id'] ?? null,
            'ticket_number'     => Repair::generateTicketNumber($company),
            'device_type'       => $validated['device_type'],
            'brand'             => $validated['brand'] ?? null,
            'model_name'        => $validated['model_name'] ?? null,
            'serial_number'     => $validated['serial_number'] ?? null,
            'issue_description' => $validated['issue_description'],
            'priority'          => $validated['priority'],
            'deposit_amount'    => $validated['deposit_amount'] ?? 0,
            'promised_at'       => $validated['promised_at'] ?? null,
            'customer_notes'    => $validated['customer_notes'] ?? null,
            'technician_name'   => $validated['technician_name'] ?? null,
            'status'            => 'received',
        ]);

        return redirect()->route('sav.repairs.show', $repair)
            ->with('success', 'Réparation créée avec succès.');
    }

    public function show(Request $request, Repair $repair): Response
    {
        $company = $request->user()->currentCompany;
        abort_if($repair->company_id !== $company->id, 403);

        $repair->load(['customer', 'parts.product']);

        return Inertia::render('SAV/Show', [
            'repair' => array_merge($repair->toArray(), [
                'status_label'   => $repair->status_label,
                'priority_label' => $repair->priority_label,
            ]),
        ]);
    }

    public function edit(Request $request, Repair $repair): Response
    {
        $company = $request->user()->currentCompany;
        abort_if($repair->company_id !== $company->id, 403);

        $customers = Customer::where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        $repair->load(['customer', 'parts.product']);

        return Inertia::render('SAV/Edit', [
            'repair'    => array_merge($repair->toArray(), [
                'status_label'   => $repair->status_label,
                'priority_label' => $repair->priority_label,
            ]),
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, Repair $repair)
    {
        $company = $request->user()->currentCompany;
        abort_if($repair->company_id !== $company->id, 403);

        $validated = $request->validate([
            'customer_id'       => 'nullable|exists:customers,id',
            'device_type'       => 'sometimes|in:smartphone,tablet,computer,printer,tv,other',
            'brand'             => 'nullable|string|max:255',
            'model_name'        => 'nullable|string|max:255',
            'serial_number'     => 'nullable|string|max:255',
            'issue_description' => 'sometimes|string',
            'diagnosis'         => 'nullable|string',
            'status'            => 'sometimes|in:received,diagnosing,waiting_parts,repairing,ready,delivered,cancelled',
            'priority'          => 'sometimes|in:low,normal,high,urgent',
            'technician_name'   => 'nullable|string|max:255',
            'estimated_cost'    => 'nullable|numeric|min:0',
            'final_cost'        => 'nullable|numeric|min:0',
            'deposit_amount'    => 'nullable|numeric|min:0',
            'promised_at'       => 'nullable|date',
            'delivered_at'      => 'nullable|date',
            'internal_notes'    => 'nullable|string',
            'customer_notes'    => 'nullable|string',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'delivered' && !$repair->delivered_at) {
            $validated['delivered_at'] = now();
        }

        $repair->update($validated);

        return redirect()->route('sav.repairs.show', $repair)
            ->with('success', 'Réparation mise à jour.');
    }

    public function destroy(Request $request, Repair $repair)
    {
        $company = $request->user()->currentCompany;
        abort_if($repair->company_id !== $company->id, 403);

        $repair->delete();

        return redirect()->route('sav.repairs.index')
            ->with('success', 'Réparation supprimée.');
    }
}
