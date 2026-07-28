<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CustomerSubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $query = CustomerSubscription::where('company_id', $company->id)
            ->with('customer:id,name,email')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->paginate(20)->withQueryString();

        return Inertia::render('Subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'filters'       => $request->only('status'),
        ]);
    }

    public function create(Request $request): Response
    {
        $company   = $request->user()->currentCompany;
        $customers = Customer::where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'currency']);

        return Inertia::render('Subscriptions/Create', [
            'customers' => $customers,
            'currency'  => $company->currency,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'customer_id'            => 'required|exists:customers,id',
            'name'                   => 'required|string|max:255',
            'description'            => 'nullable|string',
            'frequency'              => 'required|in:weekly,monthly,quarterly,biannual,annual',
            'amount'                 => 'required|numeric|min:0',
            'currency'               => 'required|string|size:3',
            'tax_rate'               => 'nullable|numeric|min:0|max:100',
            'start_date'             => 'required|date',
            'end_date'               => 'nullable|date|after_or_equal:start_date',
            'payment_terms'          => 'nullable|integer|min:0',
            'notes'                  => 'nullable|string',
            'auto_generate_invoice'  => 'nullable|boolean',
        ]);

        $data['company_id']         = $company->id;
        $data['next_billing_date']  = Carbon::parse($data['start_date']);
        $data['tax_rate']           = $data['tax_rate'] ?? 0;
        $data['payment_terms']      = $data['payment_terms'] ?? 30;
        $data['auto_generate_invoice'] = $data['auto_generate_invoice'] ?? true;

        CustomerSubscription::create($data);

        return redirect()->route('subscriptions.index')
            ->with('success', 'Abonnement créé avec succès.');
    }

    public function show(CustomerSubscription $customerSubscription): Response
    {
        $customerSubscription->load([
            'customer',
            'invoices' => fn ($q) => $q->latest()->with('document:id,number,status,type'),
        ]);

        return Inertia::render('Subscriptions/Show', [
            'subscription' => $customerSubscription,
        ]);
    }

    public function pause(CustomerSubscription $customerSubscription): RedirectResponse
    {
        $customerSubscription->update(['status' => 'paused']);

        return back()->with('success', 'Abonnement mis en pause.');
    }

    public function resume(CustomerSubscription $customerSubscription): RedirectResponse
    {
        $customerSubscription->update([
            'status'             => 'active',
            'next_billing_date'  => $customerSubscription->calculateNextBillingDate(),
        ]);

        return back()->with('success', 'Abonnement repris.');
    }

    public function cancel(Request $request, CustomerSubscription $customerSubscription): RedirectResponse
    {
        $data = $request->validate([
            'cancel_reason' => 'nullable|string',
        ]);

        $customerSubscription->update([
            'status'        => 'cancelled',
            'cancelled_at'  => now(),
            'cancel_reason' => $data['cancel_reason'] ?? null,
        ]);

        return back()->with('success', 'Abonnement annulé.');
    }

    public function generateNow(CustomerSubscription $customerSubscription): RedirectResponse
    {
        $document = $customerSubscription->generateInvoice();

        SubscriptionInvoice::create([
            'subscription_id' => $customerSubscription->id,
            'document_id'     => $document->id,
            'billing_date'    => today(),
            'amount'          => $customerSubscription->amount,
            'status'          => 'generated',
        ]);

        $customerSubscription->update([
            'last_billed_at'    => now(),
            'next_billing_date' => $customerSubscription->calculateNextBillingDate(),
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Facture ' . $document->number . ' générée.');
    }
}
