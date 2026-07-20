<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CommissionPayout;
use App\Models\SalesAgent;
use App\Services\CommissionService;
use App\Services\LicenseService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Commissions vendeurs (cahier IBIG §3 CMD « Calcul automatique des commissions
 * par commercial ou agent ») — répertoire de vendeurs, affectation des clients,
 * décomptes de commission sur les factures payées. Réservé BUSINESS/ENTERPRISE (§22.1).
 */
class CommissionController extends Controller
{
    /** Plans autorisés au module commissions. */
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    public function __construct(
        private LicenseService $licenses,
        private CommissionService $commissions,
    ) {
    }

    /** Le forfait courant donne-t-il accès aux commissions ? */
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
            'Le module commissions est réservé aux forfaits BUSINESS et ENTERPRISE.'
        );
    }

    /** Garde-fou multi-sociétés : le vendeur appartient à la société courante. */
    private function ensureAgent(Request $request, SalesAgent $agent): void
    {
        abort_if(
            (int) $agent->company_id !== (int) $request->user()->current_company_id,
            404
        );
    }

    /** Garde-fou multi-sociétés : le décompte appartient à la société courante. */
    private function ensurePayout(Request $request, CommissionPayout $payout): void
    {
        abort_if(
            (int) $payout->company_id !== (int) $request->user()->current_company_id,
            404
        );
    }

    /** Bornes de la période demandée (?from&to), défaut = mois courant. */
    private function period(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfMonth();

        // Sécurité : période inversée → on remet dans l'ordre.
        if ($to->lt($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /** Page commissions (ou upsell si forfait insuffisant). */
    public function index(Request $request): Response
    {
        $hasAccess = $this->hasAccess($request);
        $user = $request->user();
        $companyId = $user->current_company_id;

        [$from, $to] = $this->period($request);

        if (! $hasAccess || ! $companyId) {
            return Inertia::render('Commissions/Index', [
                'hasAccess' => false,
                'agents' => [],
                'preview' => null,
                'customers' => [],
                'payouts' => [],
                'stats' => null,
                'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
                'currency' => $user->currentCompany?->currency ?? 'XOF',
            ]);
        }

        $company = $user->currentCompany;

        $preview = $this->commissions->preview($company, $from, $to);
        $baseByAgent = collect($preview['rows'])->keyBy('agent_id');

        $agents = SalesAgent::where('company_id', $companyId)
            ->withCount('customers')
            ->orderBy('name')
            ->get()
            ->map(fn (SalesAgent $a) => [
                'id' => $a->id,
                'name' => $a->name,
                'email' => $a->email,
                'phone' => $a->phone,
                'commission_rate' => (float) $a->commission_rate,
                'is_active' => (bool) $a->is_active,
                'notes' => $a->notes,
                'customers_count' => (int) $a->customers_count,
                'base' => (float) ($baseByAgent[$a->id]['base'] ?? 0),
                'commission' => (float) ($baseByAgent[$a->id]['commission'] ?? 0),
            ]);

        $customers = Customer::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'sales_agent_id'])
            ->map(fn (Customer $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'sales_agent_id' => $c->sales_agent_id,
            ]);

        $payouts = CommissionPayout::where('company_id', $companyId)
            ->with('agent:id,name')
            ->orderByDesc('period_end')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn (CommissionPayout $p) => [
                'id' => $p->id,
                'agent' => $p->agent?->only(['id', 'name']),
                'period_start' => $p->period_start?->toDateString(),
                'period_end' => $p->period_end?->toDateString(),
                'base_amount' => (float) $p->base_amount,
                'rate' => (float) $p->rate,
                'commission_amount' => (float) $p->commission_amount,
                'status' => $p->status,
                'paid_at' => $p->paid_at?->toDateString(),
            ]);

        $stats = [
            'active_agents' => (int) SalesAgent::where('company_id', $companyId)->where('is_active', true)->count(),
            'commissionable_month' => (float) $preview['total_base'],
            'commissions_due' => (float) CommissionPayout::where('company_id', $companyId)
                ->where('status', 'pending')
                ->sum('commission_amount'),
        ];

        return Inertia::render('Commissions/Index', [
            'hasAccess' => true,
            'agents' => $agents,
            'preview' => $preview,
            'customers' => $customers,
            'payouts' => $payouts,
            'stats' => $stats,
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'currency' => $user->currentCompany?->currency ?? 'XOF',
        ]);
    }

    /** Crée un vendeur. */
    public function storeAgent(Request $request): RedirectResponse
    {
        $this->guardMutation($request);

        SalesAgent::create([
            'company_id' => $request->user()->current_company_id,
            ...$this->validateAgent($request),
        ]);

        return redirect()->route('commissions.index')->with('success', 'Vendeur ajouté.');
    }

    /** Met à jour un vendeur. */
    public function updateAgent(Request $request, SalesAgent $agent): RedirectResponse
    {
        $this->guardMutation($request);
        $this->ensureAgent($request, $agent);

        $agent->update($this->validateAgent($request));

        return redirect()->route('commissions.index')->with('success', 'Vendeur mis à jour.');
    }

    /** Supprime (soft delete) un vendeur ; ses clients sont détachés. */
    public function destroyAgent(Request $request, SalesAgent $agent): RedirectResponse
    {
        $this->guardMutation($request);
        $this->ensureAgent($request, $agent);

        // Détache les clients pour ne pas laisser de référence orpheline.
        Customer::where('company_id', $agent->company_id)
            ->where('sales_agent_id', $agent->id)
            ->update(['sales_agent_id' => null]);

        $agent->delete();

        return redirect()->route('commissions.index')->with('success', 'Vendeur supprimé.');
    }

    /** Affecte une liste de clients (de la société) à un vendeur. */
    public function assign(Request $request, SalesAgent $agent): RedirectResponse
    {
        $this->guardMutation($request);
        $this->ensureAgent($request, $agent);

        $data = $request->validate([
            'customer_ids' => ['present', 'array'],
            'customer_ids.*' => ['integer'],
        ]);

        $companyId = $request->user()->current_company_id;

        // On ne touche qu'aux clients de la société courante.
        Customer::where('company_id', $companyId)
            ->whereIn('id', $data['customer_ids'])
            ->update(['sales_agent_id' => $agent->id]);

        return redirect()->route('commissions.index')->with('success', 'Clients affectés au vendeur.');
    }

    /** Génère un décompte de commission pour un vendeur sur une période. */
    public function generatePayout(Request $request): RedirectResponse
    {
        $this->guardMutation($request);

        $data = $request->validate([
            'sales_agent_id' => ['required', 'integer'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $agent = SalesAgent::where('company_id', $request->user()->current_company_id)
            ->findOrFail($data['sales_agent_id']);

        $this->commissions->generatePayout(
            $agent,
            Carbon::parse($data['from']),
            Carbon::parse($data['to']),
            isset($data['rate']) ? (float) $data['rate'] : null,
            $request->user()->id,
        );

        return redirect()->route('commissions.index')->with('success', 'Décompte de commission généré.');
    }

    /** Marque un décompte comme payé. */
    public function payPayout(Request $request, CommissionPayout $payout): RedirectResponse
    {
        $this->guardMutation($request);
        $this->ensurePayout($request, $payout);

        $this->commissions->markPaid($payout);

        return redirect()->route('commissions.index')->with('success', 'Commission marquée comme payée.');
    }

    /* --------------------------------------------------------------------- */

    /** Règles de validation d'un vendeur. */
    private function validateAgent(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
