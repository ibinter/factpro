<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Deal;
use App\Models\DealActivity;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CrmController extends Controller
{
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    public function __construct(private LicenseService $licenses) {}

    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    private function authorizeCompany(Deal $deal, Request $request): void
    {
        abort_unless($deal->company_id === $request->user()->current_company_id, 403);
    }

    public function pipeline(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        if (! $this->hasAccess($request)) {
            return Inertia::render('Crm/Pipeline', [
                'hasAccess' => false,
                'stages'    => [],
                'stats'     => null,
                'customers' => [],
            ]);
        }

        $stages = ['prospect', 'contacted', 'qualified', 'quote_sent', 'won', 'lost'];

        $allDeals = Deal::forCompany($company->id)
            ->with(['customer', 'assignedTo'])
            ->get();

        $grouped = [];
        foreach ($stages as $stage) {
            $dealsInStage = $allDeals->where('stage', $stage)->values();
            $grouped[$stage] = [
                'deals' => $dealsInStage,
                'count' => $dealsInStage->count(),
                'total' => $dealsInStage->sum('value'),
            ];
        }

        $active  = $allDeals->whereNotIn('stage', ['won', 'lost']);
        $won30   = $allDeals->where('stage', 'won')
            ->where('won_at', '>=', now()->subDays(30));

        $stats = [
            'total_pipeline'  => $active->sum('value'),
            'active_count'    => $active->count(),
            'won_value_month' => $won30->sum('value'),
            'closing_rate'    => $allDeals->count()
                ? round($allDeals->where('stage', 'won')->count() / $allDeals->count() * 100, 1)
                : 0,
        ];

        $customers = Customer::where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Crm/Pipeline', [
            'hasAccess' => true,
            'stages'    => $grouped,
            'stats'     => $stats,
            'customers' => $customers,
        ]);
    }

    public function show(Deal $deal, Request $request): Response
    {
        $this->authorizeCompany($deal, $request);

        abort_unless($this->hasAccess($request), 403);

        $deal->load(['customer', 'assignedTo', 'document', 'activities.user']);

        return Inertia::render('Crm/Show', [
            'deal'       => $deal,
            'activities' => $deal->activities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->hasAccess($request), 403);

        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'prospect_name'       => 'nullable|string|max:100',
            'prospect_email'      => 'nullable|email|max:255',
            'prospect_phone'      => 'nullable|string|max:30',
            'customer_id'         => 'nullable|exists:customers,id',
            'stage'               => 'nullable|in:prospect,contacted,qualified,quote_sent,won,lost',
            'value'               => 'nullable|numeric|min:0',
            'probability'         => 'nullable|integer|min:0|max:100',
            'source'              => 'nullable|string|max:50',
            'notes'               => 'nullable|string',
            'expected_close_date' => 'nullable|date',
            'assigned_to_id'      => 'nullable|exists:users,id',
        ]);

        $deal = Deal::create([
            ...$data,
            'company_id' => $company->id,
            'stage'      => $data['stage'] ?? 'prospect',
        ]);

        DealActivity::record($deal, $request->user(), 'note', 'Deal créé.');

        return redirect()->route('crm.show', $deal)->with('success', 'Deal créé avec succès.');
    }

    public function update(Deal $deal, Request $request): RedirectResponse
    {
        $this->authorizeCompany($deal, $request);
        abort_unless($this->hasAccess($request), 403);

        $data = $request->validate([
            'prospect_name'       => 'nullable|string|max:100',
            'prospect_email'      => 'nullable|email|max:255',
            'prospect_phone'      => 'nullable|string|max:30',
            'customer_id'         => 'nullable|exists:customers,id',
            'value'               => 'nullable|numeric|min:0',
            'probability'         => 'nullable|integer|min:0|max:100',
            'source'              => 'nullable|string|max:50',
            'notes'               => 'nullable|string',
            'expected_close_date' => 'nullable|date',
            'assigned_to_id'      => 'nullable|exists:users,id',
        ]);

        $deal->update($data);

        return redirect()->route('crm.index')->with('success', 'Deal mis à jour.');
    }

    public function moveStage(Deal $deal, Request $request): RedirectResponse
    {
        $this->authorizeCompany($deal, $request);
        abort_unless($this->hasAccess($request), 403);

        $data = $request->validate([
            'stage' => 'required|in:prospect,contacted,qualified,quote_sent,won,lost',
        ]);

        $from = $deal->stage;
        $to   = $data['stage'];

        $deal->update(['stage' => $to]);

        DealActivity::record($deal, $request->user(), 'stage_change',
            "Étape changée : {$from} → {$to}",
            ['from' => $from, 'to' => $to]
        );

        return redirect()->route('crm.index')->with('success', 'Étape mise à jour.');
    }

    public function markWon(Deal $deal, Request $request): RedirectResponse
    {
        $this->authorizeCompany($deal, $request);
        abort_unless($this->hasAccess($request), 403);

        $deal->update([
            'stage'  => 'won',
            'won_at' => now(),
        ]);

        if (! $deal->customer_id) {
            $deal->convertToCustomer();
        }

        DealActivity::record($deal, $request->user(), 'note', 'Deal marqué comme Gagné.');

        return redirect()->route('crm.index')->with('success', 'Deal gagné ! Client créé si prospect.');
    }

    public function markLost(Deal $deal, Request $request): RedirectResponse
    {
        $this->authorizeCompany($deal, $request);
        abort_unless($this->hasAccess($request), 403);

        $data = $request->validate([
            'lost_reason' => 'nullable|string',
        ]);

        $deal->update([
            'stage'       => 'lost',
            'lost_at'     => now(),
            'lost_reason' => $data['lost_reason'] ?? null,
        ]);

        DealActivity::record($deal, $request->user(), 'note',
            'Deal marqué comme Perdu. Raison : '.($data['lost_reason'] ?? 'non précisée')
        );

        return redirect()->route('crm.index')->with('success', 'Deal marqué comme perdu.');
    }

    public function addActivity(Deal $deal, Request $request): RedirectResponse
    {
        $this->authorizeCompany($deal, $request);
        abort_unless($this->hasAccess($request), 403);

        $data = $request->validate([
            'type'    => 'required|in:note,call,email,meeting',
            'content' => 'required|string',
        ]);

        DealActivity::record($deal, $request->user(), $data['type'], $data['content']);

        return redirect()->route('crm.index')->with('success', 'Activité enregistrée.');
    }

    public function stats(Request $request): JsonResponse
    {
        abort_unless($this->hasAccess($request), 403);

        $company = $request->user()->currentCompany;

        $deals = Deal::forCompany($company->id)->get();

        $byStage = $deals->groupBy('stage')->map(fn ($g) => [
            'count' => $g->count(),
            'value' => $g->sum('value'),
        ]);

        $total = $deals->whereNotIn('stage', ['won', 'lost'])->sum('value');
        $won30 = $deals->where('stage', 'won')->where('won_at', '>=', now()->subDays(30));

        $rate = $deals->count() > 0
            ? round($deals->where('stage', 'won')->count() / $deals->count() * 100, 1)
            : 0;

        return response()->json([
            'by_stage'        => $byStage,
            'total_pipeline'  => $total,
            'closing_rate_30' => $rate,
            'won_value_month' => $won30->sum('value'),
        ]);
    }
}
