<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DoubleEntryController extends Controller
{
    /** Plan comptable groupé par type. */
    public function accounts(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $accounts = Account::where('company_id', $company->id)
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        return Inertia::render('Accounting/Accounts', [
            'accountsByType' => $accounts,
            'currency' => $company->currency ?? 'XOF',
        ]);
    }

    /** Créer un compte. */
    public function createAccount(Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'code'      => ['required', 'string', 'max:20'],
            'name'      => ['required', 'string', 'max:255'],
            'type'      => ['required', 'in:asset,liability,equity,revenue,expense'],
            'category'  => ['nullable', 'string', 'max:255'],
            'parent_id' => ['nullable', 'integer', 'exists:accounts,id'],
        ]);

        Account::create(array_merge($data, ['company_id' => $company->id]));

        return redirect()->route('accounting.accounts')->with('success', 'Compte créé avec succès.');
    }

    /** Journal des écritures. */
    public function journal(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $query = JournalEntry::where('company_id', $company->id)
            ->with(['lines.account'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $entries = $query->paginate(30)->withQueryString();

        $accounts = Account::where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'type']);

        return Inertia::render('Accounting/Journal', [
            'entries'  => $entries,
            'accounts' => $accounts,
            'filters'  => $request->only(['date_from', 'date_to', 'type']),
            'currency' => $company->currency ?? 'XOF',
        ]);
    }

    /** Créer une écriture manuelle. */
    public function createEntry(Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $data = $request->validate([
            'date'        => ['required', 'date'],
            'description' => ['required', 'string', 'max:500'],
            'reference'   => ['nullable', 'string', 'max:100'],
            'lines'       => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', 'exists:accounts,id'],
            'lines.*.label'      => ['required', 'string', 'max:255'],
            'lines.*.debit'      => ['required', 'numeric', 'min:0'],
            'lines.*.credit'     => ['required', 'numeric', 'min:0'],
        ]);

        $totalDebit  = collect($data['lines'])->sum('debit');
        $totalCredit = collect($data['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors([
                'lines' => 'L\'écriture n\'est pas équilibrée : total débits ('.number_format($totalDebit, 2).') ≠ total crédits ('.number_format($totalCredit, 2).').',
            ]);
        }

        DB::transaction(function () use ($data, $company, $totalDebit, $totalCredit) {
            $entry = JournalEntry::create([
                'company_id'   => $company->id,
                'date'         => $data['date'],
                'description'  => $data['description'],
                'reference'    => $data['reference'] ?? null,
                'type'         => 'manual',
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            foreach ($data['lines'] as $line) {
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'label'            => $line['label'],
                    'debit'            => $line['debit'],
                    'credit'           => $line['credit'],
                ]);
            }
        });

        return redirect()->route('accounting.journal')->with('success', 'Écriture créée avec succès.');
    }

    /** Balance de vérification (grand livre agrégé). */
    public function trialBalance(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $accounts = Account::where('company_id', $company->id)
            ->orderBy('code')
            ->get();

        $totals = JournalLine::whereHas('journalEntry', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })
        ->select('account_id', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
        ->groupBy('account_id')
        ->get()
        ->keyBy('account_id');

        $rows = $accounts->map(function ($account) use ($totals) {
            $t      = $totals->get($account->id);
            $debit  = $t ? (float) $t->total_debit  : 0;
            $credit = $t ? (float) $t->total_credit : 0;
            $balance = $debit - $credit;

            return [
                'id'       => $account->id,
                'code'     => $account->code,
                'name'     => $account->name,
                'type'     => $account->type,
                'type_label' => $account->getTypeLabel(),
                'debit'    => $debit,
                'credit'   => $credit,
                'balance'  => $balance,
            ];
        })->filter(fn ($r) => $r['debit'] > 0 || $r['credit'] > 0)->values();

        return Inertia::render('Accounting/TrialBalance', [
            'rows'     => $rows,
            'currency' => $company->currency ?? 'XOF',
        ]);
    }

    /** Importer le plan OHADA de base. */
    public function importDefaultPlan(Request $request): RedirectResponse
    {
        $company = $request->user()->currentCompany;

        $existing = Account::where('company_id', $company->id)->count();
        if ($existing > 0) {
            return back()->with('info', 'Un plan comptable existe déjà pour cette entreprise.');
        }

        $plan = [
            ['code' => '1000', 'name' => 'Capital',                  'type' => 'equity',    'category' => 'Capitaux propres'],
            ['code' => '1200', 'name' => 'Résultat de l\'exercice',   'type' => 'equity',    'category' => 'Capitaux propres'],
            ['code' => '2000', 'name' => 'Immobilisations',           'type' => 'asset',     'category' => 'Immobilisations'],
            ['code' => '3000', 'name' => 'Stocks',                    'type' => 'asset',     'category' => 'Stocks'],
            ['code' => '4000', 'name' => 'Fournisseurs',              'type' => 'liability', 'category' => 'Tiers'],
            ['code' => '4100', 'name' => 'Clients',                   'type' => 'asset',     'category' => 'Tiers'],
            ['code' => '4500', 'name' => 'TVA collectée',             'type' => 'liability', 'category' => 'Fiscalité'],
            ['code' => '4510', 'name' => 'TVA déductible',            'type' => 'asset',     'category' => 'Fiscalité'],
            ['code' => '5000', 'name' => 'Banque',                    'type' => 'asset',     'category' => 'Trésorerie'],
            ['code' => '5100', 'name' => 'Caisse',                    'type' => 'asset',     'category' => 'Trésorerie'],
            ['code' => '6000', 'name' => 'Achats marchandises',       'type' => 'expense',   'category' => 'Charges d\'exploitation'],
            ['code' => '6100', 'name' => 'Services extérieurs',       'type' => 'expense',   'category' => 'Charges d\'exploitation'],
            ['code' => '7000', 'name' => 'Ventes marchandises',       'type' => 'revenue',   'category' => 'Produits d\'exploitation'],
            ['code' => '7100', 'name' => 'Prestations de services',   'type' => 'revenue',   'category' => 'Produits d\'exploitation'],
        ];

        $count = 0;
        foreach ($plan as $item) {
            Account::create(array_merge($item, [
                'company_id' => $company->id,
                'is_system'  => true,
                'is_active'  => true,
            ]));
            $count++;
        }

        return back()->with('success', "{$count} comptes OHADA importés avec succès.");
    }
}
