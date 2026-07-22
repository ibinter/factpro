<?php

namespace App\Http\Controllers;

use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\Document;
use App\Services\ApprovalService;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApprovalController extends Controller
{
    private const ALLOWED_PLANS = ['starter', 'pro', 'business', 'enterprise'];

    public function __construct(
        private ApprovalService $approvalService,
        private LicenseService $licenses,
    ) {}

    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    public function index(Request $request): Response
    {
        $user = $request->user();
        $company = $user->currentCompany;
        $hasAccess = $this->hasAccess($request);

        $pending = $hasAccess ? $this->approvalService->pendingForUser($user) : collect();
        $workflows = $hasAccess
            ? ApprovalWorkflow::where('company_id', $company->id)->get()
            : collect();

        return Inertia::render('Approval/Index', [
            'hasAccess' => $hasAccess,
            'pendingSteps' => $pending,
            'workflows' => $workflows,
        ]);
    }

    public function myPending(Request $request): JsonResponse
    {
        if (! $this->hasAccess($request)) {
            return response()->json(['error' => 'Plan BUSINESS+ requis.'], 403);
        }

        return response()->json($this->approvalService->pendingForUser($request->user()));
    }

    public function storeWorkflow(Request $request): RedirectResponse
    {
        if (! $this->hasAccess($request)) {
            abort(403, 'Circuit de validation non disponible sur votre plan.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'document_types' => ['required', 'array', 'min:1'],
            'document_types.*' => ['string'],
            'approvers' => ['required', 'array', 'min:1', 'max:5'],
            'approvers.*' => ['integer', 'exists:users,id'],
            'is_active' => ['boolean'],
        ]);

        $company = $request->user()->currentCompany;

        ApprovalWorkflow::create([
            'company_id' => $company->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'document_types' => $data['document_types'],
            'approvers' => $data['approvers'],
            'steps_count' => count($data['approvers']),
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('success', 'Workflow cr�� avec succ�s.');
    }

    public function submit(Document $document, Request $request): RedirectResponse
    {
        if (! $this->hasAccess($request)) {
            abort(403, 'Circuit de validation non disponible sur votre plan.');
        }

        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'workflow_id' => ['nullable', 'integer', 'exists:approval_workflows,id'],
        ]);

        // Si un workflow est pr�cis�, on l'utilise ; sinon on prend le premier de la soci�t�
        if (!empty($data['workflow_id'])) {
            $workflow = ApprovalWorkflow::findOrFail($data['workflow_id']);
            abort_unless($workflow->company_id === $company->id, 403);
        } else {
            $workflow = ApprovalWorkflow::where('company_id', $company->id)
                ->where('is_active', true)
                ->orderBy('id')
                ->first();
        }

        if (! $workflow) {
            return back()->withErrors(['workflow' => 'Aucun circuit de validation configur�. Cr�ez-en un dans Validation > Circuits.']);
        }

        $this->approvalService->submitForApproval($document, $workflow, $request->user());

        return back()->with('success', 'Document soumis au circuit de validation.');
    }

    public function approve(ApprovalStep $step, Request $request): RedirectResponse
    {
        if (! $this->hasAccess($request)) {
            abort(403, 'Circuit de validation non disponible sur votre plan.');
        }

        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->approvalService->approve($step, $request->user(), $data['comment'] ?? '');

        return back()->with('success', '�tape approuv�e.');
    }

    public function reject(ApprovalStep $step, Request $request): RedirectResponse
    {
        if (! $this->hasAccess($request)) {
            abort(403, 'Circuit de validation non disponible sur votre plan.');
        }

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $this->approvalService->reject($step, $request->user(), $data['comment']);

        return back()->with('success', '�tape rejet�e.');
    }

    public function delegate(ApprovalStep $step, Request $request): RedirectResponse
    {
        if (! $this->hasAccess($request)) {
            abort(403, 'Circuit de validation non disponible sur votre plan.');
        }

        $data = $request->validate([
            'delegate_to_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $to = \App\Models\User::findOrFail($data['delegate_to_id']);

        $this->approvalService->delegate($step, $request->user(), $to);

        return back()->with('success', '�tape d�l�gu�e.');
    }

    public function history(Document $document): JsonResponse
    {
        $steps = ApprovalStep::where('document_id', $document->id)
            ->with(['approver:id,name,email', 'delegatedTo:id,name,email'])
            ->orderBy('step_number')
            ->get();

        return response()->json([
            'document_id' => $document->id,
            'approval_status' => $document->approval_status,
            'steps' => $steps,
        ]);
    }
}

