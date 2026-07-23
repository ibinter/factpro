<?php

namespace App\Http\Controllers;

use App\Models\PaymentPlan;
use App\Models\PaymentPlanInstallment;
use App\Services\LicenseService;
use App\Services\PaymentPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Acomptes & plans de paiement échelonnés (cahier IBIG §12).
 * Réservé aux forfaits PRO et plus (§22.1) → 403 sinon.
 */
class PaymentPlanController extends Controller
{
    public function __construct(
        private LicenseService $licenses,
        private PaymentPlanService $plans,
    ) {
    }

    /** Le forfait courant donne-t-il accès aux plans de paiement ? (PRO et plus) */
    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && $license->isUsable()
            && $license->plan?->code !== 'starter';
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Les plans de paiement (acomptes) sont disponibles dès le forfait PRO.'
        );
    }

    private function authorizePlan(Request $request, PaymentPlan $plan): void
    {
        abort_unless($plan->company_id === $request->user()->current_company_id, 403);
    }

    /** Liste des plans de la société + statistiques. */
    public function index(Request $request): Response
    {
        $this->authorizeAccess($request);
        $company = $request->user()->currentCompany;

        $plans = PaymentPlan::where('company_id', $company->id)
            ->with(['customer:id,name', 'sourceDocument:id,type,number', 'installments'])
            ->orderByDesc('id')
            ->paginate(15)
            ->through(fn (PaymentPlan $plan) => $this->presentPlan($plan))
            ->withQueryString();

        $activePlans = PaymentPlan::where('company_id', $company->id)
            ->where('status', 'active')->with('installments')->get();

        $upcoming = PaymentPlanInstallment::whereHas('plan', fn ($q) => $q
            ->where('company_id', $company->id)->where('status', 'active'))
            ->where('status', 'pending')
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->count();

        $stats = [
            'active'      => $activePlans->count(),
            'outstanding' => round($activePlans->sum(fn (PaymentPlan $p) => $p->remaining), 2),
            'upcoming'    => $upcoming,
        ];

        return Inertia::render('PaymentPlans/Index', [
            'plans' => $plans,
            'stats' => $stats,
        ]);
    }

    /** Détail d'un plan + échéancier complet. */
    public function show(Request $request, PaymentPlan $plan): Response
    {
        $this->authorizeAccess($request);
        $this->authorizePlan($request, $plan);

        $plan->load([
            'customer:id,name,email',
            'sourceDocument:id,type,number',
            'installments.document:id,type,number,status,total,amount_paid',
        ]);

        return Inertia::render('PaymentPlans/Show', [
            'plan' => $this->presentPlan($plan, detailed: true),
        ]);
    }

    /** Génère la facture d'acompte / de solde d'une échéance. */
    public function invoiceInstallment(Request $request, PaymentPlanInstallment $installment): RedirectResponse
    {
        $this->authorizeAccess($request);
        $plan = $installment->plan;
        $this->authorizePlan($request, $plan);

        if ($plan->status === 'cancelled') {
            return back()->with('error', 'Ce plan est annulé.');
        }

        if ($installment->document_id !== null) {
            return back()->with('error', 'Une facture a déjà été générée pour cette échéance.');
        }

        $document = $this->plans->generateInstallmentInvoice($installment, $request->user());

        return redirect()->route('documents.show', $document)
            ->with('success', $document->type_label.' '.$document->number.' générée pour l\'échéance « '.$installment->label.' ».');
    }

    /** Annule un plan tant qu'aucune échéance n'est payée. */
    public function cancel(Request $request, PaymentPlan $plan): RedirectResponse
    {
        $this->authorizeAccess($request);
        $this->authorizePlan($request, $plan);

        if ($plan->installments()->where('status', 'paid')->exists()) {
            return back()->with('error', 'Impossible d\'annuler : une échéance est déjà payée.');
        }

        $plan->update(['status' => 'cancelled']);

        return back()->with('success', 'Plan de paiement annulé.');
    }

    /** Formate un plan pour le front (avec progression et échéances). */
    private function presentPlan(PaymentPlan $plan, bool $detailed = false): array
    {
        $installments = $plan->installments->map(fn (PaymentPlanInstallment $i) => [
            'id'          => $i->id,
            'label'       => $i->label,
            'due_date'    => $i->due_date?->toDateString(),
            'amount'      => (float) $i->amount,
            'percentage'  => $i->percentage !== null ? (float) $i->percentage : null,
            'status'      => $i->status,
            'document_id' => $i->document_id,
            'document'    => $detailed ? $i->document : null,
            'sort_order'  => $i->sort_order,
        ])->values();

        return [
            'id'              => $plan->id,
            'name'            => $plan->name,
            'status'          => $plan->status,
            'total_amount'    => (float) $plan->total_amount,
            'total_invoiced'  => $plan->total_invoiced,
            'remaining'       => $plan->remaining,
            'currency'        => $plan->currency,
            'notes'           => $plan->notes,
            'customer'        => $plan->customer,
            'source_document' => $plan->sourceDocument,
            'installments'    => $installments,
            'created_at'      => $plan->created_at?->toDateString(),
        ];
    }
}
