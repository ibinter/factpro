<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Export comptable vers Sage 100, QuickBooks IIF et Pennylane JSON.
 * Phase 14 — IBIG FactPro.
 */
class AccountingExportService
{
    public const DEFAULT_ACCOUNTS = [
        'client'         => '411',
        'supplier'       => '401',
        'sales_product'  => '701000',
        'sales_service'  => '706000',
        'vat_collected'  => '445710',
        'vat_deductible' => '445660',
        'bank'           => '512000',
        'purchase'       => '607000',
    ];

    // -----------------------------------------------------------------------
    // SAGE 100
    // -----------------------------------------------------------------------

    /**
     * Génère le contenu CSV/TXT compatible Sage 100 Import Paramétrable.
     */
    public function exportSage(int $companyId, Carbon $from, Carbon $to): string
    {
        $documents = $this->getDocuments($companyId, $from, $to);

        $header = 'JournalCode;JournalLib;EcritureNum;EcritureDate;CompteNum;CompteLib;'
            .'CompteAuxNum;CompteAuxLib;PieceRef;PieceDate;EcritureLib;Debit;Credit;'
            .'EcritureLet;DateLet;ValidDate;MontantDevise;Idevise';

        $lines = [$header];

        foreach ($documents as $doc) {
            $clientAccount = $this->clientAccount($doc->customer_id);
            $clientName    = $doc->customer?->name ?? 'Client divers';
            $clientCode    = 'C'.str_pad((string) ($doc->customer_id ?? 0), 3, '0', STR_PAD_LEFT);
            $date          = $doc->issue_date?->format('Ymd') ?? now()->format('Ymd');
            $validDate     = $date;
            $ref           = $doc->number;
            $label         = 'Facture '.$ref;
            $ttc           = number_format((float) $doc->total, 2, '.', '');
            $ht            = number_format((float) $doc->subtotal, 2, '.', '');
            $tva           = number_format((float) $doc->tax_amount, 2, '.', '');
            $currency      = $doc->currency ?? 'EUR';
            $salesAccount  = $this->salesAccount($doc);

            // Ligne client (débit TTC)
            $lines[] = implode(';', [
                'VT', 'Ventes', $ref, $date,
                $clientAccount, 'Clients',
                $clientCode, $clientName,
                $ref, $date, $label,
                $ttc, '0.00',
                '', '', $validDate,
                $ttc, $currency,
            ]);

            // Ligne vente HT (crédit)
            $lines[] = implode(';', [
                'VT', 'Ventes', $ref, $date,
                $salesAccount, 'Ventes',
                '', '',
                $ref, $date, $label,
                '0.00', $ht,
                '', '', $validDate,
                $ht, $currency,
            ]);

            // Ligne TVA (crédit) — uniquement si TVA > 0
            if ((float) $doc->tax_amount > 0) {
                $lines[] = implode(';', [
                    'VT', 'Ventes', $ref, $date,
                    self::DEFAULT_ACCOUNTS['vat_collected'], 'TVA collectée',
                    '', '',
                    $ref, $date, 'TVA '.$ref,
                    '0.00', $tva,
                    '', '', $validDate,
                    $tva, $currency,
                ]);
            }
        }

        return implode("\r\n", $lines);
    }

    // -----------------------------------------------------------------------
    // QUICKBOOKS IIF
    // -----------------------------------------------------------------------

    public function exportQuickBooks(int $companyId, Carbon $from, Carbon $to): string
    {
        $documents = $this->getDocuments($companyId, $from, $to);

        $lines = [];
        $lines[] = "!TRNS\tTRNSID\tTRNSTYPE\tDATE\tACCNT\tNAME\tAMOUNT\tDOCNUM\tMEMO";
        $lines[] = "!SPL\tSPLID\tTRNSTYPE\tDATE\tACCNT\tNAME\tAMOUNT\tDOCNUM\tMEMO";
        $lines[] = "!ENDTRNS";

        $id = 1;
        foreach ($documents as $doc) {
            $clientName = $doc->customer?->name ?? 'Client divers';
            $date       = $doc->issue_date?->format('m/d/Y') ?? now()->format('m/d/Y');
            $ref        = $doc->number;
            $ttc        = number_format((float) $doc->total, 2, '.', '');
            $ht         = number_format(-(float) $doc->subtotal, 2, '.', '');
            $tva        = number_format(-(float) $doc->tax_amount, 2, '.', '');
            $salesAcct  = (float) $doc->tax_amount > 0 ? 'Sales' : 'Sales';

            $lines[] = implode("\t", [
                'TRNS', $id++, 'INVOICE', $date,
                'Accounts Receivable', $clientName,
                $ttc, $ref, 'Facture',
            ]);

            $lines[] = implode("\t", [
                'SPL', $id++, 'INVOICE', $date,
                $salesAcct, $clientName,
                $ht, $ref, '',
            ]);

            if ((float) $doc->tax_amount > 0) {
                $lines[] = implode("\t", [
                    'SPL', $id++, 'INVOICE', $date,
                    'Tax Payable', $clientName,
                    $tva, $ref, 'TVA',
                ]);
            }

            $lines[] = 'ENDTRNS';
        }

        return implode("\n", $lines);
    }

    // -----------------------------------------------------------------------
    // PENNYLANE JSON
    // -----------------------------------------------------------------------

    public function exportPennylane(int $companyId, Carbon $from, Carbon $to): array
    {
        $documents = $this->getDocuments($companyId, $from, $to);

        $entries = [];

        foreach ($documents as $doc) {
            $clientName   = $doc->customer?->name ?? 'Client divers';
            $date         = $doc->issue_date?->format('Y-m-d') ?? now()->format('Y-m-d');
            $ref          = $doc->number;
            $ttc          = round((float) $doc->total, 2);
            $ht           = round((float) $doc->subtotal, 2);
            $tva          = round((float) $doc->tax_amount, 2);
            $clientAcct   = $this->clientAccount($doc->customer_id);
            $salesAcct    = $this->salesAccount($doc);

            $linesList = [
                [
                    'account_number' => $clientAcct,
                    'label'          => $clientName,
                    'debit'          => $ttc,
                    'credit'         => 0,
                ],
                [
                    'account_number' => $salesAcct,
                    'label'          => 'Ventes',
                    'debit'          => 0,
                    'credit'         => $ht,
                ],
            ];

            if ($tva > 0) {
                $linesList[] = [
                    'account_number' => self::DEFAULT_ACCOUNTS['vat_collected'],
                    'label'          => 'TVA collectée',
                    'debit'          => 0,
                    'credit'         => $tva,
                ];
            }

            $entries[] = [
                'date'      => $date,
                'label'     => 'Facture '.$ref.' - '.$clientName,
                'reference' => $ref,
                'currency'  => $doc->currency ?? 'EUR',
                'lines'     => $linesList,
            ];
        }

        return ['ledger_entries' => $entries];
    }

    // -----------------------------------------------------------------------
    // Preview (10 premières lignes)
    // -----------------------------------------------------------------------

    public function preview(int $companyId, Carbon $from, Carbon $to, string $format): array
    {
        return match ($format) {
            'sage'       => $this->previewLines($this->exportSage($companyId, $from, $to), "\r\n"),
            'quickbooks' => $this->previewLines($this->exportQuickBooks($companyId, $from, $to), "\n"),
            'pennylane'  => $this->previewPennylane($companyId, $from, $to),
            default      => [],
        };
    }

    private function previewLines(string $content, string $sep): array
    {
        $lines = explode($sep, $content);

        return array_values(array_slice($lines, 0, 10));
    }

    private function previewPennylane(int $companyId, Carbon $from, Carbon $to): array
    {
        $data    = $this->exportPennylane($companyId, $from, $to);
        $entries = array_slice($data['ledger_entries'], 0, 10);
        $lines   = [];
        foreach ($entries as $entry) {
            $lines[] = json_encode($entry, JSON_UNESCAPED_UNICODE);
        }

        return $lines;
    }

    // -----------------------------------------------------------------------
    // Helpers privés
    // -----------------------------------------------------------------------

    private function getDocuments(int $companyId, Carbon $from, Carbon $to): Collection
    {
        return Document::with(['customer', 'lines'])
            ->where('company_id', $companyId)
            ->whereIn('type', ['invoice', 'credit_note'])
            ->whereNotNull('finalized_at')
            ->whereDate('issue_date', '>=', $from->toDateString())
            ->whereDate('issue_date', '<=', $to->toDateString())
            ->orderBy('issue_date')
            ->get();
    }

    private function clientAccount(?int $customerId): string
    {
        $suffix = str_pad((string) ($customerId ?? 0), 3, '0', STR_PAD_LEFT);

        return self::DEFAULT_ACCOUNTS['client'].$suffix;
    }

    /**
     * Détermine le compte de vente : produit (701xxx) ou service (706xxx).
     * Heuristique : si au moins une ligne a un type 'service', on utilise 706000.
     */
    private function salesAccount(Document $doc): string
    {
        $hasService = $doc->lines->contains(function ($line) {
            return isset($line->type) && $line->type === 'service';
        });

        return $hasService
            ? self::DEFAULT_ACCOUNTS['sales_service']
            : self::DEFAULT_ACCOUNTS['sales_product'];
    }
}
