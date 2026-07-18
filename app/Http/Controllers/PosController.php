<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\PosSession;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function __construct(private DocumentService $documents)
    {
    }

    /** Interface de caisse tactile (cahier des charges §7). */
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;
        $session = $this->openSessionFor($request);

        // Dernier ticket encaissé (passé en query après un checkout réussi)
        $lastTicket = null;
        if ($request->query('ticket')) {
            $doc = Document::where('company_id', $company->id)
                ->where('type', 'pos_ticket')
                ->find($request->query('ticket'));
            if ($doc) {
                $lastTicket = [
                    'id' => $doc->id,
                    'number' => $doc->number,
                    'total' => (float) $doc->total,
                ];
            }
        }

        return Inertia::render('Pos/Index', [
            'products' => $company->products()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'sku', 'barcode', 'price', 'tax_rate', 'unit', 'stock_quantity', 'track_stock', 'type']),
            'customers' => $company->customers()->orderBy('name')->get(['id', 'name']),
            'session' => $session?->only(['id', 'opening_float', 'opened_at', 'tickets_count', 'total_sales']),
            'currency' => $company->currency,
            'lastTicket' => $lastTicket,
        ]);
    }

    /** Ouvre une session de caisse avec fonds initial. */
    public function openSession(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'opening_float' => 'required|numeric|min:0',
            'cashier_name' => 'nullable|string|max:100',
            'cashier_pin' => 'nullable|string|min:4|max:20',
        ]);

        if ($this->openSessionFor($request)) {
            return back()->with('error', 'Une session de caisse est déjà ouverte.');
        }

        PosSession::create([
            'company_id' => $request->user()->current_company_id,
            'user_id' => $request->user()->id,
            'status' => 'open',
            'opening_float' => $data['opening_float'],
            'opened_at' => now(),
            'cashier_name' => $data['cashier_name'] ?? null,
            'cashier_pin' => isset($data['cashier_pin']) ? bcrypt($data['cashier_pin']) : null,
        ]);

        return back()->with('success', 'Caisse ouverte. Bonne vente !');
    }

    /** Encaisse une vente : ticket pos_ticket finalisé + paiements multi-moyens. */
    public function checkout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $company = $user->currentCompany;

        $data = $request->validate([
            'customer_id' => [
                'nullable',
                Rule::exists('customers', 'id')->where('company_id', $company->id),
            ],
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where('company_id', $company->id),
            ],
            'lines.*.description' => 'required|string',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit' => 'nullable|string|max:20',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,mobile_money,card,credit',
            'payments.*.amount' => 'required|numeric|min:0',
            'received' => 'nullable|numeric|min:0',
        ]);

        $session = $this->openSessionFor($request);
        if (! $session) {
            throw ValidationException::withMessages([
                'session' => 'Aucune session de caisse ouverte. Ouvrez la caisse avant d\'encaisser.',
            ]);
        }

        $document = DB::transaction(function () use ($data, $company, $user, $session) {
            $document = $this->documents->create($company, $user, [
                'type' => 'pos_ticket',
                'customer_id' => $data['customer_id'] ?? null,
                'issue_date' => now()->toDateString(),
                'currency' => $company->currency,
                'reference' => 'POS-S'.$session->id,
            ], $data['lines']);

            // La somme des paiements doit couvrir le total du ticket.
            $paid = round(collect($data['payments'])->sum(fn ($p) => (float) $p['amount']), 2);
            if ($paid < (float) $document->total) {
                throw ValidationException::withMessages([
                    'payments' => 'Le montant encaissé ('.$paid.') ne couvre pas le total du ticket ('.$document->total.').',
                ]);
            }

            $this->documents->finalize($document);

            foreach ($data['payments'] as $payment) {
                $this->documents->registerPayment($document, [
                    'amount' => $payment['amount'],
                    'method' => $payment['method'],
                    'reference' => $payment['reference'] ?? null,
                    'paid_at' => now()->toDateString(),
                    'currency' => $company->currency,
                ], $user);
            }

            // Mise à jour des compteurs de la session
            $totals = $session->totals_by_method ?? [];
            foreach ($data['payments'] as $payment) {
                $method = $payment['method'];
                $totals[$method] = round(($totals[$method] ?? 0) + (float) $payment['amount'], 2);
            }

            $session->update([
                'tickets_count' => $session->tickets_count + 1,
                'total_sales' => round((float) $session->total_sales + (float) $document->total, 2),
                'totals_by_method' => $totals,
            ]);

            return $document;
        });

        return redirect()
            ->route('pos.index', ['ticket' => $document->id])
            ->with('success', 'Ticket '.$document->number.' encaissé.');
    }

    /** Clôture la session : comptage des espèces + calcul de l'écart. */
    public function closeSession(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'counted_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $session = $this->openSessionFor($request);
        if (! $session) {
            return redirect()->route('pos.index')->with('error', 'Aucune session de caisse ouverte.');
        }

        $expected = round(
            (float) $session->opening_float + (float) (($session->totals_by_method['cash'] ?? 0)),
            2
        );

        $session->update([
            'status' => 'closed',
            'expected_cash' => $expected,
            'counted_cash' => $data['counted_cash'],
            'difference' => round((float) $data['counted_cash'] - $expected, 2),
            'closed_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('pos.report', $session)
            ->with('success', 'Caisse clôturée — rapport Z généré.');
    }

    /** Rapport Z d'une session de caisse. */
    public function report(Request $request, PosSession $session): Response
    {
        abort_unless($session->company_id === $request->user()->current_company_id, 403);

        $session->load('user:id,name');

        return Inertia::render('Pos/Report', [
            'session' => $session,
            'currency' => $request->user()->currentCompany->currency,
        ]);
    }

    /** Rapport X : statistiques intraday sans clôture de session. */
    public function reportX(Request $request, PosSession $session): \Illuminate\Http\JsonResponse|\Inertia\Response
    {
        abort_unless($session->company_id === $request->user()->current_company_id, 403);
        abort_unless($session->status === 'open', 422);

        $session->load('user:id,name');

        $stats = [
            'session_id' => $session->id,
            'cashier' => $session->cashier_name ?? $session->user?->name,
            'opened_at' => $session->opened_at,
            'tickets_count' => $session->tickets_count ?? 0,
            'total_sales' => (float) ($session->total_sales ?? 0),
            'totals_by_method' => $session->totals_by_method ?? [],
            'opening_float' => (float) ($session->opening_float ?? 0),
            'status' => 'open',
        ];

        if ($request->expectsJson()) {
            return response()->json($stats);
        }

        return Inertia::render('Pos/ReportX', [
            'stats' => $stats,
            'currency' => $request->user()->currentCompany->currency,
        ]);
    }

    /** Session ouverte du caissier courant (scopée société). */
    private function openSessionFor(Request $request): ?PosSession
    {
        return PosSession::open()
            ->where('company_id', $request->user()->current_company_id)
            ->where('user_id', $request->user()->id)
            ->latest('opened_at')
            ->first();
    }
}
