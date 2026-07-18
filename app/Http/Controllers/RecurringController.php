<?php

namespace App\Http\Controllers;

use App\Models\RecurringTemplate;
use App\Services\LicenseService;
use App\Services\RecurringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Factures récurrentes / abonnements automatiques (cahier §3).
 * Inclus dès le forfait PRO (§22.1) — le forfait STARTER voit un upsell.
 */
class RecurringController extends Controller
{
    /** Fréquence → nombre de périodes par an (pour le MRR estimé). */
    private const PERIODS_PER_YEAR = [
        'weekly' => 52,
        'monthly' => 12,
        'quarterly' => 4,
        'semiannual' => 2,
        'yearly' => 1,
    ];

    public function __construct(
        private LicenseService $licenses,
        private RecurringService $recurring,
    ) {
    }

    /** Le forfait courant donne-t-il accès aux factures récurrentes ? (PRO et plus) */
    private function hasAccess(Request $request): bool
    {
        $license = $this->licenses->currentFor($request->user());

        return $license !== null
            && $license->isUsable()
            && $license->plan?->code !== 'starter';
    }

    /** Liste des gabarits + stats (ou upsell si forfait STARTER). */
    public function index(Request $request): Response
    {
        $hasAccess = $this->hasAccess($request);
        $company = $request->user()->currentCompany;

        $templates = [];
        $stats = ['active' => 0, 'mrr' => 0.0];
        $customers = [];
        $products = [];

        if ($hasAccess) {
            $templates = RecurringTemplate::where('company_id', $company->id)
                ->with('customer:id,name,email')
                ->orderByDesc('is_active')
                ->orderBy('next_run_date')
                ->get()
                ->map(fn (RecurringTemplate $t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'customer' => $t->customer,
                    'customer_id' => $t->customer_id,
                    'frequency' => $t->frequency,
                    'interval' => $t->interval,
                    'day_of_month' => $t->day_of_month,
                    'next_run_date' => $t->next_run_date?->toDateString(),
                    'last_run_date' => $t->last_run_date?->toDateString(),
                    'end_date' => $t->end_date?->toDateString(),
                    'occurrences_limit' => $t->occurrences_limit,
                    'occurrences_done' => $t->occurrences_done,
                    'currency' => $t->currency,
                    'due_days' => $t->due_days,
                    'auto_finalize' => $t->auto_finalize,
                    'auto_send' => $t->auto_send,
                    'notes' => $t->notes,
                    'terms' => $t->terms,
                    'lines' => $t->lines,
                    'is_active' => $t->is_active,
                    'total' => $this->templateTotal($t->lines ?? []),
                ])
                ->values();

            $active = $templates->where('is_active', true);
            $stats = [
                'active' => $active->count(),
                // MRR estimé : total TTC du gabarit mensualisé (× périodes/an ÷ 12)
                'mrr' => round($active->sum(function (array $t) {
                    $perYear = (self::PERIODS_PER_YEAR[$t['frequency']] ?? 12) / max(1, $t['interval']);

                    return $t['total'] * $perYear / 12;
                }), 2),
            ];

            $customers = $company->customers()->orderBy('name')->get(['id', 'name', 'email', 'currency']);
            $products = $company->products()->where('is_active', true)->orderBy('name')
                ->get(['id', 'name', 'description', 'unit', 'price', 'tax_rate']);
        }

        return Inertia::render('Recurring/Index', [
            'hasAccess' => $hasAccess,
            'templates' => $templates,
            'stats' => $stats,
            'customers' => $customers,
            'products' => $products,
            'defaults' => [
                'currency' => $company->currency,
                'tax_rate' => (float) $company->default_tax_rate,
            ],
        ]);
    }

    /** Crée un gabarit de facture récurrente. */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($request);

        $data = $this->validateData($request, creating: true);
        $company = $request->user()->currentCompany;

        RecurringTemplate::create([
            ...$data,
            'currency' => $data['currency'] ?? $company->currency,
            'company_id' => $company->id,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Gabarit de facture récurrente créé.');
    }

    /** Met à jour un gabarit. */
    public function update(Request $request, RecurringTemplate $template): RedirectResponse
    {
        $this->authorizeAccess($request);
        $this->authorizeTemplate($request, $template);

        $data = $this->validateData($request, creating: false);

        $template->update($data);

        return back()->with('success', 'Gabarit mis à jour.');
    }

    /** Supprime (soft delete) un gabarit — l'historique des factures est conservé. */
    public function destroy(Request $request, RecurringTemplate $template): RedirectResponse
    {
        $this->authorizeAccess($request);
        $this->authorizeTemplate($request, $template);

        $template->delete();

        return back()->with('success', 'Gabarit supprimé. Les factures déjà générées sont conservées.');
    }

    /** Pause / reprise du gabarit. */
    public function toggle(Request $request, RecurringTemplate $template): RedirectResponse
    {
        $this->authorizeAccess($request);
        $this->authorizeTemplate($request, $template);

        $template->update(['is_active' => ! $template->is_active]);

        return back()->with('success', $template->is_active
            ? 'Gabarit réactivé : les émissions reprennent.'
            : 'Gabarit mis en pause.');
    }

    /** « Générer maintenant » : facture immédiate + prochaine échéance recalculée. */
    public function run(Request $request, RecurringTemplate $template): RedirectResponse
    {
        $this->authorizeAccess($request);
        $this->authorizeTemplate($request, $template);

        $document = $this->recurring->generateNow($template);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Facture '.$document->number.' générée depuis le gabarit « '.$template->name.' ».');
    }

    /** Mutations réservées aux forfaits PRO et plus. */
    private function authorizeAccess(Request $request): void
    {
        abort_unless(
            $this->hasAccess($request),
            403,
            'Les factures récurrentes sont disponibles dès le forfait PRO.'
        );
    }

    private function authorizeTemplate(Request $request, RecurringTemplate $template): void
    {
        abort_unless($template->company_id === $request->user()->current_company_id, 403);
    }

    /** Total TTC du gabarit calculé depuis les lignes JSON (avec TVA ligne à ligne). */
    private function templateTotal(array $lines): float
    {
        $total = 0.0;

        foreach ($lines as $line) {
            $quantity = (float) ($line['quantity'] ?? 1);
            $unitPrice = (float) ($line['unit_price'] ?? 0);
            $discount = (float) ($line['discount_percent'] ?? 0);
            $lineHt = round($quantity * $unitPrice * (1 - $discount / 100), 2);
            $total += $lineHt * (1 + ((float) ($line['tax_rate'] ?? 0)) / 100);
        }

        return round($total, 2);
    }

    /** Règles communes store/update (lignes identiques à DocumentController). */
    private function validateData(Request $request, bool $creating): array
    {
        $companyId = $request->user()->current_company_id;

        return $request->validate([
            'name' => 'required|string|max:255',
            'customer_id' => [
                'required',
                Rule::exists('customers', 'id')->where('company_id', $companyId),
            ],
            'frequency' => 'required|in:weekly,monthly,quarterly,semiannual,yearly',
            'interval' => 'required|integer|min:1|max:12',
            'day_of_month' => 'nullable|integer|min:1|max:28',
            'next_run_date' => array_filter([
                'required',
                'date',
                $creating ? 'after_or_equal:today' : null,
            ]),
            'due_days' => 'required|integer|min:0|max:120',
            'end_date' => 'nullable|date|after:next_run_date',
            'occurrences_limit' => 'nullable|integer|min:1|max:999',
            'auto_finalize' => 'boolean',
            'auto_send' => 'boolean',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('company_id', $companyId),
            ],
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit' => 'nullable|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ], [
            'next_run_date.after_or_equal' => 'La première émission doit être aujourd\'hui ou dans le futur.',
        ]);
    }
}
