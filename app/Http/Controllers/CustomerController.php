<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $customers = Customer::where('company_id', $company->id)
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")))
            ->withCount('documents')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request, LicenseService $licenses): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        if ($licenses->limitReached($request->user(), 'customers', $company->customers()->count())) {
            return back()->with('error', 'Limite de clients atteinte pour votre forfait. Passez au forfait supérieur.');
        }

        $data = $this->validateData($request);
        Customer::create([...$data, 'company_id' => $company->id]);

        return back()->with('success', 'Client créé avec succès.');
    }

    /** Création rapide depuis un formulaire document (retourne JSON, sans rechargement). */
    public function quickStore(Request $request, LicenseService $licenses): JsonResponse
    {
        $company = $request->user()->currentCompany;

        if ($licenses->limitReached($request->user(), 'customers', $company->customers()->count())) {
            return response()->json(['error' => 'Limite de clients atteinte pour votre forfait.'], 422);
        }

        $data = $request->validate([
            'type'    => ['required', 'in:individual,company'],
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['nullable', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $customer = Customer::create([...$data, 'company_id' => $company->id]);

        return response()->json(['id' => $customer->id, 'name' => $customer->name, 'email' => $customer->email]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        abort_unless($customer->company_id === $request->user()->current_company_id, 403);

        $customer->update($this->validateData($request));

        return back()->with('success', 'Client mis à jour.');
    }

    public function destroy(Request $request, Customer $customer): RedirectResponse
    {
        abort_unless($customer->company_id === $request->user()->current_company_id, 403);

        $customer->delete();

        return back()->with('success', 'Client supprimé.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'type' => 'required|in:individual,company',
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|size:2',
            'tax_id' => 'nullable|string|max:50',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
        ]);
    }
}
