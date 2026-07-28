<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Lease;
use App\Models\Property;
use App\Models\RentPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaseController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $leases = Lease::whereHas('property', fn($q) => $q->where('company_id', $company->id))
            ->with(['property', 'tenant'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('RealEstate/Leases/Index', [
            'leases' => $leases,
        ]);
    }

    public function create(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $availableProperties = Property::where('company_id', $company->id)
            ->where('status', 'available')
            ->get(['id', 'name', 'reference', 'monthly_rent', 'currency']);

        $customers = Customer::where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('RealEstate/Leases/Create', [
            'availableProperties' => $availableProperties,
            'customers'           => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'property_id'         => 'required|exists:properties,id',
            'customer_id'         => 'required|exists:customers,id',
            'start_date'          => 'required|date',
            'end_date'            => 'nullable|date|after:start_date',
            'is_open_ended'       => 'boolean',
            'monthly_rent'        => 'required|numeric|min:0',
            'deposit_amount'      => 'nullable|numeric|min:0',
            'payment_day'         => 'required|integer|min:1|max:28',
            'renewal_notice_days' => 'nullable|integer|min:0',
            'notes'               => 'nullable|string',
        ]);

        // Vérifier que le bien appartient à la société
        $property = Property::where('id', $data['property_id'])
            ->where('company_id', $company->id)
            ->firstOrFail();

        $lease = Lease::create($data);

        // Marquer le bien comme loué
        $property->update(['status' => 'rented']);

        return redirect()->route('real-estate.leases.show', $lease)
            ->with('success', 'Bail créé avec succès.');
    }

    public function show(Request $request, Lease $baux): Response
    {
        $company = $request->user()->currentCompany;
        abort_if($baux->property->company_id !== $company->id, 403);

        $baux->load(['property', 'tenant', 'rentPayments.document']);

        return Inertia::render('RealEstate/Leases/Show', [
            'lease' => $baux->append(['status_label']),
        ]);
    }

    public function edit(Request $request, Lease $baux): Response
    {
        $company = $request->user()->currentCompany;
        abort_if($baux->property->company_id !== $company->id, 403);

        $customers = Customer::where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('RealEstate/Leases/Edit', [
            'lease'     => $baux->load(['property', 'tenant']),
            'customers' => $customers,
        ]);
    }

    public function update(Request $request, Lease $baux)
    {
        $company = $request->user()->currentCompany;
        abort_if($baux->property->company_id !== $company->id, 403);

        $data = $request->validate([
            'start_date'          => 'required|date',
            'end_date'            => 'nullable|date|after:start_date',
            'is_open_ended'       => 'boolean',
            'monthly_rent'        => 'required|numeric|min:0',
            'deposit_amount'      => 'nullable|numeric|min:0',
            'payment_day'         => 'required|integer|min:1|max:28',
            'renewal_notice_days' => 'nullable|integer|min:0',
            'notes'               => 'nullable|string',
        ]);

        $baux->update($data);

        return redirect()->route('real-estate.leases.show', $baux)
            ->with('success', 'Bail mis à jour.');
    }

    public function destroy(Request $request, Lease $baux)
    {
        $company = $request->user()->currentCompany;
        abort_if($baux->property->company_id !== $company->id, 403);

        $baux->delete();

        return redirect()->route('real-estate.leases.index')
            ->with('success', 'Bail supprimé.');
    }

    public function terminate(Request $request, Lease $baux)
    {
        $company = $request->user()->currentCompany;
        abort_if($baux->property->company_id !== $company->id, 403);

        $request->validate([
            'terminate_reason' => 'required|string|max:500',
        ]);

        $baux->update([
            'status'           => 'terminated',
            'terminated_at'    => today(),
            'terminate_reason' => $request->terminate_reason,
        ]);

        $baux->property->update(['status' => 'available']);

        return redirect()->route('real-estate.leases.show', $baux)
            ->with('success', 'Bail résilié. Le bien est de nouveau disponible.');
    }

    public function generateRent(Request $request, Lease $baux)
    {
        $company = $request->user()->currentCompany;
        abort_if($baux->property->company_id !== $company->id, 403);

        $request->validate([
            'period_month' => 'required|date',
        ]);

        $period = Carbon::parse($request->period_month)->startOfMonth();

        $document = $baux->property->generateRentInvoice($baux, $period);

        RentPayment::create([
            'lease_id'     => $baux->id,
            'document_id'  => $document->id,
            'period_month' => $period->toDateString(),
            'amount'       => $baux->monthly_rent,
            'status'       => 'pending',
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Quittance générée avec succès.');
    }
}
