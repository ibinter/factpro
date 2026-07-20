<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Services\DocumentService;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Time tracking & projets (cahier §9) — projets clients avec budget,
 * conversion des heures en facture. Réservé BUSINESS/ENTERPRISE (§22.1).
 */
class ProjectController extends Controller
{
    /** Plans autorisés à utiliser le module projets. */
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    public function __construct(
        private LicenseService $licenses,
        private DocumentService $documents,
    ) {
    }

    /** Le forfait courant donne-t-il accès au module projets ? */
    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    /** 403 si le forfait ne permet pas le module projets. */
    private function ensureAccess(Request $request): void
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Le suivi du temps et des projets est réservé aux forfaits BUSINESS et ENTERPRISE.'
        );
    }

    /** 403 si le projet n'appartient pas à la société courante. */
    private function authorizeProject(Request $request, Project $project): void
    {
        abort_unless($project->company_id === $request->user()->current_company_id, 403);
    }

    /** Liste des projets (ou upsell si forfait insuffisant). */
    public function index(Request $request): Response
    {
        $hasAccess = $this->hasAccess($request);

        $projects = [];
        $customers = [];
        $stats = ['active_projects' => 0, 'month_minutes' => 0, 'unbilled_amount' => 0.0];

        if ($hasAccess) {
            $company = $request->user()->currentCompany;

            // Montant facturable non facturé par projet (taux effectif entrée → projet).
            $unbilledByProject = TimeEntry::where('company_id', $company->id)
                ->where('is_billable', true)
                ->where('is_billed', false)
                ->with('project:id,hourly_rate')
                ->get()
                ->groupBy('project_id')
                ->map(fn ($entries) => round($entries->sum->amount, 2));

            $projects = Project::where('company_id', $company->id)
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->with('customer:id,name')
                ->withCount('entries')
                ->withSum('entries as total_minutes', 'duration_minutes')
                ->orderByDesc('id')
                ->get()
                ->map(fn (Project $project) => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'customer_id' => $project->customer_id,
                    'customer' => $project->customer?->only('id', 'name'),
                    'hourly_rate' => $project->hourly_rate,
                    'budget_hours' => $project->budget_hours,
                    'budget_amount' => $project->budget_amount,
                    'currency' => $project->currency,
                    'starts_at' => $project->starts_at?->toDateString(),
                    'ends_at' => $project->ends_at?->toDateString(),
                    'entries_count' => $project->entries_count,
                    'total_minutes' => (int) ($project->total_minutes ?? 0),
                    'unbilled_amount' => (float) ($unbilledByProject[$project->id] ?? 0),
                ]);

            $stats = [
                'active_projects' => Project::where('company_id', $company->id)
                    ->where('status', 'active')->count(),
                'month_minutes' => (int) TimeEntry::where('company_id', $company->id)
                    ->where('entry_date', '>=', now()->startOfMonth()->toDateString())
                    ->sum('duration_minutes'),
                'unbilled_amount' => round($unbilledByProject->sum(), 2),
            ];

            $customers = $company->customers()->orderBy('name')->get(['id', 'name']);
        }

        return Inertia::render('Projects/Index', [
            'hasAccess' => $hasAccess,
            'projects' => $projects,
            'customers' => $customers,
            'stats' => $stats,
            'filters' => $request->only('status'),
            'currency' => $request->user()->currentCompany?->currency ?? 'XOF',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAccess($request);

        $company = $request->user()->currentCompany;
        $data = $this->validateData($request);

        Project::create([
            ...$data,
            'company_id' => $company->id,
            'status' => $data['status'] ?? 'active',
            'currency' => $data['currency'] ?? $company->currency ?? 'XOF',
        ]);

        return redirect()->route('projects.index')->with('success', 'Projet créé.');
    }

    /** Détail d'un projet : entrées de temps, totaux et avancement budget. */
    public function show(Request $request, Project $project): Response
    {
        $this->ensureAccess($request);
        $this->authorizeProject($request, $project);

        $project->load('customer:id,name');

        // Totaux calculés sur toutes les entrées (pas seulement la page courante).
        $all = $project->entries()->get();
        $all->each->setRelation('project', $project);

        $totalMinutes = (int) $all->sum('duration_minutes');
        $unbilled = $all->where('is_billable', true)->where('is_billed', false);
        $unbilledMinutes = (int) $unbilled->sum('duration_minutes');
        $unbilledAmount = round($unbilled->sum->amount, 2);
        $totalBillableAmount = round($all->where('is_billable', true)->sum->amount, 2);

        $hoursPct = $project->budget_hours > 0
            ? round($totalMinutes / 60 / (float) $project->budget_hours * 100, 1)
            : null;
        $amountPct = $project->budget_amount > 0
            ? round($totalBillableAmount / (float) $project->budget_amount * 100, 1)
            : null;

        $entries = $project->entries()
            ->with('user:id,name')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (TimeEntry $entry) => [
                'id' => $entry->id,
                'description' => $entry->description,
                'entry_date' => $entry->entry_date->toDateString(),
                'duration_minutes' => $entry->duration_minutes,
                'hourly_rate' => $entry->hourly_rate,
                'effective_rate' => (float) ($entry->hourly_rate ?? $project->hourly_rate ?? 0),
                'amount' => round($entry->duration_minutes / 60 * (float) ($entry->hourly_rate ?? $project->hourly_rate ?? 0), 2),
                'is_billable' => $entry->is_billable,
                'is_billed' => $entry->is_billed,
                'document_id' => $entry->document_id,
                'user' => $entry->user?->only('id', 'name'),
            ]);

        return Inertia::render('Projects/Show', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'customer' => $project->customer?->only('id', 'name'),
                'hourly_rate' => $project->hourly_rate,
                'budget_hours' => $project->budget_hours,
                'budget_amount' => $project->budget_amount,
                'currency' => $project->currency,
                'starts_at' => $project->starts_at?->toDateString(),
                'ends_at' => $project->ends_at?->toDateString(),
            ],
            'entries' => $entries,
            'totals' => [
                'total_minutes' => $totalMinutes,
                'total_billable_amount' => $totalBillableAmount,
                'unbilled_minutes' => $unbilledMinutes,
                'unbilled_amount' => $unbilledAmount,
                'hours_pct' => $hoursPct,
                'amount_pct' => $amountPct,
                'hours_over_budget' => $hoursPct !== null && $hoursPct > 100,
                'amount_over_budget' => $amountPct !== null && $amountPct > 100,
            ],
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->ensureAccess($request);
        $this->authorizeProject($request, $project);

        $data = $this->validateData($request);
        $project->update($data);

        return redirect()->route('projects.index')->with('success', 'Projet mis à jour.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $this->ensureAccess($request);
        $this->authorizeProject($request, $project);

        $project->delete(); // soft delete

        return redirect()->route('projects.index')->with('success', 'Projet supprimé.');
    }

    /** Convertit les entrées de temps sélectionnées en facture (une ligne par entrée). */
    public function invoice(Request $request, Project $project): RedirectResponse
    {
        $this->ensureAccess($request);
        $this->authorizeProject($request, $project);

        $data = $request->validate([
            'entry_ids' => ['required', 'array', 'min:1'],
            'entry_ids.*' => ['required', 'integer'],
        ]);

        if ($project->customer_id === null) {
            throw ValidationException::withMessages([
                'entry_ids' => 'Ce projet n\'a pas de client : associez un client avant de facturer.',
            ]);
        }

        $entries = TimeEntry::where('project_id', $project->id)
            ->where('company_id', $project->company_id)
            ->whereIn('id', $data['entry_ids'])
            ->where('is_billable', true)
            ->where('is_billed', false)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        if ($entries->count() !== count(array_unique($data['entry_ids']))) {
            throw ValidationException::withMessages([
                'entry_ids' => 'Certaines entrées sont introuvables, non facturables ou déjà facturées.',
            ]);
        }

        $company = $request->user()->currentCompany;

        $lines = $entries->map(fn (TimeEntry $entry) => [
            'product_id' => null,
            'description' => $entry->entry_date->format('d/m/Y').' — '.$entry->description,
            'quantity' => round($entry->duration_minutes / 60, 2),
            'unit' => 'heure',
            'unit_price' => (float) ($entry->hourly_rate ?? $project->hourly_rate ?? 0),
            'tax_rate' => (float) $company->default_tax_rate,
            'discount_percent' => 0,
        ])->all();

        $document = DB::transaction(function () use ($request, $company, $project, $entries, $lines) {
            $document = $this->documents->create($company, $request->user(), [
                'type' => 'invoice',
                'customer_id' => $project->customer_id,
                'currency' => $project->currency,
                'issue_date' => now()->toDateString(),
                'reference' => 'Projet : '.$project->name,
            ], $lines);

            TimeEntry::whereIn('id', $entries->pluck('id'))->update([
                'is_billed' => true,
                'document_id' => $document->id,
            ]);

            return $document;
        });

        return redirect()->route('documents.show', $document)
            ->with('success', 'Facture '.$document->number.' créée à partir de '.$entries->count().' entrée(s) de temps.');
    }

    /** Validation commune création / mise à jour. */
    private function validateData(Request $request): array
    {
        $companyId = $request->user()->current_company_id;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where('company_id', $companyId),
            ],
            'status' => ['nullable', Rule::in(Project::STATUSES)],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'budget_hours' => ['nullable', 'numeric', 'min:0'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);
    }
}
