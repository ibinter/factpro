<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentPayment;

/**
 * Export FEC — Fichier des Écritures Comptables (norme française,
 * art. A47 A-1 du LPF) : fichier texte tabulé, EOL CRLF, 18 colonnes.
 *
 * Journal VE (ventes) : 3 lignes équilibrées par document facturable
 * finalisé (411 Clients / 707 Ventes / 44571 TVA collectée).
 * Journal BQ (banque) : 2 lignes par encaissement (512 / 411).
 */
class FecExportService
{
    /** En-tête réglementaire du FEC (ordre et libellés imposés). */
    public const HEADER = [
        'JournalCode', 'JournalLib', 'EcritureNum', 'EcritureDate',
        'CompteNum', 'CompteLib', 'CompAuxNum', 'CompAuxLib',
        'PieceRef', 'PieceDate', 'EcritureLib', 'Debit', 'Credit',
        'EcritureLet', 'DateLet', 'ValidDate', 'Montantdevise', 'Idevise',
    ];

    /** Nom de fichier conventionnel : {SIREN}FEC{AAAA1231}.txt */
    public function filename(Company $company, int $year): string
    {
        $siren = preg_replace('/[^A-Za-z0-9]/', '', (string) $company->tax_id);

        return ($siren !== '' ? $siren : 'FACTPRO').'FEC'.$year.'1231.txt';
    }

    /** Génère le contenu texte complet du FEC pour l'exercice donné. */
    public function generate(Company $company, int $year): string
    {
        $rows = [self::HEADER];
        $entryNumber = 0;

        // ---- Journal VE : ventes ------------------------------------------
        $documents = Document::query()
            ->where('company_id', $company->id)
            ->whereIn('type', AccountingService::BILLABLE_TYPES)
            ->whereNotNull('finalized_at')
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('issue_date', [$year.'-01-01', $year.'-12-31'])
            ->with('customer:id,name')
            ->orderBy('issue_date')
            ->orderBy('number')
            ->get();

        foreach ($documents as $document) {
            $entryNumber++;
            $isCredit = $document->type === 'credit_note';

            $ttc = round(abs((float) $document->total), 2);
            $ht = round(abs((float) $document->subtotal - (float) $document->discount_amount), 2);
            $tva = round(abs((float) $document->tax_amount), 2);

            $clientName = $document->customer?->name ?? 'Client divers';
            $auxNum = 'CLI'.str_pad((string) ($document->customer_id ?? 0), 6, '0', STR_PAD_LEFT);
            $label = trim($document->type_label.' '.$document->number.' '.$clientName);
            $date = $document->issue_date->format('Ymd');
            $validDate = $document->finalized_at->format('Ymd');
            $currency = $document->currency ?: $company->currency ?: 'XOF';

            $base = [
                'journal_code' => 'VE',
                'journal_lib' => 'Journal des ventes',
                'num' => $entryNumber,
                'date' => $date,
                'piece_ref' => $document->number,
                'piece_date' => $date,
                'lib' => $label,
                'valid_date' => $validDate,
                'currency' => $currency,
            ];

            // 411 Clients — TTC (débit facture / crédit avoir)
            $rows[] = $this->line($base, '411', 'Clients', $auxNum, $clientName,
                $isCredit ? 0.0 : $ttc, $isCredit ? $ttc : 0.0, $ttc);

            // 707 Ventes de marchandises — HT (crédit facture / débit avoir)
            $rows[] = $this->line($base, '707', 'Ventes de marchandises', '', '',
                $isCredit ? $ht : 0.0, $isCredit ? 0.0 : $ht, $ht);

            // 44571 TVA collectée (uniquement si TVA ≠ 0)
            if ($tva > 0.0) {
                $rows[] = $this->line($base, '44571', 'TVA collectée', '', '',
                    $isCredit ? $tva : 0.0, $isCredit ? 0.0 : $tva, $tva);
            }
        }

        // ---- Journal BQ : encaissements -----------------------------------
        $payments = DocumentPayment::query()
            ->where('company_id', $company->id)
            ->whereBetween('paid_at', [$year.'-01-01', $year.'-12-31'])
            ->whereHas('document', function ($query) {
                $query->whereIn('type', AccountingService::BILLABLE_TYPES)
                    ->whereNotNull('finalized_at')
                    ->whereNotIn('status', ['draft', 'cancelled']);
            })
            ->with(['document.customer:id,name'])
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();

        foreach ($payments as $payment) {
            $entryNumber++;
            $document = $payment->document;

            $amount = round(abs((float) $payment->amount), 2);
            $clientName = $document?->customer?->name ?? 'Client divers';
            $auxNum = 'CLI'.str_pad((string) ($document?->customer_id ?? 0), 6, '0', STR_PAD_LEFT);
            $pieceRef = $payment->reference ?: ($document?->number ?? 'REG-'.$payment->id);
            $label = trim('Règlement '.($document?->number ?? '').' '.$clientName);
            $date = $payment->paid_at->format('Ymd');
            $currency = $payment->currency ?: $company->currency ?: 'XOF';

            $base = [
                'journal_code' => 'BQ',
                'journal_lib' => 'Banque',
                'num' => $entryNumber,
                'date' => $date,
                'piece_ref' => $pieceRef,
                'piece_date' => $date,
                'lib' => $label,
                'valid_date' => $date,
                'currency' => $currency,
            ];

            // 512 Banque — débit / 411 Clients — crédit
            $rows[] = $this->line($base, '512', 'Banque', '', '', $amount, 0.0, $amount);
            $rows[] = $this->line($base, '411', 'Clients', $auxNum, $clientName, 0.0, $amount, $amount);
        }

        return implode("\r\n", array_map(
            fn (array $row) => implode("\t", $row),
            $rows
        ))."\r\n";
    }

    /** Construit une ligne FEC (18 colonnes) à partir de l'écriture de base. */
    private function line(
        array $base,
        string $accountNumber,
        string $accountLabel,
        string $auxNumber,
        string $auxLabel,
        float $debit,
        float $credit,
        float $currencyAmount,
    ): array {
        return [
            $base['journal_code'],
            $base['journal_lib'],
            (string) $base['num'],
            $base['date'],
            $accountNumber,
            $accountLabel,
            $auxNumber,
            $this->clean($auxLabel),
            $base['piece_ref'],
            $base['piece_date'],
            $this->clean($base['lib']),
            $this->amount($debit),
            $this->amount($credit),
            '', // EcritureLet
            '', // DateLet
            $base['valid_date'],
            $this->amount($currencyAmount),
            $base['currency'],
        ];
    }

    /** Format monétaire FEC : virgule décimale, pas de séparateur de milliers. */
    private function amount(float $value): string
    {
        return number_format($value, 2, ',', '');
    }

    /** Neutralise tabulations et retours ligne dans les libellés. */
    private function clean(string $value): string
    {
        return trim(preg_replace('/[\t\r\n]+/', ' ', $value) ?? '');
    }
}
