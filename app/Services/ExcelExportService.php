<?php

namespace App\Services;

use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelExportService
{
    private string $brandColor = 'FF0062CC';
    private string $goldColor  = 'FFF0C040';
    private string $grayColor  = 'FFF3F4F6';

    // -------------------------------------------------------------------------
    // Public exports
    // -------------------------------------------------------------------------

    public function exportCustomers(Collection $customers, string $companyName): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Clients');

        $this->addCompanyHeader($sheet, $companyName, 'Export Clients');

        $headers = ['N°', 'Nom', 'Email', 'Téléphone', 'Adresse', 'Ville', 'Pays', 'SIRET/RCCM', 'N° TVA', 'Solde dû', 'Nb factures', 'Créé le'];
        $this->writeRow($sheet, 4, $headers);
        $this->applyHeaderStyle($sheet, 'A4:L4', $this->brandColor);

        $row = 5;
        $num = 1;
        foreach ($customers as $customer) {
            $sheet->setCellValue("A{$row}", $num++);
            $sheet->setCellValue("B{$row}", $customer->name ?? '');
            $sheet->setCellValue("C{$row}", $customer->email ?? '');
            $sheet->setCellValue("D{$row}", $customer->phone ?? '');
            $sheet->setCellValue("E{$row}", $customer->address ?? '');
            $sheet->setCellValue("F{$row}", $customer->city ?? '');
            $sheet->setCellValue("G{$row}", $customer->country ?? '');
            $sheet->setCellValue("H{$row}", $customer->siret ?? $customer->rccm ?? '');
            $sheet->setCellValue("I{$row}", $customer->tax_id ?? '');
            $sheet->setCellValue("J{$row}", round((float)($customer->outstanding ?? 0), 2));
            $sheet->setCellValue("K{$row}", (int)($customer->documents_count ?? 0));
            $sheet->setCellValue("L{$row}", $customer->created_at?->format('d/m/Y') ?? '');
            $row++;
        }

        if ($row > 5) {
            $this->applyDataStyle($sheet, 5, $row - 1, 12);
        }

        $this->autoSizeColumns($sheet, 'A', 'L');

        return $spreadsheet;
    }

    public function exportProducts(Collection $products, string $companyName): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Produits');

        $this->addCompanyHeader($sheet, $companyName, 'Export Produits');

        $headers = ['N°', 'Référence', 'Nom', 'Description', 'Prix HT', 'Unité', 'TVA %', 'Prix TTC', 'Stock', 'Valeur stock', 'Actif'];
        $this->writeRow($sheet, 4, $headers);
        $this->applyHeaderStyle($sheet, 'A4:K4', $this->brandColor);

        $row = 5;
        $num = 1;
        foreach ($products as $product) {
            $ht  = (float)($product->price ?? 0);
            $tva = (float)($product->tax_rate ?? 0);
            $ttc = round($ht * (1 + $tva / 100), 2);
            $qty = (float)($product->stock_quantity ?? 0);

            $sheet->setCellValue("A{$row}", $num++);
            $sheet->setCellValue("B{$row}", $product->sku ?? '');
            $sheet->setCellValue("C{$row}", $product->name ?? '');
            $sheet->setCellValue("D{$row}", $product->description ?? '');
            $sheet->setCellValue("E{$row}", round($ht, 2));
            $sheet->setCellValue("F{$row}", $product->unit ?? '');
            $sheet->setCellValue("G{$row}", round($tva, 2));
            $sheet->setCellValue("H{$row}", $ttc);
            $sheet->setCellValue("I{$row}", round($qty, 2));
            $sheet->setCellValue("J{$row}", round($ht * $qty, 2));
            $sheet->setCellValue("K{$row}", ($product->is_active ?? true) ? 'Oui' : 'Non');
            $row++;
        }

        if ($row > 5) {
            $this->applyDataStyle($sheet, 5, $row - 1, 11);
        }

        $this->autoSizeColumns($sheet, 'A', 'K');

        return $spreadsheet;
    }

    public function exportDocuments(Collection $documents, string $companyName, string $type = 'all'): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Documents');

        $label = match ($type) {
            'invoice'     => 'Export Factures',
            'quote'       => 'Export Devis',
            'credit_note' => 'Export Avoirs',
            default       => 'Export Documents',
        };
        $this->addCompanyHeader($sheet, $companyName, $label);

        $statusLabels = [
            'draft' => 'Brouillon', 'sent' => 'Envoyé', 'viewed' => 'Vu',
            'accepted' => 'Accepté', 'rejected' => 'Refusé', 'partial' => 'Partiel',
            'paid' => 'Payé', 'overdue' => 'En retard', 'cancelled' => 'Annulé',
            'converted' => 'Converti',
        ];

        $headers = ['Numéro', 'Type', 'Client', 'Date', 'Échéance', 'Statut', 'HT', 'TVA', 'Total TTC', 'Payé', 'Solde dû', 'Créé le'];
        $this->writeRow($sheet, 4, $headers);
        $this->applyHeaderStyle($sheet, 'A4:L4', $this->brandColor);

        $row = 5;
        foreach ($documents as $document) {
            $total   = (float)($document->total ?? 0);
            $paid    = (float)($document->amount_paid ?? 0);
            $typeMap = \App\Models\Document::TYPES[$document->type]['label'] ?? $document->type;

            $sheet->setCellValue("A{$row}", $document->number ?? '');
            $sheet->setCellValue("B{$row}", $typeMap);
            $sheet->setCellValue("C{$row}", $document->customer?->name ?? '');
            $sheet->setCellValue("D{$row}", $document->issue_date?->format('d/m/Y') ?? '');
            $sheet->setCellValue("E{$row}", $document->due_date?->format('d/m/Y') ?? '');
            $sheet->setCellValue("F{$row}", $statusLabels[$document->status] ?? $document->status ?? '');
            $sheet->setCellValue("G{$row}", round((float)($document->subtotal ?? 0), 2));
            $sheet->setCellValue("H{$row}", round((float)($document->tax_amount ?? 0), 2));
            $sheet->setCellValue("I{$row}", round($total, 2));
            $sheet->setCellValue("J{$row}", round($paid, 2));
            $sheet->setCellValue("K{$row}", round($total - $paid, 2));
            $sheet->setCellValue("L{$row}", $document->created_at?->format('d/m/Y') ?? '');
            $row++;
        }

        if ($row > 5) {
            $this->applyDataStyle($sheet, 5, $row - 1, 12);
        }

        $this->autoSizeColumns($sheet, 'A', 'L');

        return $spreadsheet;
    }

    public function exportMonthlyRevenue(array $monthlyData, string $companyName, int $year): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // -- Onglet 1 : Données -------------------------------------------------
        $data = $spreadsheet->getActiveSheet();
        $data->setTitle('Données');

        $this->addCompanyHeader($data, $companyName, "CA mensuel {$year}");

        $headers = ['Mois', 'CA HT', 'TVA', 'CA TTC', 'Nb factures', 'Nb clients'];
        $this->writeRow($data, 4, $headers);
        $this->applyHeaderStyle($data, 'A4:F4', $this->brandColor);

        $row = 5;
        foreach ($monthlyData as $m) {
            $data->setCellValue("A{$row}", $m['label'] ?? '');
            $data->setCellValue("B{$row}", round((float)($m['subtotal'] ?? 0), 2));
            $data->setCellValue("C{$row}", round((float)($m['tax_amount'] ?? 0), 2));
            $data->setCellValue("D{$row}", round((float)($m['total'] ?? 0), 2));
            $data->setCellValue("E{$row}", (int)($m['invoices_count'] ?? 0));
            $data->setCellValue("F{$row}", (int)($m['customers_count'] ?? 0));
            $row++;
        }

        if ($row > 5) {
            $this->applyDataStyle($data, 5, $row - 1, 6);
        }

        $this->autoSizeColumns($data, 'A', 'F');

        // -- Onglet 2 : Résumé --------------------------------------------------
        $summary = $spreadsheet->createSheet();
        $summary->setTitle('Résumé');

        $this->addCompanyHeader($summary, $companyName, "Résumé annuel {$year}");

        $totalHT   = array_sum(array_column($monthlyData, 'subtotal'));
        $totalTVA  = array_sum(array_column($monthlyData, 'tax_amount'));
        $totalTTC  = array_sum(array_column($monthlyData, 'total'));
        $totalInv  = array_sum(array_column($monthlyData, 'invoices_count'));
        $months    = count(array_filter($monthlyData, fn($m) => ($m['total'] ?? 0) > 0));
        $avgTTC    = $months > 0 ? $totalTTC / $months : 0;
        $bestMonth = collect($monthlyData)->sortByDesc('total')->first();

        $this->writeRow($summary, 4, ['Indicateur', 'Valeur']);
        $this->applyHeaderStyle($summary, 'A4:B4', $this->goldColor);

        $summaryRows = [
            ['Total CA HT',         round($totalHT, 2)],
            ['Total TVA',           round($totalTVA, 2)],
            ['Total CA TTC',        round($totalTTC, 2)],
            ['Nb total factures',   (int)$totalInv],
            ['Mois actifs',         $months],
            ['Moyenne mensuelle TTC', round($avgTTC, 2)],
            ['Meilleur mois',       $bestMonth['label'] ?? ''],
            ['CA meilleur mois',    round((float)($bestMonth['total'] ?? 0), 2)],
        ];

        $row = 5;
        foreach ($summaryRows as [$label, $value]) {
            $summary->setCellValue("A{$row}", $label);
            $summary->setCellValue("B{$row}", $value);
            $row++;
        }

        $this->applyDataStyle($summary, 5, $row - 1, 2);
        $this->autoSizeColumns($summary, 'A', 'B');

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function exportFecXlsx(Collection $entries, string $companyName): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('FEC');

        $this->addCompanyHeader($sheet, $companyName, 'Journal FEC — DGFIP');

        $headers = FecExportService::HEADER;
        $this->writeRow($sheet, 4, $headers);
        $this->applyHeaderStyle($sheet, 'A4:R4', $this->brandColor);

        $row = 5;
        foreach ($entries as $entry) {
            $col = 'A';
            foreach ($headers as $field) {
                $sheet->setCellValue("{$col}{$row}", $entry[$field] ?? '');
                $col++;
            }
            $row++;
        }

        if ($row > 5) {
            $this->applyDataStyle($sheet, 5, $row - 1, count($headers));
        }

        $this->autoSizeColumns($sheet, 'A', 'R');

        return $spreadsheet;
    }

    public function download(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function addCompanyHeader(Worksheet $sheet, string $companyName, string $exportType): void
    {
        // Ligne 1 : IBIG FactPro + nom société
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'IBIG FactPro — '.$companyName);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => $this->brandColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Ligne 2 : type d'export + date
        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', $exportType.' — Généré le '.now()->format('d/m/Y à H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // Ligne 3 : vide (séparateur)
        $sheet->setCellValue('A3', '');
    }

    private function applyHeaderStyle(Worksheet $sheet, string $range, string $color): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => $color],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFD1D5DB'],
                ],
            ],
        ]);

        $sheet->getRowDimension(
            (int) preg_replace('/[^0-9]/', '', explode(':', $range)[0])
        )->setRowHeight(20);
    }

    private function applyDataStyle(Worksheet $sheet, int $startRow, int $endRow, int $cols): void
    {
        $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols);

        for ($r = $startRow; $r <= $endRow; $r++) {
            $bgColor = ($r % 2 === 0) ? 'FFF9FAFB' : 'FFFFFFFF';
            $range   = "A{$r}:{$endCol}{$r}";

            $sheet->getStyle($range)->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => $bgColor],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_HAIR,
                        'color'       => ['argb' => 'FFE5E7EB'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }
    }

    private function autoSizeColumns(Worksheet $sheet, string $fromCol, string $toCol): void
    {
        $fromIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($fromCol);
        $toIndex   = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($toCol);

        for ($i = $fromIndex; $i <= $toIndex; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function writeRow(Worksheet $sheet, int $row, array $values): void
    {
        $col = 'A';
        foreach ($values as $value) {
            $sheet->setCellValue("{$col}{$row}", $value);
            $col++;
        }
    }
}
