<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MakeController extends Controller
{
    public function __construct(private DocumentService $documents) {}

    /** POST /api/make/customers */
    public function createCustomer(Request $request): JsonResponse
    {
        /** @var \App\Models\IncomingWebhook $webhook */
        $webhook = $request->incomingWebhook;

        $data = $request->validate([
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

        $customer = Customer::create([
            ...$data,
            'company_id' => $webhook->company_id,
        ]);

        return response()->json([
            'id' => (string) $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'created_at' => $customer->created_at->toIso8601String(),
        ], 201);
    }

    /** POST /api/make/documents */
    public function createDocument(Request $request): JsonResponse
    {
        /** @var \App\Models\IncomingWebhook $webhook */
        $webhook = $request->incomingWebhook;
        $company = $webhook->company;

        $owner = $company->users()->wherePivot('role', 'owner')->first()
            ?? $company->users()->first();

        $data = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(Document::TYPES)),
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where('company_id', $webhook->company_id),
            ],
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date',
            'currency' => 'required|string|size:3',
            'notes' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $lines = $data['lines'];
        unset($data['lines']);

        $document = $this->documents->create($company, $owner, $data, $lines);

        return response()->json([
            'id' => 'doc_' . $document->id,
            'type' => $document->type,
            'number' => $document->number,
            'total' => (float) $document->total,
            'status' => $document->status,
            'created_at' => $document->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * GET /api/make/triggers/invoices
     * Polling Make : factures créées depuis ?since=.
     */
    public function triggerInvoices(Request $request): JsonResponse
    {
        /** @var \App\Models\IncomingWebhook $webhook */
        $webhook = $request->incomingWebhook;

        $since = $request->query('since')
            ? \Carbon\Carbon::parse($request->query('since'))
            : now()->subHour();

        $documents = Document::where('company_id', $webhook->company_id)
            ->where('type', 'invoice')
            ->where('created_at', '>=', $since)
            ->orderByDesc('id')
            ->limit(100)
            ->get();

        return response()->json(
            $documents->map(fn ($doc) => [
                'id' => 'doc_' . $doc->id,
                'type' => $doc->type,
                'number' => $doc->number,
                'total' => (float) $doc->total,
                'status' => $doc->status,
                'created_at' => $doc->created_at->toIso8601String(),
            ])->values()->toArray()
        );
    }
}
