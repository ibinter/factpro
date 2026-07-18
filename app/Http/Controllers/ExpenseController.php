<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Notes de frais (cahier §3 NDF) — dépenses collaborateurs avec justificatif
 * photo/PDF, workflow soumission → approbation/rejet → remboursement.
 * Réservé BUSINESS/ENTERPRISE (§22.1).
 */
class ExpenseController extends Controller
{
    /** Plans autorisés à utiliser les notes de frais. */
    private const ALLOWED_PLANS = ['business', 'enterprise'];

    public function __construct(private LicenseService $licenses)
    {
    }

    /** Le forfait courant donne-t-il accès aux notes de frais ? */
    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && in_array($license->plan?->code, self::ALLOWED_PLANS, true);
    }

    /** L'utilisateur peut-il approuver/rejeter (owner_id de la société OU rôle pivot owner/admin) ? */
    private function canReview(User $user): bool
    {
        if (! $user->current_company_id) {
            return false;
        }

        if ((int) $user->currentCompany?->owner_id === (int) $user->id) {
            return true;
        }

        $role = $user->companies()
            ->where('companies.id', $user->current_company_id)
            ->first()?->pivot->role;

        return in_array($role, ['owner', 'admin'], true);
    }

    /** L'utilisateur est-il le propriétaire (owner_id) de sa société courante ? */
    private function isCompanyOwner(User $user): bool
    {
        return (int) $user->currentCompany?->owner_id === (int) $user->id;
    }

    /** Garde-fou multi-sociétés : la dépense doit appartenir à la société courante. */
    private function ensureSameCompany(Request $request, Expense $expense): void
    {
        abort_if(
            (int) $expense->company_id !== (int) $request->user()->current_company_id,
            404
        );
    }

    /** Liste des notes de frais (ou upsell si forfait insuffisant). */
    public function index(Request $request): Response
    {
        $hasAccess = $this->hasAccess($request);
        $user = $request->user();

        if (! $hasAccess) {
            return Inertia::render('Expenses/Index', [
                'hasAccess' => false,
                'canReview' => false,
                'expenses' => null,
                'stats' => null,
                'categories' => Expense::CATEGORIES,
                'filters' => (object) [],
                'currency' => $user->currentCompany?->currency ?? 'XOF',
            ]);
        }

        $canReview = $this->canReview($user);
        $isCompanyOwner = $this->isCompanyOwner($user);

        // Périmètre : les approbateurs voient toute la société, les autres uniquement leurs dépenses.
        $base = Expense::where('company_id', $user->current_company_id)
            ->when(! $canReview, fn ($q) => $q->where('user_id', $user->id));

        $expenses = (clone $base)
            ->with(['user:id,name', 'reviewer:id,name'])
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->category, fn ($q, $c) => $q->where('category', $c))
            ->when($request->month, function ($q, $m) {
                [$year, $month] = array_pad(explode('-', (string) $m), 2, null);
                if (is_numeric($year) && is_numeric($month)) {
                    $q->whereYear('expense_date', (int) $year)
                        ->whereMonth('expense_date', (int) $month);
                }
            })
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Expense $e) => [
                'id' => $e->id,
                'expense_date' => $e->expense_date?->format('Y-m-d'),
                'category' => $e->category,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'currency' => $e->currency,
                'status' => $e->status,
                'review_note' => $e->review_note,
                'reimbursed_at' => $e->reimbursed_at?->format('Y-m-d'),
                'user' => $e->user?->only(['id', 'name']),
                'reviewer' => $e->reviewer?->only(['id', 'name']),
                'has_receipt' => $e->receipt_path !== null,
                'receipt_original_name' => $e->receipt_original_name,
                'can_edit' => (int) $e->user_id === (int) $user->id
                    && in_array($e->status, Expense::EDITABLE_STATUSES, true),
                'can_review' => $canReview
                    && $e->status === 'submitted'
                    && ((int) $e->user_id !== (int) $user->id || $isCompanyOwner),
                'can_reimburse' => $canReview && $e->status === 'approved',
            ]);

        $stats = [
            'pending_count' => (clone $base)->where('status', 'submitted')->count(),
            'pending_amount' => (float) (clone $base)->where('status', 'submitted')->sum('amount'),
            'approved_amount' => (float) (clone $base)->where('status', 'approved')->sum('amount'),
            'reimbursed_month_amount' => (float) (clone $base)->where('status', 'reimbursed')
                ->where('reimbursed_at', '>=', now()->startOfMonth()->toDateString())
                ->sum('amount'),
        ];

        return Inertia::render('Expenses/Index', [
            'hasAccess' => true,
            'canReview' => $canReview,
            'expenses' => $expenses,
            'stats' => $stats,
            'categories' => Expense::CATEGORIES,
            'filters' => $request->only('status', 'category', 'month'),
            'currency' => $user->currentCompany?->currency ?? 'XOF',
        ]);
    }

    /** Déclare une nouvelle dépense (statut submitted, justificatif privé optionnel). */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Les notes de frais sont réservées aux forfaits BUSINESS et ENTERPRISE.'
        );

        $data = $this->validateExpense($request);
        $user = $request->user();

        $receipt = $this->storeReceipt($request);

        Expense::create([
            'company_id' => $user->current_company_id,
            'user_id' => $user->id,
            'category' => $data['category'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'currency' => $user->currentCompany?->currency ?? 'XOF',
            'expense_date' => $data['expense_date'],
            'status' => 'submitted',
            ...$receipt,
        ]);

        return back()->with('success', 'Note de frais soumise pour validation.');
    }

    /** Met à jour une dépense (uniquement la sienne, tant qu'elle n'est pas approuvée/remboursée). */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Les notes de frais sont réservées aux forfaits BUSINESS et ENTERPRISE.'
        );
        $this->ensureSameCompany($request, $expense);

        abort_if(
            (int) $expense->user_id !== (int) $request->user()->id,
            403,
            'Vous ne pouvez modifier que vos propres notes de frais.'
        );
        abort_unless(
            in_array($expense->status, Expense::EDITABLE_STATUSES, true),
            403,
            'Une note de frais approuvée ou remboursée ne peut plus être modifiée.'
        );

        $data = $this->validateExpense($request);

        // Un nouveau justificatif remplace l'ancien (supprimé du disque privé).
        $receipt = [];
        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk(config('factpro.proofs.disk'))->delete($expense->receipt_path);
            }
            $receipt = $this->storeReceipt($request);
        }

        $expense->update([
            'category' => $data['category'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'],
            // Une dépense corrigée repart en validation (efface la revue précédente).
            'status' => 'submitted',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
            ...$receipt,
        ]);

        return back()->with('success', 'Note de frais mise à jour et resoumise pour validation.');
    }

    /** Supprime (soft delete) une dépense — mêmes règles que la modification. */
    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Les notes de frais sont réservées aux forfaits BUSINESS et ENTERPRISE.'
        );
        $this->ensureSameCompany($request, $expense);

        abort_if(
            (int) $expense->user_id !== (int) $request->user()->id,
            403,
            'Vous ne pouvez supprimer que vos propres notes de frais.'
        );
        abort_unless(
            in_array($expense->status, Expense::EDITABLE_STATUSES, true),
            403,
            'Une note de frais approuvée ou remboursée ne peut plus être supprimée.'
        );

        $expense->delete();

        return back()->with('success', 'Note de frais supprimée.');
    }

    /** Consultation sécurisée du justificatif (stockage privé, streamé inline). */
    public function receipt(Request $request, Expense $expense): StreamedResponse
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Les notes de frais sont réservées aux forfaits BUSINESS et ENTERPRISE.'
        );
        $this->ensureSameCompany($request, $expense);

        $user = $request->user();
        abort_unless(
            (int) $expense->user_id === (int) $user->id || $this->canReview($user),
            403,
            'Vous n\'êtes pas autorisé à consulter ce justificatif.'
        );

        abort_if($expense->receipt_path === null, 404);

        $disk = Storage::disk(config('factpro.proofs.disk'));
        abort_unless($disk->exists($expense->receipt_path), 404);

        $filename = $expense->receipt_original_name ?? basename($expense->receipt_path);

        $headers = ['Content-Disposition' => 'inline; filename="'.$filename.'"'];
        if ($expense->receipt_mime) {
            $headers['Content-Type'] = $expense->receipt_mime;
        }

        return $disk->response($expense->receipt_path, $filename, $headers);
    }

    /** Approuve ou rejette une dépense soumise (approbateurs seulement, motif requis si rejet). */
    public function review(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Les notes de frais sont réservées aux forfaits BUSINESS et ENTERPRISE.'
        );
        $this->ensureSameCompany($request, $expense);

        $user = $request->user();
        abort_unless(
            $this->canReview($user),
            403,
            'Seuls le propriétaire et les administrateurs peuvent valider les notes de frais.'
        );

        // Séparation des rôles : on ne valide pas sa propre dépense
        // (sauf le propriétaire de la société, seul maître à bord).
        abort_if(
            (int) $expense->user_id === (int) $user->id && ! $this->isCompanyOwner($user),
            403,
            'Vous ne pouvez pas valider votre propre note de frais.'
        );

        abort_unless(
            $expense->status === 'submitted',
            403,
            'Seule une note de frais soumise peut être approuvée ou rejetée.'
        );

        $data = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'note' => ['nullable', 'string', 'max:255', 'required_if:decision,reject'],
        ], [
            'note.required_if' => 'Un motif est obligatoire pour rejeter une note de frais.',
        ]);

        $approved = $data['decision'] === 'approve';

        $expense->update([
            'status' => $approved ? 'approved' : 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_note' => $data['note'] ?? null,
        ]);

        return back()->with(
            'success',
            $approved ? 'Note de frais approuvée.' : 'Note de frais rejetée.'
        );
    }

    /** Marque une dépense approuvée comme remboursée (approbateurs seulement). */
    public function reimburse(Request $request, Expense $expense): RedirectResponse
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Les notes de frais sont réservées aux forfaits BUSINESS et ENTERPRISE.'
        );
        $this->ensureSameCompany($request, $expense);

        abort_unless(
            $this->canReview($request->user()),
            403,
            'Seuls le propriétaire et les administrateurs peuvent marquer un remboursement.'
        );
        abort_unless(
            $expense->status === 'approved',
            403,
            'Seule une note de frais approuvée peut être marquée comme remboursée.'
        );

        $expense->update([
            'status' => 'reimbursed',
            'reimbursed_at' => today(),
        ]);

        return back()->with('success', 'Note de frais marquée comme remboursée.');
    }

    /** Règles de validation communes création/mise à jour. */
    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'category' => ['required', Rule::in(array_keys(Expense::CATEGORIES))],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ]);
    }

    /** Stocke le justificatif sur le disque privé (pattern preuves de paiement, script §8). */
    private function storeReceipt(Request $request): array
    {
        $file = $request->file('receipt');
        if (! $file) {
            return [];
        }

        $storedName = Str::random(40).'.'.strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('private/receipts', $storedName, config('factpro.proofs.disk'));

        return [
            'receipt_path' => $path,
            'receipt_original_name' => $file->getClientOriginalName(),
            'receipt_mime' => (string) $file->getMimeType(),
        ];
    }
}
