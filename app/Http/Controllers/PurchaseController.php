<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Achats fournisseurs (cahier IBIG §10.1 « Journal des achats ») — répertoire
 * fournisseurs et saisie des factures d'achat avec justificatif privé, suivi du
 * paiement et alimentation de la comptabilité. Réservé BUSINESS/ENTERPRISE (§22.1).
 */
class PurchaseController extends Controller
{
    /** Plans autorisés à utiliser le module achats. */
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    /** Tolérance de cohérence HT + TVA = TTC. */
    private const AMOUNT_TOLERANCE = 0.05;

    public function __construct(private LicenseService $licenses)
    {
    }

    /** Le forfait courant donne-t-il accès aux achats ? */
    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    /** Interdit toute mutation hors BUSINESS/ENTERPRISE. */
    private function guardMutation(Request $request): void
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Le module achats est réservé aux forfaits BUSINESS et ENTERPRISE.'
        );
    }

    /** Garde-fou multi-sociétés : le fournisseur appartient à la société courante. */
    private function ensureSupplier(Request $request, Supplier $supplier): void
    {
        abort_if(
            (int) $supplier->company_id !== (int) $request->user()->current_company_id,
            404
        );
    }

    /** Garde-fou multi-sociétés : la facture appartient à la société courante. */
    private function ensureInvoice(Request $request, SupplierInvoice $invoice): void
    {
        abort_if(
            (int) $invoice->company_id !== (int) $request->user()->current_company_id,
            404
        );
    }

    /** Dashboard achats (ou upsell si forfait insuffisant). */
    public function index(Request $request): Response
    {
        $hasAccess = $this->hasAccess($request);
        $user = $request->user();
        $companyId = $user->current_company_id;

        if (! $hasAccess || ! $companyId) {
            return Inertia::render('Purchases/Index', [
                'hasAccess' => false,
                'suppliers' => [],
                'invoices' => null,
                'stats' => null,
                'categories' => SupplierInvoice::CATEGORIES,
                'filters' => (object) [],
                'currency' => $user->currentCompany?->currency ?? 'XOF',
            ]);
        }

        $suppliers = Supplier::where('company_id', $companyId)
            ->withCount('invoices')
            ->withSum('invoices as invoices_ttc_sum', 'amount_ttc')
            ->orderBy('name')
            ->get()
            ->map(fn (Supplier $s) => [
                'id' => $s->id,
                'name' => $s->name,
                'contact_name' => $s->contact_name,
                'email' => $s->email,
                'phone' => $s->phone,
                'address' => $s->address,
                'city' => $s->city,
                'country' => $s->country,
                'tax_id' => $s->tax_id,
                'notes' => $s->notes,
                'invoices_count' => (int) $s->invoices_count,
                'invoices_ttc_sum' => (float) ($s->invoices_ttc_sum ?? 0),
            ]);

        $invoices = SupplierInvoice::where('company_id', $companyId)
            ->with('supplier:id,name')
            ->when($request->supplier, fn ($q, $s) => $q->where('supplier_id', $s))
            ->when($request->status, fn ($q, $st) => $q->where('status', $st))
            ->when($request->month, function ($q, $m) {
                [$year, $month] = array_pad(explode('-', (string) $m), 2, null);
                if (is_numeric($year) && is_numeric($month)) {
                    $q->whereYear('invoice_date', (int) $year)
                        ->whereMonth('invoice_date', (int) $month);
                }
            })
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (SupplierInvoice $i) => [
                'id' => $i->id,
                'supplier_id' => $i->supplier_id,
                'supplier' => $i->supplier?->only(['id', 'name']),
                'number' => $i->number,
                'reference' => $i->reference,
                'invoice_date' => $i->invoice_date?->toDateString(),
                'due_date' => $i->due_date?->toDateString(),
                'amount_ht' => (float) $i->amount_ht,
                'vat_amount' => (float) $i->vat_amount,
                'amount_ttc' => (float) $i->amount_ttc,
                'amount_paid' => (float) $i->amount_paid,
                'balance_due' => $i->balance_due,
                'currency' => $i->currency,
                'category' => $i->category,
                'status' => $i->status,
                'paid_at' => $i->paid_at?->toDateString(),
                'notes' => $i->notes,
                'has_receipt' => $i->receipt_path !== null,
                'receipt_original_name' => $i->receipt_original_name,
            ]);

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $monthScope = fn () => SupplierInvoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', [$monthStart, $monthEnd]);

        $stats = [
            'purchases_month_ttc' => (float) $monthScope()->sum('amount_ttc'),
            'vat_deductible_month' => (float) $monthScope()->sum('vat_amount'),
            'unpaid_total' => (float) SupplierInvoice::where('company_id', $companyId)
                ->whereIn('status', ['unpaid', 'partial'])
                ->sum(DB::raw('amount_ttc - amount_paid')),
        ];

        return Inertia::render('Purchases/Index', [
            'hasAccess' => true,
            'suppliers' => $suppliers,
            'invoices' => $invoices,
            'stats' => $stats,
            'categories' => SupplierInvoice::CATEGORIES,
            'filters' => $request->only('supplier', 'status', 'month'),
            'currency' => $user->currentCompany?->currency ?? 'XOF',
        ]);
    }

    /** Crée un fournisseur. */
    public function storeSupplier(Request $request): RedirectResponse
    {
        $this->guardMutation($request);

        $data = $this->validateSupplier($request);

        Supplier::create([
            'company_id' => $request->user()->current_company_id,
            ...$data,
        ]);

        return back()->with('success', 'Fournisseur ajouté.');
    }

    /** Met à jour un fournisseur. */
    public function updateSupplier(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->guardMutation($request);
        $this->ensureSupplier($request, $supplier);

        $supplier->update($this->validateSupplier($request));

        return back()->with('success', 'Fournisseur mis à jour.');
    }

    /** Supprime (soft delete) un fournisseur. */
    public function destroySupplier(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->guardMutation($request);
        $this->ensureSupplier($request, $supplier);

        $supplier->delete();

        return back()->with('success', 'Fournisseur supprimé.');
    }

    /** Enregistre une facture d'achat (justificatif privé optionnel). */
    public function storeInvoice(Request $request): RedirectResponse
    {
        $this->guardMutation($request);

        $data = $this->validateInvoice($request);
        $user = $request->user();
        $companyId = $user->current_company_id;

        $this->ensureSupplierBelongs($companyId, (int) $data['supplier_id']);
        $this->assertUniqueNumber($companyId, (int) $data['supplier_id'], $data['number']);
        $this->assertAmountsConsistent($data);

        $receipt = $this->storeReceipt($request);

        SupplierInvoice::create([
            'company_id' => $companyId,
            'created_by' => $user->id,
            'currency' => $user->currentCompany?->currency ?? 'XOF',
            'status' => 'unpaid',
            'amount_paid' => 0,
            ...$data,
            ...$receipt,
        ]);

        return back()->with('success', 'Facture d\'achat enregistrée.');
    }

    /** Met à jour une facture d'achat. */
    public function updateInvoice(Request $request, SupplierInvoice $invoice): RedirectResponse
    {
        $this->guardMutation($request);
        $this->ensureInvoice($request, $invoice);

        $data = $this->validateInvoice($request);
        $companyId = $request->user()->current_company_id;

        $this->ensureSupplierBelongs($companyId, (int) $data['supplier_id']);
        $this->assertUniqueNumber($companyId, (int) $data['supplier_id'], $data['number'], $invoice->id);
        $this->assertAmountsConsistent($data);

        // Un nouveau justificatif remplace l'ancien (supprimé du disque privé).
        $receipt = [];
        if ($request->hasFile('receipt')) {
            if ($invoice->receipt_path) {
                Storage::disk(config('factpro.proofs.disk'))->delete($invoice->receipt_path);
            }
            $receipt = $this->storeReceipt($request);
        }

        $invoice->update([...$data, ...$receipt]);

        // Le statut de paiement se recalcule si le TTC a changé.
        $this->recomputePaymentStatus($invoice->fresh());

        return back()->with('success', 'Facture d\'achat mise à jour.');
    }

    /** Supprime (soft delete) une facture d'achat. */
    public function destroyInvoice(Request $request, SupplierInvoice $invoice): RedirectResponse
    {
        $this->guardMutation($request);
        $this->ensureInvoice($request, $invoice);

        $invoice->delete();

        return back()->with('success', 'Facture d\'achat supprimée.');
    }

    /** Enregistre un règlement (partiel ou solde) sur une facture d'achat. */
    public function payment(Request $request, SupplierInvoice $invoice): RedirectResponse
    {
        $this->guardMutation($request);
        $this->ensureInvoice($request, $invoice);

        $balance = $invoice->balance_due;
        abort_if($balance <= 0, 422, 'Cette facture est déjà soldée.');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$balance],
            'paid_at' => ['sometimes', 'nullable', 'date'],
        ], [
            'amount.max' => 'Le règlement ne peut dépasser le reste à payer.',
        ]);

        $paid = round((float) $invoice->amount_paid + (float) $data['amount'], 2);
        $settled = $paid + self::AMOUNT_TOLERANCE >= (float) $invoice->amount_ttc;

        $invoice->update([
            'amount_paid' => $paid,
            'status' => $settled ? 'paid' : 'partial',
            'paid_at' => $settled ? ($data['paid_at'] ?? now()->toDateString()) : $invoice->paid_at,
        ]);

        return back()->with('success', $settled ? 'Facture soldée.' : 'Règlement partiel enregistré.');
    }

    /** Consultation sécurisée du justificatif (stockage privé, streamé inline). */
    public function receipt(Request $request, SupplierInvoice $invoice): StreamedResponse
    {
        $this->guardMutation($request);
        $this->ensureInvoice($request, $invoice);

        abort_if($invoice->receipt_path === null, 404);

        $disk = Storage::disk(config('factpro.proofs.disk'));
        abort_unless($disk->exists($invoice->receipt_path), 404);

        $filename = $invoice->receipt_original_name ?? basename($invoice->receipt_path);

        $headers = ['Content-Disposition' => 'inline; filename="'.$filename.'"'];
        if ($invoice->receipt_mime) {
            $headers['Content-Type'] = $invoice->receipt_mime;
        }

        return $disk->response($invoice->receipt_path, $filename, $headers);
    }

    /* --------------------------------------------------------------------- */

    /** Règles de validation d'un fournisseur. */
    private function validateSupplier(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'size:2'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    /** Règles de validation d'une facture d'achat (le fichier reçu est traité à part). */
    private function validateInvoice(Request $request): array
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'integer'],
            'number' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'amount_ht' => ['required', 'numeric', 'min:0'],
            'vat_amount' => ['nullable', 'numeric', 'min:0'],
            'amount_ttc' => ['required', 'numeric', 'min:0'],
            'category' => ['required', Rule::in(array_keys(SupplierInvoice::CATEGORIES))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ]);

        // Le justificatif est persisté séparément (disque privé), jamais en colonne.
        unset($validated['receipt']);
        $validated['vat_amount'] = $validated['vat_amount'] ?? 0;

        return $validated;
    }

    /** Le fournisseur ciblé appartient bien à la société courante. */
    private function ensureSupplierBelongs(int $companyId, int $supplierId): void
    {
        $exists = Supplier::where('company_id', $companyId)
            ->whereKey($supplierId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'supplier_id' => 'Fournisseur introuvable.',
            ]);
        }
    }

    /** Unicité du numéro de facture par fournisseur (au sein de la société). */
    private function assertUniqueNumber(int $companyId, int $supplierId, string $number, ?int $ignoreId = null): void
    {
        $exists = SupplierInvoice::where('company_id', $companyId)
            ->where('supplier_id', $supplierId)
            ->where('number', $number)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'number' => 'Ce numéro de facture existe déjà pour ce fournisseur.',
            ]);
        }
    }

    /** Cohérence comptable : amount_ttc ≈ amount_ht + vat_amount (±0,05). */
    private function assertAmountsConsistent(array $data): void
    {
        $ht = (float) $data['amount_ht'];
        $vat = (float) ($data['vat_amount'] ?? 0);
        $ttc = (float) $data['amount_ttc'];

        if (abs($ht + $vat - $ttc) > self::AMOUNT_TOLERANCE) {
            throw ValidationException::withMessages([
                'amount_ttc' => 'Montants incohérents : le TTC doit égaler HT + TVA.',
            ]);
        }
    }

    /** Recalcule le statut de paiement après modification du TTC. */
    private function recomputePaymentStatus(SupplierInvoice $invoice): void
    {
        $paid = (float) $invoice->amount_paid;
        $ttc = (float) $invoice->amount_ttc;

        if ($paid <= 0) {
            $status = 'unpaid';
        } elseif ($paid + self::AMOUNT_TOLERANCE >= $ttc) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        $invoice->update([
            'status' => $status,
            'paid_at' => $status === 'paid' ? ($invoice->paid_at ?? now()->toDateString()) : null,
        ]);
    }

    /** Stocke le justificatif sur le disque privé (pattern preuves de paiement). */
    private function storeReceipt(Request $request): array
    {
        $file = $request->file('receipt');
        if (! $file) {
            return [];
        }

        $storedName = Str::random(40).'.'.strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('private/purchase-receipts', $storedName, config('factpro.proofs.disk'));

        return [
            'receipt_path' => $path,
            'receipt_original_name' => $file->getClientOriginalName(),
            'receipt_mime' => (string) $file->getMimeType(),
        ];
    }
}
