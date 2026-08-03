<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title><?php echo e($document->type_label ?? 'Document'); ?> <?php echo e($document->number ?? ''); ?></title>
    <style>
        @page {
            margin: 20px 45px 20px 80px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #333333;
            background: #ffffff;
        }

        /* ══ SIDEBAR LATÉRALE FIXE ══ */
        #sidebar {
            position: fixed;
            top: 0;
            left: -75px;
            width: 60px;
            height: 100%;
            background: <?php echo e($primaryColor); ?>;
            padding: 16px 0;
            z-index: 999;
        }

        #sidebar .sb-inner {
            display: table;
            width: 100%;
            height: 100%;
        }

        #sidebar .sb-top {
            display: table-row;
            vertical-align: top;
        }

        #sidebar .sb-top-cell {
            display: table-cell;
            text-align: center;
            padding-top: 8px;
            vertical-align: top;
        }

        #sidebar .sb-logo {
            max-width: 32px;
            max-height: 32px;
            display: block;
            margin: 0 auto 10px auto;
        }

        #sidebar .sb-company-name {
            display: block;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-height: 160px;
            margin: 0 auto;
        }

        #sidebar .sb-bottom {
            display: table-row;
            vertical-align: bottom;
        }

        #sidebar .sb-bottom-cell {
            display: table-cell;
            text-align: center;
            padding-bottom: 8px;
            vertical-align: bottom;
        }

        #sidebar .sb-page {
            color: rgba(255,255,255,0.75);
            font-size: 6.5px;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            display: block;
            margin: 0 auto;
        }

        /* ══ WATERMARK ══ */
        .watermark {
            position: fixed;
            top: 40%;
            left: 5%;
            width: 90%;
            transform: rotate(-35deg);
            font-size: 42px;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.18);
            z-index: 1000;
            text-align: center;
            pointer-events: none;
        }

        /* ══ EN-TÊTE DU DOCUMENT ══ */
        .doc-title-row {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            border-bottom: 2px solid <?php echo e($primaryColor); ?>;
            padding-bottom: 8px;
        }

        .doc-title-left {
            display: table-cell;
            vertical-align: bottom;
            width: 60%;
        }

        .doc-title-right {
            display: table-cell;
            vertical-align: bottom;
            text-align: right;
            width: 40%;
        }

        .doc-type-label {
            font-size: 20px;
            font-weight: bold;
            color: <?php echo e($primaryColor); ?>;
            text-transform: uppercase;
            line-height: 1.2;
        }

        .doc-number-label {
            font-size: 9px;
            color: #888888;
            margin-top: 2px;
        }

        .doc-status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            <?php
                $statusColors = [
                    'paid'      => ['bg' => '#D1FAE5', 'color' => '#065F46'],
                    'draft'     => ['bg' => '#F3F4F6', 'color' => '#6B7280'],
                    'sent'      => ['bg' => '#DBEAFE', 'color' => '#1E40AF'],
                    'overdue'   => ['bg' => '#FEE2E2', 'color' => '#991B1B'],
                    'cancelled' => ['bg' => '#FEF3C7', 'color' => '#92400E'],
                    'partial'   => ['bg' => '#EDE9FE', 'color' => '#5B21B6'],
                ];
                $sc = $statusColors[$document->status ?? ''] ?? ['bg' => '#F3F4F6', 'color' => '#374151'];
            ?>
            background: <?php echo e($sc['bg']); ?>;
            color: <?php echo e($sc['color']); ?>;
            border: 1px solid <?php echo e($sc['color']); ?>33;
        }

        /* ══ ROW MÉTA (3 CARDS) ══ */
        .meta-row {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }

        .meta-card {
            display: table-cell;
            border: 1px solid #E5EAF1;
            background: #ffffff;
            padding: 7px 10px;
            vertical-align: top;
        }

        .meta-card + .meta-card {
            border-left: none;
        }

        .meta-card-title {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #888888;
            margin-bottom: 3px;
        }

        .meta-card-value {
            font-size: 9px;
            font-weight: bold;
            color: #1a2332;
        }

        /* ══ ADRESSES ══ */
        .addresses {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }

        .col-doc-info {
            display: table-cell;
            width: 42%;
            vertical-align: top;
            padding-right: 10px;
        }

        .col-client {
            display: table-cell;
            width: 58%;
            vertical-align: top;
            padding-left: 10px;
        }

        .addr-card {
            background: #F8F9FA;
            border-radius: 3px;
            padding: 9px 11px;
            height: 100%;
        }

        .addr-card-client {
            background: #ffffff;
            border-left: 3px solid <?php echo e($primaryColor); ?>;
            border-radius: 0 3px 3px 0;
            padding: 9px 11px;
        }

        .addr-card-title {
            font-size: 7.5px;
            font-weight: bold;
            color: <?php echo e($primaryColor); ?>;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 3px;
        }

        .addr-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }

        .addr-label {
            display: table-cell;
            font-size: 8px;
            color: #888888;
            width: 40%;
        }

        .addr-value {
            display: table-cell;
            font-size: 8.5px;
            color: #222222;
            font-weight: bold;
        }

        .client-name {
            font-size: 11px;
            font-weight: bold;
            color: #002D5B;
            margin-bottom: 4px;
        }

        .client-detail {
            font-size: 8.5px;
            color: #555555;
            line-height: 1.6;
        }

        /* ══ OBJET ══ */
        .subject-bar {
            background: #EFF6FF;
            border-left: 3px solid <?php echo e($primaryColor); ?>;
            padding: 5px 10px;
            margin-bottom: 12px;
            font-size: 8.5px;
            color: #1e3a5f;
        }

        .subject-bar span {
            font-weight: bold;
        }

        /* ══ TABLEAU LIGNES ══ */
        table.lines-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.lines-table thead tr {
            background: <?php echo e($primaryColor); ?>1F;
            border-bottom: 2px solid <?php echo e($primaryColor); ?>;
        }

        table.lines-table thead th {
            padding: 6px 7px;
            font-size: 8px;
            font-weight: bold;
            color: <?php echo e($primaryColor); ?>;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        table.lines-table thead th.text-right {
            text-align: right;
        }

        table.lines-table tbody tr.row-even {
            background: #F8F9FA;
        }

        table.lines-table tbody tr.row-odd {
            background: #ffffff;
        }

        table.lines-table tbody td {
            padding: 5px 7px;
            font-size: 8.5px;
            color: #333333;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        table.lines-table tbody td.text-right {
            text-align: right;
        }

        table.lines-table tbody td.text-center {
            text-align: center;
        }

        table.lines-table tbody td.line-desc {
            color: #555555;
            font-size: 8px;
            font-style: italic;
        }

        table.lines-table tfoot tr {
            background: #F0F4FA;
        }

        table.lines-table tfoot td {
            padding: 5px 7px;
            font-size: 8.5px;
            font-weight: bold;
            color: #002D5B;
        }

        table.lines-table tfoot td.text-right {
            text-align: right;
        }

        .discount-badge {
            display: inline-block;
            background: #FEF3C7;
            color: #92400E;
            font-size: 7px;
            padding: 1px 4px;
            border-radius: 2px;
            margin-left: 3px;
        }

        /* ══ QR + TOTAUX ══ */
        .bottom-section {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .bottom-left {
            display: table-cell;
            width: 35%;
            vertical-align: top;
            padding-right: 10px;
        }

        .bottom-right {
            display: table-cell;
            width: 65%;
            vertical-align: top;
        }

        .qr-block {
            text-align: center;
        }

        .qr-block img {
            width: 80px;
            height: 80px;
            border: 1px solid #e5e7eb;
            padding: 3px;
        }

        .qr-label {
            font-size: 7px;
            color: #888888;
            margin-top: 3px;
            text-align: center;
        }

        table.totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        table.totals-table tr td {
            padding: 4px 8px;
            font-size: 8.5px;
            border-bottom: 1px solid #e5e7eb;
        }

        table.totals-table tr td:first-child {
            color: #555555;
            width: 55%;
        }

        table.totals-table tr td:last-child {
            text-align: right;
            font-weight: bold;
            color: #222222;
        }

        table.totals-table tr.total-grand td {
            background: <?php echo e($primaryColor); ?>;
            color: #ffffff;
            font-size: 10px;
            font-weight: bold;
            border-bottom: none;
        }

        table.totals-table tr.total-paid td {
            background: #D1FAE5;
            color: #065F46;
            font-size: 9px;
            font-weight: bold;
            border-bottom: none;
        }

        table.totals-table tr.total-due td {
            background: <?php echo e($accentColor); ?>;
            color: #ffffff;
            font-size: 10.5px;
            font-weight: bold;
            border-bottom: none;
        }

        /* ══ NOTES / CONDITIONS ══ */
        .notes-section {
            margin-bottom: 10px;
        }

        .notes-title {
            font-size: 8px;
            font-weight: bold;
            color: <?php echo e($primaryColor); ?>;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .notes-content {
            font-size: 8px;
            color: #555555;
            line-height: 1.6;
            background: #F8F9FA;
            border-left: 2px solid #e5e7eb;
            padding: 5px 8px;
            border-radius: 0 2px 2px 0;
        }

        /* ══ SIGNATURES ══ */
        .sig-section {
            margin-top: 16px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            margin-bottom: 14px;
        }

        .sig-section-title {
            font-size: 8px;
            font-weight: bold;
            color: <?php echo e($primaryColor); ?>;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 8px;
        }

        .sig-columns {
            display: table;
            width: 100%;
        }

        .sig-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 8px;
        }

        .sig-box:first-child {
            padding-left: 0;
            border-right: 1px solid #e5e7eb;
            padding-right: 16px;
        }

        .sig-box:last-child {
            padding-right: 0;
            padding-left: 16px;
        }

        .sig-box-label {
            font-size: 8px;
            font-weight: bold;
            color: #002D5B;
            margin-bottom: 5px;
        }

        .sig-photo-zone {
            height: 70px;
            border: 1px dashed #d1d5db;
            border-radius: 3px;
            background: #F8F9FA;
            display: block;
            text-align: center;
            margin-bottom: 5px;
            overflow: hidden;
        }

        .sig-photo-zone img {
            max-height: 68px;
            max-width: 100%;
        }

        .sig-photo-placeholder {
            height: 70px;
            line-height: 70px;
            font-size: 7.5px;
            color: #aaaaaa;
            text-align: center;
        }

        .sig-line {
            border-bottom: 1px solid #333333;
            margin-bottom: 3px;
        }

        .sig-meta {
            font-size: 7.5px;
            color: #888888;
        }

        .sig-mention {
            font-size: 7.5px;
            color: #555555;
            font-style: italic;
            margin-top: 3px;
        }

        /* ══ PIED DE PAGE ══ */
        .doc-footer {
            margin-top: 14px;
            border-top: 2px solid <?php echo e($accentColor); ?>;
            padding-top: 6px;
        }

        .doc-footer-inner {
            display: table;
            width: 100%;
        }

        .doc-footer-left {
            display: table-cell;
            vertical-align: middle;
            font-size: 7.5px;
            color: #888888;
            width: 60%;
        }

        .doc-footer-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            font-size: 7.5px;
            color: #888888;
            width: 40%;
        }

        .doc-footer-right strong {
            color: <?php echo e($primaryColor); ?>;
        }

        /* ══ UTILITAIRES ══ */
        .mt-8  { margin-top: 8px; }
        .mb-8  { margin-bottom: 8px; }
        .mb-12 { margin-bottom: 12px; }
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>


<div id="sidebar">
    <div class="sb-inner">
        <div class="sb-top">
            <div class="sb-top-cell">
                <?php if($logoBase64 ?? null): ?>
                    <img src="<?php echo e($logoBase64); ?>" class="sb-logo" alt="Logo"/>
                <?php endif; ?>
                <span class="sb-company-name"><?php echo e($company->name ?? ''); ?></span>
            </div>
        </div>
        <div class="sb-bottom">
            <div class="sb-bottom-cell">
                <span class="sb-page">Page <span class="pagenum"></span></span>
            </div>
        </div>
    </div>
</div>


<?php if($watermark ?? false): ?>
<div class="watermark"><?php echo e($watermark); ?></div>
<?php endif; ?>


<main>

    
    <div class="doc-title-row">
        <div class="doc-title-left">
            <div class="doc-type-label"><?php echo e($document->type_label ?? 'Document'); ?></div>
            <div class="doc-number-label">N° <?php echo e($document->number ?? '—'); ?></div>
        </div>
        <div class="doc-title-right">
            <span class="doc-status-badge"><?php echo e($document->status_label ?? ''); ?></span>
        </div>
    </div>

    
    <div class="meta-row">
        <div class="meta-card">
            <div class="meta-card-title">Date d'émission</div>
            <div class="meta-card-value">
                <?php echo e($document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') : '—'); ?>

            </div>
        </div>
        <div class="meta-card">
            <div class="meta-card-title">Date d'échéance</div>
            <div class="meta-card-value">
                <?php echo e(!empty($document->due_date) ? \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') : '—'); ?>

            </div>
        </div>
        <div class="meta-card">
            <div class="meta-card-title">Devise</div>
            <div class="meta-card-value"><?php echo e($document->currency ?? 'XOF'); ?></div>
        </div>
    </div>

    
    <div class="addresses">
        <div class="col-doc-info">
            <div class="addr-card">
                <div class="addr-card-title">Émetteur</div>
                <div style="font-size:10px; font-weight:bold; color:#002D5B; margin-bottom:4px;">
                    <?php echo e($company->name ?? ''); ?>

                </div>
                <div style="font-size:8.5px; color:#555555; line-height:1.6;">
                    <?php if(!empty($company->address)): ?><?php echo e($company->address); ?><br/><?php endif; ?>
                    <?php if(!empty($company->city)): ?><?php echo e($company->city); ?><?php if(!empty($company->country)): ?>, <?php echo e($company->country); ?><?php endif; ?><br/><?php endif; ?>
                    <?php if(!empty($company->phone)): ?>Tél : <?php echo e($company->phone); ?><br/><?php endif; ?>
                    <?php if(!empty($company->email)): ?><?php echo e($company->email); ?><br/><?php endif; ?>
                    <?php if(!empty($company->tax_id)): ?>N° Fiscal : <?php echo e($company->tax_id); ?><br/><?php endif; ?>
                    <?php if(!empty($company->rccm)): ?>RCCM : <?php echo e($company->rccm); ?><?php endif; ?>
                </div>
                <?php if(!empty($document->reference)): ?>
                <div class="addr-row" style="margin-top:6px;">
                    <span class="addr-label">Référence</span>
                    <span class="addr-value"><?php echo e($document->reference); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-client">
            <div class="addr-card-client">
                <div class="addr-card-title">Destinataire</div>
                <div class="client-name"><?php echo e($document->customer->name ?? '—'); ?></div>
                <div class="client-detail">
                    <?php if(!empty($document->customer->address)): ?><?php echo e($document->customer->address); ?><br/><?php endif; ?>
                    <?php if(!empty($document->customer->city)): ?><?php echo e($document->customer->city); ?><?php if(!empty($document->customer->country)): ?>, <?php echo e($document->customer->country); ?><?php endif; ?><br/><?php endif; ?>
                    <?php if(!empty($document->customer->phone)): ?>Tél : <?php echo e($document->customer->phone); ?><br/><?php endif; ?>
                    <?php if(!empty($document->customer->email)): ?><?php echo e($document->customer->email); ?><br/><?php endif; ?>
                    <?php if(!empty($document->customer->tax_number)): ?>N° Fiscal : <?php echo e($document->customer->tax_number); ?><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(!empty($document->subject)): ?>
    <div class="subject-bar">
        <span>Objet :</span> <?php echo e($document->subject); ?>

    </div>
    <?php endif; ?>

    
    <?php
        $totalHT = collect($document->lines)->sum(fn($l) => (float)($l->line_total ?? 0));
    ?>
    <table class="lines-table">
        <thead>
            <tr>
                <th style="width:4%;">#</th>
                <th style="width:38%;">Désignation</th>
                <th class="text-right" style="width:10%;">Qté</th>
                <th class="text-right" style="width:13%;">P.U. HT</th>
                <th class="text-right" style="width:10%;">Remise</th>
                <th class="text-right" style="width:13%;">TVA</th>
                <th class="text-right" style="width:12%;">Total HT</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $document->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr class="<?php echo e($index % 2 === 0 ? 'row-even' : 'row-odd'); ?>">
                <td><?php echo e($index + 1); ?></td>
                <td>
                    <strong><?php echo e($line->description ?? $line->name ?? '—'); ?></strong>
                    <?php if(!empty($line->detail)): ?>
                        <br/><span class="line-desc"><?php echo e($line->detail); ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <?php echo e(number_format((float)($line->quantity ?? 1), 2)); ?>

                    <?php if(!empty($line->unit)): ?><br/><span style="font-size:7px;color:#aaa;"><?php echo e($line->unit); ?></span><?php endif; ?>
                </td>
                <td class="text-right"><?php echo e(number_format((float)($line->unit_price ?? 0), 0, ',', ' ')); ?></td>
                <td class="text-right">
                    <?php if((float)($line->discount_percent ?? 0) > 0): ?>
                        <span class="discount-badge"><?php echo e(number_format((float)$line->discount_percent, 1)); ?>%</span>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <?php if(!empty($line->tax_rate)): ?>
                        <?php echo e(number_format((float)$line->tax_rate, 0)); ?>%
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td class="text-right"><?php echo e(number_format((float)($line->line_total ?? 0), 0, ',', ' ')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Total HT</td>
                <td class="text-right"><?php echo e(number_format($totalHT, 0, ',', ' ')); ?></td>
            </tr>
        </tfoot>
    </table>

    
    <div class="bottom-section">
        <div class="bottom-left">
            <?php if($qrDataUri ?? null): ?>
            <div class="qr-block">
                <img src="<?php echo e($qrDataUri); ?>" alt="QR Code"/>
                <div class="qr-label">Scanner pour vérifier</div>
            </div>
            <?php endif; ?>
        </div>
        <div class="bottom-right">
            <table class="totals-table">
                <tr>
                    <td>Sous-total HT</td>
                    <td><?php echo e(number_format((float)($document->subtotal ?? 0), 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td>
                </tr>
                <?php if((float)($document->discount_amount ?? 0) > 0): ?>
                <tr>
                    <td>Remise globale</td>
                    <td>- <?php echo e(number_format((float)$document->discount_amount, 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td>
                </tr>
                <?php endif; ?>
                <?php if((float)($document->tax_amount ?? 0) > 0): ?>
                <tr>
                    <td>TVA (<?php echo e(number_format((float)($document->tax_rate ?? 0), 0)); ?>%)</td>
                    <td><?php echo e(number_format((float)$document->tax_amount, 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-grand">
                    <td>TOTAL TTC</td>
                    <td><?php echo e(number_format((float)($document->total ?? 0), 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td>
                </tr>
                <?php if((float)($document->amount_paid ?? 0) > 0): ?>
                <tr class="total-paid">
                    <td>Montant payé</td>
                    <td>- <?php echo e(number_format((float)$document->amount_paid, 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td>
                </tr>
                <?php $amountDue = (float)($document->total ?? 0) - (float)($document->amount_paid ?? 0); ?>
                <?php if($amountDue > 0): ?>
                <tr class="total-due">
                    <td>RESTE À PAYER</td>
                    <td><?php echo e(number_format($amountDue, 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>

    
    <?php echo $__env->make('pdf.engine.blocks._payment-info', [
        'company'      => $company,
        'document'     => $document,
        'primaryColor' => $primaryColor,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php if(!empty($document->notes)): ?>
    <div class="notes-section mb-12">
        <div class="notes-title">Notes</div>
        <div class="notes-content"><?php echo e($document->notes); ?></div>
    </div>
    <?php endif; ?>

    
    <?php if(!empty($document->terms)): ?>
    <div class="notes-section mb-12">
        <div class="notes-title">Conditions générales</div>
        <div class="notes-content"><?php echo e($document->terms); ?></div>
    </div>
    <?php endif; ?>

    
    <?php
        $sigShowEmitter  = $sigConfig['show_emitter'] ?? true;
        $sigShowClient   = $sigConfig['show_client']  ?? true;
        $showSigSection  = $sigShowEmitter || $sigShowClient;
        $sigMode         = $sigConfig['mode']          ?? 'photo';
        $sigMention      = $sigConfig['mention']       ?? 'Lu et approuvé';
        $sigEmitterLabel = $sigConfig['emitter_label'] ?? 'Émetteur';
        $sigClientLabel  = $sigConfig['client_label']  ?? 'Client';
    ?>

    <?php if($showSigSection): ?>
    <div class="sig-section">
        <div class="sig-section-title">Signatures</div>
        <div class="sig-columns">

            <?php if($sigShowEmitter): ?>
            <div class="sig-box">
                <div class="sig-box-label"><?php echo e($sigEmitterLabel); ?></div>
                <div class="sig-photo-zone">
                    <?php if($sigDigitalBase64 ?? null): ?>
                        <img src="<?php echo e($sigDigitalBase64); ?>" alt="Signature émetteur"/>
                    <?php elseif($sigStampBase64 ?? null): ?>
                        <img src="<?php echo e($sigStampBase64); ?>" alt="Cachet"/>
                    <?php else: ?>
                        <div class="sig-photo-placeholder">Signature / Cachet</div>
                    <?php endif; ?>
                </div>
                <?php if($sigMode === 'mention'): ?>
                <div class="sig-mention"><?php echo e($sigMention); ?></div>
                <?php endif; ?>
                <div class="sig-line"></div>
                <div class="sig-meta">
                    Nom : ___________________________<br/>
                    Date : __________________________
                </div>
            </div>
            <?php endif; ?>

            <?php if($sigShowClient): ?>
            <div class="sig-box">
                <div class="sig-box-label"><?php echo e($sigClientLabel); ?></div>
                <div class="sig-photo-zone">
                    <div class="sig-photo-placeholder">Signature / Cachet</div>
                </div>
                <?php if($sigMode === 'mention'): ?>
                <div class="sig-mention"><?php echo e($sigMention); ?></div>
                <?php endif; ?>
                <div class="sig-line"></div>
                <div class="sig-meta">
                    Nom : ___________________________<br/>
                    Date : __________________________
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endif; ?>

    
    <div class="doc-footer">
        <div class="doc-footer-inner">
            <div class="doc-footer-left">
                <?php if(!empty($company->invoice_footer)): ?>
                    <?php echo e($company->invoice_footer); ?>

                <?php else: ?>
                    <?php echo e($company->legal_name ?? $company->name ?? ''); ?>

                    <?php if(!empty($company->capital)): ?> — Capital : <?php echo e($company->capital); ?><?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="doc-footer-right">
                <?php if(!empty($company->rccm)): ?>RCCM : <?php echo e($company->rccm); ?> — <?php endif; ?>
                <?php if(!empty($company->trade_register)): ?>RC : <?php echo e($company->trade_register); ?> — <?php endif; ?>
                <strong>IBIG FactPro</strong>
            </div>
        </div>
    </div>

</main>
</body>
</html>
