<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerApiController extends Controller
{
    /** GET /api/v1/customers — liste paginée (?search=, ?per_page=). */
    public function index(Request $request): AnonymousResourceCollection
    {
        $customers = Customer::where('company_id', $request->user()->current_company_id)
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('phone', 'like', "%{$s}%")))
            ->orderBy('name')
            ->paginate(min((int) $request->integer('per_page', 15) ?: 15, 100))
            ->withQueryString();

        return CustomerResource::collection($customers);
    }

    /** POST /api/v1/customers */
    public function store(Request $request, LicenseService $licenses): JsonResponse
    {
        $companyId = $request->user()->current_company_id;

        $count = Customer::where('company_id', $companyId)->count();
        if ($licenses->limitReached($request->user(), 'customers', $count)) {
            return response()->json([
                'message' => 'Limite de clients atteinte pour votre forfait.',
            ], 422);
        }

        $customer = Customer::create([
            ...$this->validateData($request),
            'company_id' => $companyId,
        ]);

        return (new CustomerResource($customer))->response()->setStatusCode(201);
    }

    /** GET /api/v1/customers/{id} */
    public function show(Request $request, int $id): CustomerResource
    {
        return new CustomerResource($this->find($request, $id));
    }

    /** PUT /api/v1/customers/{id} */
    public function update(Request $request, int $id): CustomerResource
    {
        $customer = $this->find($request, $id);
        $customer->update($this->validateData($request));

        return new CustomerResource($customer->fresh());
    }

    /** DELETE /api/v1/customers/{id} */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->find($request, $id)->delete();

        return response()->json(['message' => 'Client supprimé.']);
    }

    /** Scope société courante — 404 hors périmètre. */
    private function find(Request $request, int $id): Customer
    {
        return Customer::where('company_id', $request->user()->current_company_id)
            ->findOrFail($id);
    }

    /** Mêmes règles que le contrôleur web (CustomerController). */
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
