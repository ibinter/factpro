<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $query = Property::where('company_id', $company->id)
            ->with(['activeLease.tenant'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        $properties = $query->paginate(20)->withQueryString();

        return Inertia::render('RealEstate/Properties/Index', [
            'properties' => $properties,
            'filters'    => $request->only('status', 'city'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('RealEstate/Properties/Form', [
            'property' => null,
        ]);
    }

    public function store(Request $request)
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'reference'      => 'nullable|string|max:100',
            'type'           => 'required|in:apartment,house,villa,commercial,office,warehouse,land,parking',
            'address'        => 'required|string|max:255',
            'city'           => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:100',
            'area_sqm'       => 'nullable|numeric|min:0',
            'bedrooms'       => 'nullable|integer|min:0',
            'bathrooms'      => 'nullable|integer|min:0',
            'floor'          => 'nullable|integer',
            'total_floors'   => 'nullable|integer|min:0',
            'monthly_rent'   => 'required|numeric|min:0',
            'currency'       => 'required|string|size:3',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date'  => 'nullable|date',
            'tax_rate'       => 'nullable|numeric|min:0|max:100',
            'status'         => 'required|in:available,rented,maintenance,for_sale',
            'description'    => 'nullable|string',
            'amenities'      => 'nullable|array',
        ]);

        $data['company_id'] = $company->id;

        Property::create($data);

        return redirect()->route('real-estate.properties.index')
            ->with('success', 'Bien immobilier créé avec succès.');
    }

    public function show(Request $request, Property $propriete): Response
    {
        $company = $request->user()->currentCompany;
        abort_if($propriete->company_id !== $company->id, 403);

        $propriete->load([
            'leases.tenant',
            'leases.rentPayments',
            'activeLease.tenant',
        ]);

        $rentPayments = $propriete->rentPayments()
            ->with('document')
            ->orderByDesc('period_month')
            ->limit(12)
            ->get();

        return Inertia::render('RealEstate/Properties/Show', [
            'property'     => $propriete->append(['type_label', 'status_label', 'status_color']),
            'rentPayments' => $rentPayments,
        ]);
    }

    public function edit(Request $request, Property $propriete): Response
    {
        $company = $request->user()->currentCompany;
        abort_if($propriete->company_id !== $company->id, 403);

        return Inertia::render('RealEstate/Properties/Form', [
            'property' => $propriete,
        ]);
    }

    public function update(Request $request, Property $propriete)
    {
        $company = $request->user()->currentCompany;
        abort_if($propriete->company_id !== $company->id, 403);

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'reference'      => 'nullable|string|max:100',
            'type'           => 'required|in:apartment,house,villa,commercial,office,warehouse,land,parking',
            'address'        => 'required|string|max:255',
            'city'           => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:100',
            'area_sqm'       => 'nullable|numeric|min:0',
            'bedrooms'       => 'nullable|integer|min:0',
            'bathrooms'      => 'nullable|integer|min:0',
            'floor'          => 'nullable|integer',
            'total_floors'   => 'nullable|integer|min:0',
            'monthly_rent'   => 'required|numeric|min:0',
            'currency'       => 'required|string|size:3',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date'  => 'nullable|date',
            'tax_rate'       => 'nullable|numeric|min:0|max:100',
            'status'         => 'required|in:available,rented,maintenance,for_sale',
            'description'    => 'nullable|string',
            'amenities'      => 'nullable|array',
        ]);

        $propriete->update($data);

        return redirect()->route('real-estate.properties.show', $propriete)
            ->with('success', 'Bien immobilier mis à jour.');
    }

    public function destroy(Request $request, Property $propriete)
    {
        $company = $request->user()->currentCompany;
        abort_if($propriete->company_id !== $company->id, 403);

        $propriete->delete();

        return redirect()->route('real-estate.properties.index')
            ->with('success', 'Bien immobilier supprimé.');
    }
}
