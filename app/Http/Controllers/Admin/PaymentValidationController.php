<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Order;
use App\Models\PaymentAuditLog;
use App\Models\PaymentProof;
use App\Models\PaymentTransaction;
use App\Models\Plan;
use App\Services\LicenseService;
use App\Services\PaymentNotificationService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Console Superadmin — file de validation des paiements manuels (script §9 & §16).
 */
class PaymentValidationController extends Controller
{
    public function __construct(private PaymentService $payments)
    {
    }

    public function index(Request $request): Response
    {
        $pending = PaymentTransaction::with([
            'order.plan:id,code,name',
            'user:id,name,email,country,created_at',
            'proofs:id,transaction_id,original_filename,mime_type,file_size,verification_status',
        ])
            ->when(
                $request->status,
                fn ($q, $s) => $q->where('status', $s),
                fn ($q) => $q->whereIn('status', ['under_review', 'pending'])
            )
            ->orderBy('created_at')
            ->paginate(15)
            ->through(fn (PaymentTransaction $t) => [
                'id' => $t->id,
                'internal_reference' => $t->internal_reference,
                'provider' => $t->payment_provider,
                'provider_reference' => $t->provider_reference,
                'amount_expected' => (float) $t->amount_expected,
                'amount_declared' => $t->amount_declared !== null ? (float) $t->amount_declared : null,
                'currency' => $t->currency,
                'status' => $t->status,
                'sender_name' => $t->sender_name,
                'sender_number' => $t->sender_number,
                'created_at' => $t->created_at->format('d/m/Y H:i'),
                'risk_level' => $this->payments->riskLevel($t),
                'user' => $t->user?->only(['name', 'email', 'country']),
                'order' => [
                    'order_number' => $t->order?->order_number,
                    'plan' => $t->order?->plan?->name,
                    'duration_months' => $t->order?->duration_months,
                ],
                'proofs' => $t->proofs,
            ]);

        $stats = [
            'to_review' => PaymentTransaction::whereIn('status', ['under_review', 'pending'])->count(),
            'validated_today' => PaymentTransaction::where('status', 'manually_validated')
                ->whereDate('confirmed_at', today())->count(),
            'active_licenses' => License::whereIn('status', ['active', 'trial', 'provisional', 'grace_period'])->count(),
            'revenue_month' => (float) Order::where('status', 'paid')
                ->where('paid_at', '>=', now()->startOfMonth())->sum('total_amount'),
        ];

        return Inertia::render('Admin/Payments', [
            'transactions' => $pending,
            'stats' => $stats,
            'filters' => $request->only('status'),
        ]);
    }

    public function validatePayment(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'amount_received' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500',
        ]);

        $this->payments->validateManualPayment(
            $transaction,
            $request->user(),
            (float) $data['amount_received'],
            $data['note'] ?? null,
        );

        return back()->with('success', 'Paiement validé — licence activée pour '.$transaction->user?->name.'.');
    }

    public function reject(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500', // motif obligatoire (script §9)
        ]);

        $this->payments->rejectManualPayment($transaction, $request->user(), $data['reason']);

        return back()->with('success', 'Paiement rejeté.');
    }

    /**
     * File de validation enrichie (PaymentQueue.vue) avec drawer, filtres avancés, score fraude.
     */
    public function queue(Request $request): Response
    {
        $query = PaymentTransaction::with([
            'order.plan:id,code,name',
            'user:id,name,email,country,created_at',
            'proofs:id,transaction_id,original_filename,mime_type,file_size,verification_status,file_path',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['under_review', 'pending', 'proof_submitted']);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('internal_reference', 'like', "%{$search}%")
                ->orWhere('provider_reference', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")));
        }

        if ($request->filled('provider')) {
            $query->where('payment_provider', $request->provider);
        }
        if ($request->filled('country')) {
            $query->whereHas('user', fn ($u) => $u->where('country', $request->country));
        }
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }
        if ($request->filled('plan')) {
            $query->whereHas('order.plan', fn ($p) => $p->where('code', $request->plan));
        }
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to.' 23:59:59');
        }

        $transactions = $query->orderBy('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (PaymentTransaction $t) => [
                'id' => $t->id,
                'internal_reference' => $t->internal_reference,
                'provider' => $t->payment_provider,
                'provider_reference' => $t->provider_reference,
                'amount_expected' => (float) $t->amount_expected,
                'amount_declared' => $t->amount_declared !== null ? (float) $t->amount_declared : null,
                'currency' => $t->currency,
                'status' => $t->status,
                'sender_name' => $t->sender_name,
                'sender_number' => $t->sender_number,
                'created_at' => $t->created_at->format('d/m/Y H:i'),
                'risk_level' => $this->payments->riskLevel($t),
                'user' => $t->user?->only(['id', 'name', 'email', 'country']),
                'order' => [
                    'id' => $t->order?->id,
                    'order_number' => $t->order?->order_number,
                    'plan' => $t->order?->plan?->name,
                    'plan_code' => $t->order?->plan?->code,
                    'duration_months' => $t->order?->duration_months,
                    'total_amount' => (float) ($t->order?->total_amount ?? 0),
                    'status' => $t->order?->status,
                ],
                'proofs' => $t->proofs->map(fn ($p) => [
                    'id' => $p->id,
                    'original_filename' => $p->original_filename,
                    'mime_type' => $p->mime_type,
                    'file_size' => $p->file_size,
                    'verification_status' => $p->verification_status,
                    'url' => route('admin.proofs.show', $p->id),
                ]),
            ]);

        $stats = [
            'pending' => PaymentTransaction::where('status', 'pending')->count(),
            'under_review' => PaymentTransaction::whereIn('status', ['under_review', 'proof_submitted'])->count(),
            'provisional' => License::where('status', 'provisional')->count(),
        ];

        $plans = Plan::orderBy('sort_order')->get(['id', 'code', 'name']);

        return Inertia::render('Admin/PaymentQueue', [
            'transactions' => $transactions,
            'stats' => $stats,
            'plans' => $plans,
            'filters' => $request->only(['search', 'status', 'provider', 'country', 'currency', 'plan', 'date_from', 'date_to']),
        ]);
    }

    /** Demande un complément d'information au client. */
    public function requestComplement(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'complement_note' => 'required|string|max:500',
        ]);

        $transaction->update(['status' => 'missing_info']);
        $transaction->order?->update(['status' => 'missing_info']);

        PaymentAuditLog::record(
            'complement_requested',
            'transaction',
            $transaction->id,
            null,
            ['note' => $data['complement_note']],
            $data['complement_note'],
            $request->user()->id,
        );

        if ($transaction->order) {
            app(PaymentNotificationService::class)->sendComplementRequested(
                $transaction->order,
                $data['complement_note'],
            );
        }

        return back()->with('success', 'Demande de complément envoyée au client.');
    }

    /** Active une licence à titre provisoire en attendant confirmation du paiement. */
    public function activateProvisionally(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'motif' => 'required|string|max:500',
            'days' => 'required|integer|in:7,14,30',
        ]);

        $transaction = $order->transactions()->latest()->firstOrFail();

        app(LicenseService::class)->activateProvisionally(
            $order,
            $transaction,
            $request->user(),
            $data['motif'],
            (int) $data['days'],
        );

        return back()->with('success', "Licence provisoire activée pour {$order->user?->name} ({$data['days']} jours).");
    }

    /** Marque une transaction comme suspecte pour investigation. */
    public function markSuspect(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $transaction->update(['status' => 'suspected_fraud']);

        PaymentAuditLog::record(
            'payment_marked_suspect',
            'transaction',
            $transaction->id,
            null,
            ['reason' => $data['reason']],
            $data['reason'],
            $request->user()->id,
        );

        return back()->with('success', 'Transaction marquée suspecte.');
    }

    /** Consultation sécurisée d'une preuve (stockage privé — script §8). */
    public function proof(Request $request, PaymentProof $proof): StreamedResponse
    {
        $disk = Storage::disk(config('factpro.proofs.disk'));

        abort_unless($disk->exists($proof->file_path), 404);

        \App\Models\PaymentAuditLog::record('proof_viewed', 'proof', $proof->id, adminId: $request->user()->id);

        return $disk->response($proof->file_path, $proof->original_filename, [
            'Content-Type' => $proof->mime_type,
            'Content-Disposition' => 'inline; filename="'.$proof->original_filename.'"',
        ]);
    }
}
