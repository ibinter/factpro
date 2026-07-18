<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Services\ProjectBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Phase 15 — Gestion des jalons de projet.
 */
class ProjectMilestoneController extends Controller
{
    public function __construct(
        private ProjectBillingService $billing,
    ) {
    }

    private function authorizeProject(Request $request, Project $project): void
    {
        abort_unless($project->company_id === $request->user()->current_company_id, 403);
    }

    private function authorizeMilestone(Request $request, ProjectMilestone $milestone): void
    {
        abort_unless($milestone->company_id === $request->user()->current_company_id, 403);
    }

    /** Liste des jalons + statut budget. */
    public function index(Request $request, Project $project): Response
    {
        $this->authorizeProject($request, $project);

        $project->load('customer:id,name');
        $milestones = $project->milestones()
            ->with('document:id,number,type')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get()
            ->map(fn (ProjectMilestone $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'description' => $m->description,
                'due_date' => $m->due_date?->toDateString(),
                'completion_pct' => $m->completion_pct,
                'status' => $m->status,
                'billing_amount' => $m->billing_amount,
                'document_id' => $m->document_id,
                'document' => $m->document ? $m->document->only('id', 'number', 'type') : null,
                'invoiced_at' => $m->invoiced_at?->toDateTimeString(),
            ]);

        $budgetStatus = $this->billing->getBudgetStatus($project);
        $completionPct = $this->billing->getCompletionPct($project);

        return Inertia::render('Projects/Milestones', [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status,
                'customer' => $project->customer?->only('id', 'name'),
                'currency' => $project->currency,
                'budget_hours' => $project->budget_hours,
                'budget_amount' => $project->budget_amount,
                'alert_threshold_pct' => $project->alert_threshold_pct,
            ],
            'milestones' => $milestones,
            'budgetStatus' => $budgetStatus,
            'completionPct' => $completionPct,
        ]);
    }

    /** Créer un jalon. */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorizeProject($request, $project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'completion_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::in(ProjectMilestone::STATUSES)],
            'billing_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $project->milestones()->create([
            ...$data,
            'company_id' => $project->company_id,
            'completion_pct' => $data['completion_pct'] ?? 0,
            'status' => $data['status'] ?? 'pending',
        ]);

        return back()->with('success', 'Jalon créé.');
    }

    /** Modifier un jalon (complétion, statut, etc.). */
    public function update(Request $request, ProjectMilestone $milestone): RedirectResponse
    {
        $this->authorizeMilestone($request, $milestone);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'completion_pct' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['nullable', Rule::in(ProjectMilestone::STATUSES)],
            'billing_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $milestone->update($data);

        return back()->with('success', 'Jalon mis à jour.');
    }

    /** Supprimer un jalon. */
    public function destroy(Request $request, ProjectMilestone $milestone): RedirectResponse
    {
        $this->authorizeMilestone($request, $milestone);

        $milestone->delete();

        return back()->with('success', 'Jalon supprimé.');
    }

    /** Facturer un jalon → crée une facture d'acompte. */
    public function bill(Request $request, ProjectMilestone $milestone): RedirectResponse
    {
        $this->authorizeMilestone($request, $milestone);

        try {
            $document = $this->billing->billMilestone($milestone, $request->user());
        } catch (\LogicException $e) {
            return back()->withErrors(['milestone' => $e->getMessage()]);
        }

        return redirect()->route('documents.show', $document)
            ->with('success', 'Facture d\'acompte ' . $document->number . ' générée.');
    }

    /** Statut budget du projet (JSON). */
    public function budgetStatus(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProject($request, $project);

        return response()->json($this->billing->getBudgetStatus($project));
    }
}
