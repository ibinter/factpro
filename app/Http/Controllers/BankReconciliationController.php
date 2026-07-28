<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\DocumentPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankReconciliationController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->currentCompany;

        $accounts = BankAccount::where('company_id', $company->id)
            ->withCount([
                'transactions',
                'transactions as reconciled_transactions_count' => fn ($q) => $q->where('is_reconciled', true),
            ])
            ->orderBy('name')
            ->get();

        return Inertia::render('Banking/Index', [
            'accounts' => $accounts,
        ]);
    }

    public function show(Request $request, BankAccount $account): Response
    {
        $company = $request->user()->currentCompany;

        abort_unless($account->company_id === $company->id, 403);

        $bankTransactions = $account->transactions()
            ->with('matchedPayment.document.customer')
            ->unreconciled()
            ->orderByDesc('date')
            ->paginate(50);

        // Paiements sans transaction bancaire associée
        $unmatchedPayments = DocumentPayment::where('company_id', $company->id)
            ->whereDoesntHave('bankTransaction')
            ->with('document.customer')
            ->orderByDesc('paid_at')
            ->get();

        return Inertia::render('Banking/Reconcile', [
            'account' => $account,
            'bankTransactions' => $bankTransactions,
            'unmatchedPayments' => $unmatchedPayments,
        ]);
    }

    public function createAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'swift_bic' => 'nullable|string|max:11',
            'account_number' => 'nullable|string|max:50',
            'currency' => 'required|string|size:3',
            'current_balance' => 'nullable|numeric',
        ]);

        $company = $request->user()->currentCompany;

        BankAccount::create(array_merge($data, [
            'company_id' => $company->id,
            'current_balance' => $data['current_balance'] ?? 0,
        ]));

        return redirect()->route('banking.index')->with('success', 'Compte bancaire créé.');
    }

    public function importCsv(Request $request, BankAccount $account): JsonResponse
    {
        $company = $request->user()->currentCompany;
        abort_unless($account->company_id === $company->id, 403);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('file')->getRealPath();
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $imported = 0;
        $separator = ',';

        // Detect separator from first line
        if (!empty($lines[0]) && substr_count($lines[0], ';') > substr_count($lines[0], ',')) {
            $separator = ';';
        }

        foreach ($lines as $index => $line) {
            // Skip header row if it starts with a non-numeric date
            if ($index === 0 && !preg_match('/^\d/', trim($line))) {
                continue;
            }

            $cols = str_getcsv($line, $separator);
            if (count($cols) < 3) {
                continue;
            }

            $rawDate = trim($cols[0]);
            $description = trim($cols[1]);
            $rawAmount = (float) str_replace([' ', "\u{00A0}", ','], ['', '', '.'], trim($cols[2]));

            // Try to parse date in common formats
            try {
                $date = \Carbon\Carbon::parse($rawDate)->toDateString();
            } catch (\Exception $e) {
                continue;
            }

            if ($rawAmount == 0 || empty($description)) {
                continue;
            }

            $type = $rawAmount > 0 ? 'credit' : 'debit';
            $amount = abs($rawAmount);

            $valueDate = null;
            if (isset($cols[3]) && trim($cols[3]) !== '') {
                try {
                    $valueDate = \Carbon\Carbon::parse(trim($cols[3]))->toDateString();
                } catch (\Exception $e) {
                    // ignore
                }
            }

            $reference = isset($cols[4]) ? trim($cols[4]) : null;

            BankTransaction::create([
                'bank_account_id' => $account->id,
                'date' => $date,
                'value_date' => $valueDate,
                'description' => mb_substr($description, 0, 500),
                'amount' => $amount,
                'type' => $type,
                'reference' => $reference,
                'is_reconciled' => false,
            ]);

            $imported++;
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'message' => "{$imported} transactions importées.",
        ]);
    }

    public function matchTransaction(Request $request): JsonResponse
    {
        $data = $request->validate([
            'transaction_id' => 'required|integer|exists:bank_transactions,id',
            'payment_id' => 'required|integer|exists:document_payments,id',
        ]);

        $company = $request->user()->currentCompany;

        $transaction = BankTransaction::findOrFail($data['transaction_id']);
        $payment = DocumentPayment::findOrFail($data['payment_id']);

        abort_unless($transaction->bankAccount->company_id === $company->id, 403);
        abort_unless($payment->company_id === $company->id, 403);

        $transaction->update([
            'matched_payment_id' => $payment->id,
            'matched_at' => now(),
            'is_reconciled' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction rapprochée avec succès.',
        ]);
    }

    public function unmatchTransaction(Request $request, BankTransaction $transaction): JsonResponse
    {
        $company = $request->user()->currentCompany;
        abort_unless($transaction->bankAccount->company_id === $company->id, 403);

        $transaction->update([
            'matched_payment_id' => null,
            'matched_at' => null,
            'is_reconciled' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Correspondance supprimée.',
        ]);
    }
}
