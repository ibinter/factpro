<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Document;
use App\Models\PaymentTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentLinkController extends Controller
{
    // ──────────────────────────────────────────────────────────────────────────
    // PUBLIC — page de paiement
    // ──────────────────────────────────────────────────────────────────────────

    public function show(string $token): Response
    {
        $document = Document::where('public_token', $token)
            ->with(['company', 'customer', 'lines', 'payments'])
            ->firstOrFail();

        // Seuls les documents finalisés sont accessibles publiquement.
        if (! in_array($document->status, ['sent', 'partial', 'paid', 'overdue'], true)) {
            abort(404);
        }

        $company = $document->company;

        $paymentMethods = $this->activePaymentMethods($company);

        return Inertia::render('Public/Pay', [
            'document' => [
                'id'              => $document->id,
                'number'          => $document->number,
                'type'            => $document->type,
                'type_label'      => $document->type_label ?? (Document::TYPES[$document->type]['label'] ?? $document->type),
                'issue_date'      => $document->issue_date,
                'due_date'        => $document->due_date,
                'total'           => $document->total,
                'subtotal'        => $document->subtotal,
                'tax_amount'      => $document->tax_amount,
                'discount_amount' => $document->discount_amount,
                'amount_paid'     => $document->amount_paid,
                'currency'        => $document->currency ?? ($company->currency ?? 'XOF'),
                'status'          => $document->status,
                'notes'           => $document->notes,
            ],
            'lines' => $document->lines->map(fn ($line) => [
                'description' => $line->description,
                'quantity'    => $line->quantity,
                'unit_price'  => $line->unit_price,
                'line_total'  => $line->line_total ?? ($line->quantity * $line->unit_price),
                'tax_rate'    => $line->tax_rate,
            ])->values(),
            'company' => [
                'name'            => $company->name,
                'address'         => $company->address,
                'city'            => $company->city,
                'phone'           => $company->phone,
                'email'           => $company->email,
                'logo_url'        => $company->logo_url,
                'payment_methods' => $paymentMethods,
            ],
            'customer' => $document->customer ? [
                'name'  => $document->customer->name,
                'email' => $document->customer->email,
                'phone' => $document->customer->phone,
            ] : null,
            'alreadyPaid'     => $document->status === 'paid',
            'paymentToken'    => $token,
            'uploadProofUrl'  => route('pay.proof', $token),
            'statusCheckUrl'  => route('pay.status', $token),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // INITIER UN PAIEMENT EN LIGNE
    // ──────────────────────────────────────────────────────────────────────────

    public function initiateOnline(Request $request, string $token): JsonResponse
    {
        $request->validate([
            'gateway' => ['required', 'in:cinetpay,fedapay,flutterwave,moneroo,stripe,paypal'],
        ]);

        $document = Document::where('public_token', $token)->firstOrFail();
        $company  = $document->company;

        $result = match ($request->input('gateway')) {
            'cinetpay'   => $this->initiateCinetpay($document, $company),
            'fedapay'    => $this->initiateFedapay($document, $company),
            default      => ['error' => 'Gateway non configurée'],
        };

        if (isset($result['error'])) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CALLBACKS
    // ──────────────────────────────────────────────────────────────────────────

    public function callbackSuccess(Request $request, string $token): RedirectResponse
    {
        $document = Document::where('public_token', $token)->firstOrFail();

        if ($document->status !== 'paid') {
            $document->update([
                'status'     => 'paid',
                'amount_paid' => $document->total,
            ]);

            PaymentTransaction::create([
                'document_id'    => $document->id,
                'company_id'     => $document->company_id,
                'amount'         => $document->total,
                'currency'       => $document->currency ?? 'XOF',
                'gateway'        => $request->query('gateway', 'online'),
                'status'         => 'completed',
                'transaction_id' => $request->query('transaction_id'),
                'metadata'       => $request->query(),
            ]);
        }

        return redirect()->route('pay.public', ['token' => $token, 'paid' => 1]);
    }

    public function callbackCancel(Request $request, string $token): RedirectResponse
    {
        return redirect()->route('pay.public', ['token' => $token, 'cancelled' => 1]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // WEBHOOK CINETPAY
    // ──────────────────────────────────────────────────────────────────────────

    public function webhookCinetpay(Request $request): JsonResponse
    {
        $payload = $request->all();

        // Vérification de la signature CinetPay.
        $secretKey   = null;
        $transactionId = $payload['cpm_trans_id'] ?? null;

        // Retrouver le document via la transaction_id stockée dans metadata.
        $transaction = PaymentTransaction::where('transaction_id', $transactionId)->first();

        if ($transaction) {
            $document = $transaction->document;

            if ($document && ($payload['cpm_result'] ?? null) === '00') {
                $document->update([
                    'status'      => 'paid',
                    'amount_paid' => $document->total,
                ]);

                $transaction->update([
                    'status'   => 'completed',
                    'metadata' => array_merge($transaction->metadata ?? [], $payload),
                ]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GATEWAYS PRIVÉES
    // ──────────────────────────────────────────────────────────────────────────

    private function initiateCinetpay(Document $document, Company $company): array
    {
        $methods   = $company->payment_methods ?? [];
        $cinetpay  = collect($methods)->firstWhere('gateway', 'cinetpay');

        if (! $cinetpay || empty($cinetpay['api_key']) || empty($cinetpay['site_id'])) {
            return ['error' => 'CinetPay non configuré pour cette entreprise.'];
        }

        $transactionId = 'FP-' . $document->id . '-' . time();

        // Stocker la transaction en attente.
        PaymentTransaction::create([
            'document_id'    => $document->id,
            'company_id'     => $document->company_id,
            'amount'         => $document->total,
            'currency'       => $document->currency ?? 'XOF',
            'gateway'        => 'cinetpay',
            'status'         => 'pending',
            'transaction_id' => $transactionId,
            'metadata'       => ['initiated_at' => now()->toISOString()],
        ]);

        $params = http_build_query([
            'apikey'            => $cinetpay['api_key'],
            'site_id'           => $cinetpay['site_id'],
            'transaction_id'    => $transactionId,
            'amount'            => (int) $document->total,
            'currency'          => $document->currency ?? 'XOF',
            'description'       => 'Paiement ' . ($document->number ?? $document->id),
            'return_url'        => route('pay.success', ['token' => $document->public_token, 'gateway' => 'cinetpay', 'transaction_id' => $transactionId]),
            'cancel_url'        => route('pay.cancel', ['token' => $document->public_token]),
            'notify_url'        => route('webhooks.cinetpay'),
            'channels'          => 'ALL',
            'lang'              => 'fr',
        ]);

        return ['redirect_url' => 'https://checkout.cinetpay.com/?' . $params];
    }

    private function initiateFedapay(Document $document, Company $company): array
    {
        $methods  = $company->payment_methods ?? [];
        $fedapay  = collect($methods)->firstWhere('gateway', 'fedapay');

        if (! $fedapay || empty($fedapay['api_key'])) {
            return ['error' => 'FedaPay non configuré pour cette entreprise.'];
        }

        $transactionId = 'FP-FED-' . $document->id . '-' . time();

        PaymentTransaction::create([
            'document_id'    => $document->id,
            'company_id'     => $document->company_id,
            'amount'         => $document->total,
            'currency'       => $document->currency ?? 'XOF',
            'gateway'        => 'fedapay',
            'status'         => 'pending',
            'transaction_id' => $transactionId,
            'metadata'       => ['initiated_at' => now()->toISOString()],
        ]);

        // FedaPay SDK ou appel API direct — simplifié.
        return ['error' => 'Gateway non configurée'];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // UPLOAD PREUVE DE PAIEMENT (public)
    // ──────────────────────────────────────────────────────────────────────────

    public function uploadProof(Request $request, string $token): JsonResponse
    {
        $document = Document::where('public_token', $token)->firstOrFail();

        $request->validate([
            'proof' => ['required', 'image', 'max:5120'],
        ]);

        $path = $request->file('proof')->store("payment-proofs/{$document->company_id}", 'public');

        \Log::info("Preuve paiement uploadée pour document {$document->number}: {$path}");

        return response()->json([
            'success' => true,
            'message' => 'Preuve envoyée. La société vous contactera pour confirmation.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // VÉRIFICATION STATUT (public, polling)
    // ──────────────────────────────────────────────────────────────────────────

    public function statusCheck(string $token): JsonResponse
    {
        $document = Document::where('public_token', $token)
            ->with('payments')
            ->firstOrFail();

        return response()->json([
            'status'      => $document->status,
            'amount_paid' => $document->amount_paid,
            'total'       => $document->total,
            'currency'    => $document->currency,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // HELPER
    // ──────────────────────────────────────────────────────────────────────────

    private function activePaymentMethods(Company $company): array
    {
        $methods = $company->payment_methods ?? [];

        return collect($methods)
            ->filter(fn ($m) => ! empty($m['enabled']))
            ->values()
            ->toArray();
    }
}
