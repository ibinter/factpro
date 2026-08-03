<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 150px 45px 30px 45px; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; background: #fff; }

/* ── HEADER FIXE ─────────────────────────────────────────────── */
#lux-header {
    position: fixed;
    top: -135px; left: 0; right: 0;
    height: 120px;
    background: <?php echo e($primaryColor); ?>;
    padding: 14px 16px;
}

.lux-hdr-top {
    display: table;
    width: 100%;
    margin-bottom: 6px;
}
.lux-hdr-logo-cell {
    display: table-cell;
    vertical-align: middle;
    width: 28px;
}
.lux-hdr-logo-wrap {
    background: rgba(255,255,255,0.1);
    border-radius: 3px;
    padding: 3px;
    display: inline-block;
}
.lux-hdr-logo-wrap img {
    max-height: 22px;
    max-width: 22px;
    display: block;
}
.lux-hdr-name-cell {
    display: table-cell;
    vertical-align: middle;
    padding-left: 8px;
}
.lux-hdr-company {
    font-size: 14px;
    font-weight: bold;
    color: <?php echo e($accentColor); ?>;
    letter-spacing: 0.5px;
    line-height: 1.2;
}
.lux-hdr-tagline {
    font-size: 6px;
    color: rgba(255,255,255,0.5);
    letter-spacing: 0.5px;
    margin-top: 1px;
}
.lux-hdr-doc-cell {
    display: table-cell;
    vertical-align: middle;
    text-align: right;
}
.lux-hdr-doc-type {
    font-size: 8px;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 2.5px;
    line-height: 1.3;
}
.lux-hdr-doc-num {
    font-size: 6px;
    color: rgba(255,255,255,0.4);
    margin-top: 2px;
    letter-spacing: 0.5px;
}

/* Séparateur décoratif avec losange central */
.lux-hdr-sep {
    display: table;
    width: 100%;
    margin: 5px 0;
}
.lux-hdr-sep-line {
    display: table-cell;
    border-top: 1px solid <?php echo e($accentColor); ?>;
    opacity: 0.4;
    vertical-align: middle;
}
.lux-hdr-sep-diamond {
    display: table-cell;
    white-space: nowrap;
    padding: 0 6px;
    font-size: 8px;
    color: <?php echo e($accentColor); ?>;
    vertical-align: middle;
    line-height: 1;
}

/* Rangée basse : date · client · total */
.lux-hdr-bottom {
    display: table;
    width: 100%;
}
.lux-hdr-bottom-cell {
    display: table-cell;
    vertical-align: top;
}
.lux-hdr-bottom-cell.center { text-align: center; }
.lux-hdr-bottom-cell.right  { text-align: right; }
.lux-hdr-meta-label {
    font-size: 5px;
    color: <?php echo e($accentColor); ?>;
    opacity: 0.7;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 1px;
}
.lux-hdr-meta-value {
    font-size: 6px;
    color: #fff;
    font-weight: bold;
    letter-spacing: 0.3px;
}

/* Filet accentué sous header (dans le flux) */
.lux-header-rule {
    border: none;
    border-top: 2px solid <?php echo e($accentColor); ?>;
    margin: 0 0 12px 0;
}

/* ── WATERMARK ───────────────────────────────────────────────── */
<?php if($watermark): ?>
#lux-watermark {
    position: fixed;
    top: 80mm; left: 10mm;
    font-size: 54px;
    font-weight: bold;
    color: rgba(0,0,0,0.035);
    transform: rotate(-35deg);
    white-space: nowrap;
    z-index: 0;
}
<?php endif; ?>

/* ── ADRESSES ────────────────────────────────────────────────── */
.lux-addresses {
    display: table;
    width: 100%;
    margin-bottom: 14px;
}
.lux-addr-cell {
    display: table-cell;
    width: 47%;
    vertical-align: top;
    border: 1px solid #E8E8E8;
    border-top: 2px solid <?php echo e($accentColor); ?>;
    padding: 8px 10px;
    background: #fff;
}
.lux-addr-sep {
    display: table-cell;
    width: 6%;
}
.lux-addr-label {
    font-size: 6px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: <?php echo e($accentColor); ?>;
    margin-bottom: 4px;
}
.lux-addr-name {
    font-size: 10px;
    font-weight: bold;
    color: #111;
    margin-bottom: 3px;
}
.lux-addr-detail {
    font-size: 8px;
    color: #555;
    line-height: 1.6;
}

/* Objet / référence */
.lux-subject {
    font-size: 8.5px;
    color: #444;
    margin-bottom: 12px;
    padding: 5px 8px;
    border-left: 3px solid <?php echo e($accentColor); ?>;
    background: #FAFAFA;
}
.lux-subject strong {
    color: <?php echo e($accentColor); ?>;
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-right: 4px;
}

/* ── TABLEAU LIGNES ──────────────────────────────────────────── */
.lux-items {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
}
.lux-items thead tr {
    border-bottom: 2px solid <?php echo e($accentColor); ?>;
}
.lux-items thead th {
    padding: 6px 5px;
    font-size: 7px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: <?php echo e($accentColor); ?>;
    background: #fff;
    text-align: left;
    font-weight: bold;
}
.lux-items thead th.r { text-align: right; }
.lux-items tbody tr {
    border-bottom: 1px solid #F0F0F0;
}
.lux-items tbody tr:nth-child(even) {
    background: #FDFCFA;
}
.lux-items tbody td {
    padding: 5px 5px;
    font-size: 8.5px;
    color: #222;
    vertical-align: top;
}
.lux-items tbody td.r { text-align: right; }
.lux-items tbody td.line-detail {
    font-size: 7.5px;
    color: #888;
    padding-top: 0;
    padding-bottom: 5px;
}

/* ── TOTAUX ──────────────────────────────────────────────────── */
.lux-totals-wrap {
    display: table;
    width: 100%;
    margin-bottom: 14px;
}
.lux-totals-qr {
    display: table-cell;
    width: 26mm;
    vertical-align: bottom;
    padding-right: 6px;
}
.lux-totals-qr-frame {
    border: 1.5px solid <?php echo e($accentColor); ?>;
    padding: 4px;
    display: inline-block;
}
.lux-totals-qr-frame img { width: 20mm; height: 20mm; display: block; }
.lux-totals-qr-label {
    font-size: 6px;
    color: #bbb;
    text-align: center;
    margin-top: 2px;
    letter-spacing: 0.5px;
}
.lux-totals-spacer { display: table-cell; }
.lux-totals-bloc {
    display: table-cell;
    width: 82mm;
    vertical-align: top;
}
.lux-totals-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5px;
}
.lux-totals-table td {
    padding: 4px 6px;
    color: #333;
}
.lux-totals-table td:last-child { text-align: right; }
.lux-totals-table .lux-tot-sep td {
    border-top: 1px solid #E8E8E8;
    padding-top: 3px;
}
.lux-totals-table .lux-tot-grand td {
    background: <?php echo e($primaryColor); ?>;
    color: <?php echo e($accentColor); ?>;
    font-size: 11px;
    font-weight: bold;
    padding: 6px 8px;
    letter-spacing: 0.3px;
}
.lux-totals-table .lux-tot-paid td {
    color: #388e3c;
    font-size: 8px;
}
.lux-totals-table .lux-tot-due td {
    color: #c62828;
    font-size: 9px;
    font-weight: bold;
    border-top: 1px dashed #e0c0c0;
}

/* ── NOTES / CONDITIONS ──────────────────────────────────────── */
.lux-notes {
    font-size: 8px;
    color: #666;
    margin-bottom: 10px;
    padding: 6px 8px;
    border: 1px solid #EFEFEF;
    border-left: 2px solid rgba(201,146,42,0.35);
    line-height: 1.5;
}
.lux-notes-label {
    font-size: 6.5px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: <?php echo e($accentColor); ?>;
    margin-bottom: 3px;
}
.lux-terms {
    font-size: 7.5px;
    color: #888;
    margin-bottom: 10px;
    padding: 5px 8px;
    border-top: 1px solid #EFEFEF;
    line-height: 1.5;
}
.lux-terms-label {
    font-size: 6px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #aaa;
    margin-bottom: 2px;
}

/* ── SIGNATURES ──────────────────────────────────────────────── */
.lux-sig-section {
    border-top: 1px solid <?php echo e($accentColor); ?>;
    padding-top: 10px;
    margin-top: 14px;
    margin-bottom: 10px;
}
.lux-sig-section-label {
    font-size: 6px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: <?php echo e($accentColor); ?>;
    margin-bottom: 8px;
    opacity: 0.8;
}
.lux-sig-row {
    display: table;
    width: 100%;
}
.lux-sig-col {
    display: table-cell;
    width: 50%;
    vertical-align: top;
    padding: 0 8px;
    text-align: center;
}
.lux-sig-col:first-child { padding-left: 0; }
.lux-sig-col:last-child  { padding-right: 0; }
.lux-sig-box {
    border: 1px solid rgba(201,146,42,0.4);
    min-height: 38px;
    padding: 4px;
    margin-bottom: 4px;
    position: relative;
    background: #FEFEFE;
}
.lux-sig-box img { max-height: 32px; max-width: 100%; display: block; margin: 0 auto; }
.lux-sig-stamp { max-height: 30px; max-width: 80%; display: block; margin: 3px auto 0; }
.lux-sig-name {
    font-size: 7.5px;
    font-weight: bold;
    color: #333;
    margin-bottom: 2px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.lux-sig-sublabel {
    font-size: 6.5px;
    color: #aaa;
    letter-spacing: 0.3px;
}

/* ── PIED DE PAGE ────────────────────────────────────────────── */
.lux-footer {
    margin-top: 12px;
    padding-top: 6px;
    border-top: 1px solid <?php echo e($accentColor); ?>;
}
.lux-footer-top {
    display: table;
    width: 100%;
    margin-bottom: 3px;
}
.lux-footer-col {
    display: table-cell;
    font-size: 6.5px;
    color: #aaa;
    vertical-align: top;
}
.lux-footer-col.center { text-align: center; }
.lux-footer-col.right  { text-align: right; }
.lux-footer-brand {
    font-size: 7px;
    font-weight: bold;
    color: <?php echo e($accentColor); ?>;
    letter-spacing: 0.5px;
}
.lux-footer-hash {
    font-size: 5.5px;
    color: #ccc;
    margin-top: 4px;
    word-break: break-all;
    text-align: center;
}
</style>
</head>
<body>

<?php
    $totalHT = collect($document->lines)->sum(fn($l) => (float)($l->line_total ?? 0));

    $sigShowEmitter = $sigConfig['show_emitter'] ?? true;
    $sigShowClient  = $sigConfig['show_client']  ?? true;
    $showSigSection = $sigShowEmitter || $sigShowClient;

    $issueDate = \Carbon\Carbon::parse($document->issue_date ?? $document->date)->format('d/m/Y');
    $dueDate   = $document->due_date ? \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') : null;
    $resteDue  = (float)($document->total ?? 0) - (float)($document->amount_paid ?? 0);
?>


<?php if($watermark): ?>
<div id="lux-watermark"><?php echo e($watermark); ?></div>
<?php endif; ?>


<div id="lux-header">

    
    <div class="lux-hdr-top">
        <?php if($logoBase64): ?>
        <div class="lux-hdr-logo-cell">
            <div class="lux-hdr-logo-wrap">
                <img src="<?php echo e($logoBase64); ?>" alt="logo">
            </div>
        </div>
        <?php endif; ?>
        <div class="lux-hdr-name-cell">
            <div class="lux-hdr-company"><?php echo e($company->name); ?></div>
            <?php if($company->tagline): ?>
            <div class="lux-hdr-tagline"><?php echo e($company->tagline); ?></div>
            <?php endif; ?>
        </div>
        <div class="lux-hdr-doc-cell">
            <div class="lux-hdr-doc-type"><?php echo e($document->type_label); ?></div>
            <div class="lux-hdr-doc-num">N° <?php echo e($document->number); ?></div>
        </div>
    </div>

    
    <div class="lux-hdr-sep">
        <div class="lux-hdr-sep-line"></div>
        <div class="lux-hdr-sep-diamond">◆</div>
        <div class="lux-hdr-sep-line"></div>
    </div>

    
    <div class="lux-hdr-bottom">
        <div class="lux-hdr-bottom-cell">
            <div class="lux-hdr-meta-label">Date d'émission</div>
            <div class="lux-hdr-meta-value"><?php echo e($issueDate); ?></div>
            <?php if($dueDate): ?>
            <div class="lux-hdr-meta-label" style="margin-top:3px;">Échéance</div>
            <div class="lux-hdr-meta-value"><?php echo e($dueDate); ?></div>
            <?php endif; ?>
        </div>
        <div class="lux-hdr-bottom-cell center">
            <?php if($document->customer): ?>
            <div class="lux-hdr-meta-label">Client</div>
            <div class="lux-hdr-meta-value"><?php echo e($document->customer->name); ?></div>
            <?php if($document->customer->city): ?>
            <div class="lux-hdr-meta-value" style="font-weight:normal;font-size:5px;color:rgba(255,255,255,0.6);"><?php echo e($document->customer->city); ?></div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="lux-hdr-bottom-cell right">
            <div class="lux-hdr-meta-label">Total TTC</div>
            <div class="lux-hdr-meta-value" style="font-size:9px;">
                <?php echo e(number_format((float)($document->total ?? 0), 0, ',', ' ')); ?> <?php echo e($document->currency); ?>

            </div>
            <?php if($document->status): ?>
            <div class="lux-hdr-meta-label" style="margin-top:3px;">Statut</div>
            <div class="lux-hdr-meta-value" style="font-size:5.5px;opacity:0.85;"><?php echo e($document->status_label); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>


<hr class="lux-header-rule">


<div class="lux-addresses">
    <div class="lux-addr-cell">
        <div class="lux-addr-label">Émetteur</div>
        <div class="lux-addr-name"><?php echo e($company->name); ?></div>
        <div class="lux-addr-detail">
            <?php if($company->legal_name && $company->legal_name !== $company->name): ?>
                <?php echo e($company->legal_name); ?><br>
            <?php endif; ?>
            <?php echo e($company->address ?? ''); ?><br>
            <?php echo e($company->city ?? ''); ?><?php if($company->country): ?>, <?php echo e($company->country); ?><?php endif; ?>
            <?php if($company->phone): ?><br><?php echo e($company->phone); ?><?php endif; ?>
            <?php if($company->email): ?><br><?php echo e($company->email); ?><?php endif; ?>
            <?php if($company->tax_id): ?><br><span style="color:#999;">NIF : <?php echo e($company->tax_id); ?></span><?php endif; ?>
            <?php if($company->trade_register): ?><br><span style="color:#999;">RCCM : <?php echo e($company->trade_register); ?></span><?php endif; ?>
        </div>
    </div>
    <div class="lux-addr-sep"></div>
    <div class="lux-addr-cell">
        <div class="lux-addr-label">Facturé à</div>
        <?php if($document->customer): ?>
        <div class="lux-addr-name"><?php echo e($document->customer->name); ?></div>
        <div class="lux-addr-detail">
            <?php echo e($document->customer->address ?? ''); ?>

            <?php if($document->customer->address): ?><br><?php endif; ?>
            <?php echo e($document->customer->city ?? ''); ?><?php if($document->customer->country): ?> <?php echo e($document->customer->country); ?><?php endif; ?>
            <?php if($document->customer->phone): ?><br><?php echo e($document->customer->phone); ?><?php endif; ?>
            <?php if($document->customer->email): ?><br><?php echo e($document->customer->email); ?><?php endif; ?>
            <?php if($document->customer->tax_number): ?><br><span style="color:#999;">NIF : <?php echo e($document->customer->tax_number); ?></span><?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>


<?php if($document->subject || $document->reference): ?>
<div class="lux-subject">
    <?php if($document->subject): ?>
        <strong>Objet :</strong> <?php echo e($document->subject); ?>

    <?php endif; ?>
    <?php if($document->reference): ?>
        <?php if($document->subject): ?> &nbsp;·&nbsp; <?php endif; ?>
        <strong>Réf. :</strong> <?php echo e($document->reference); ?>

    <?php endif; ?>
</div>
<?php endif; ?>


<table class="lux-items">
    <thead>
        <tr>
            <th style="width:38%;">Description</th>
            <th class="r" style="width:8%;">Qté</th>
            <th style="width:7%;">Unité</th>
            <th class="r" style="width:13%;">P.U. HT</th>
            <th class="r" style="width:9%;">Rem.%</th>
            <th class="r" style="width:9%;">TVA%</th>
            <th class="r" style="width:16%;">Total HT</th>
        </tr>
    </thead>
    <tbody>
    <?php $__currentLoopData = $document->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td><?php echo e($line->description); ?></td>
            <td class="r"><?php echo e(number_format((float)($line->quantity ?? 0), 2, ',', ' ')); ?></td>
            <td><?php echo e($line->unit ?? ''); ?></td>
            <td class="r"><?php echo e(number_format((float)($line->unit_price ?? 0), 0, ',', ' ')); ?></td>
            <td class="r">
                <?php if(($line->discount_percent ?? 0) > 0): ?>
                    <?php echo e(number_format((float)$line->discount_percent, 0, ',', ' ')); ?>%
                <?php else: ?>
                    —
                <?php endif; ?>
            </td>
            <td class="r"><?php echo e(number_format((float)($line->tax_rate ?? 0), 0, ',', ' ')); ?>%</td>
            <td class="r"><?php echo e(number_format((float)($line->line_total ?? 0), 0, ',', ' ')); ?></td>
        </tr>
        <?php if(!empty($line->detail)): ?>
        <tr>
            <td class="line-detail" colspan="7" style="padding-top:0;"><?php echo e($line->detail); ?></td>
        </tr>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>


<div class="lux-totals-wrap">
    <div class="lux-totals-qr">
        <?php if($qrDataUri): ?>
        <div class="lux-totals-qr-frame">
            <img src="<?php echo e($qrDataUri); ?>" alt="QR vérification">
        </div>
        <div class="lux-totals-qr-label">Vérification</div>
        <?php endif; ?>
    </div>
    <div class="lux-totals-spacer"></div>
    <div class="lux-totals-bloc">
        <table class="lux-totals-table">
            <tr>
                <td>Sous-total HT</td>
                <td><?php echo e(number_format($totalHT, 0, ',', ' ')); ?> <?php echo e($document->currency); ?></td>
            </tr>
            <?php if(($document->discount_amount ?? 0) > 0): ?>
            <tr>
                <td>Remise</td>
                <td>- <?php echo e(number_format((float)$document->discount_amount, 0, ',', ' ')); ?> <?php echo e($document->currency); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="lux-tot-sep">
                <td>TVA (<?php echo e(number_format((float)($document->tax_rate ?? 0), 0)); ?>%)</td>
                <td><?php echo e(number_format((float)($document->tax_amount ?? 0), 0, ',', ' ')); ?> <?php echo e($document->currency); ?></td>
            </tr>
            <tr class="lux-tot-grand">
                <td>TOTAL TTC</td>
                <td><?php echo e(number_format((float)($document->total ?? 0), 0, ',', ' ')); ?> <?php echo e($document->currency); ?></td>
            </tr>
            <?php if(($document->amount_paid ?? 0) > 0): ?>
            <tr class="lux-tot-paid">
                <td>Déjà réglé</td>
                <td><?php echo e(number_format((float)$document->amount_paid, 0, ',', ' ')); ?> <?php echo e($document->currency); ?></td>
            </tr>
            <tr class="lux-tot-due">
                <td>Reste à payer</td>
                <td><?php echo e(number_format($resteDue, 0, ',', ' ')); ?> <?php echo e($document->currency); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
</div>


<?php echo $__env->make('pdf.engine.blocks._payment-info', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>


<?php if($document->notes): ?>
<div class="lux-notes">
    <div class="lux-notes-label">Notes</div>
    <?php echo e($document->notes); ?>

</div>
<?php endif; ?>


<?php if($document->terms): ?>
<div class="lux-terms">
    <div class="lux-terms-label">Conditions</div>
    <?php echo e($document->terms); ?>

</div>
<?php endif; ?>


<?php if($showSigSection): ?>
<div class="lux-sig-section">
    <div class="lux-sig-section-label">Signatures &amp; approbations</div>
    <div class="lux-sig-row">
        <?php if($sigShowEmitter): ?>
        <div class="lux-sig-col">
            <div class="lux-sig-box">
                <?php if($sigDigitalBase64): ?>
                    <img src="<?php echo e($sigDigitalBase64); ?>" alt="Signature émetteur">
                <?php endif; ?>
                <?php if($sigStampBase64): ?>
                    <img class="lux-sig-stamp" src="<?php echo e($sigStampBase64); ?>" alt="Cachet">
                <?php endif; ?>
            </div>
            <div class="lux-sig-name"><?php echo e($company->name); ?></div>
            <div class="lux-sig-sublabel">Émetteur — Lu et approuvé</div>
        </div>
        <?php endif; ?>
        <?php if($sigShowClient): ?>
        <div class="lux-sig-col">
            <div class="lux-sig-box" style="min-height:38px;"></div>
            <div class="lux-sig-name"><?php echo e($document->customer->name ?? 'Client'); ?></div>
            <div class="lux-sig-sublabel">Destinataire — Lu et approuvé</div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<div class="lux-footer">
    <div class="lux-footer-top">
        <div class="lux-footer-col">
            <?php echo e($company->name); ?>

            <?php if($company->address): ?> — <?php echo e($company->address); ?><?php endif; ?>
            <?php if($company->city): ?>, <?php echo e($company->city); ?><?php endif; ?>
            <?php if($company->phone): ?> · <?php echo e($company->phone); ?><?php endif; ?>
        </div>
        <div class="lux-footer-col center">
            <?php if($company->tax_id): ?>NIF : <?php echo e($company->tax_id); ?><?php endif; ?>
            <?php if($company->trade_register): ?> · RCCM : <?php echo e($company->trade_register); ?><?php endif; ?>
            <?php if($company->capital): ?> · Capital : <?php echo e($company->capital); ?><?php endif; ?>
        </div>
        <div class="lux-footer-col right">
            <div class="lux-footer-brand">IBIG FactPro</div>
            <?php if($document->verification_url): ?>
            <div style="margin-top:2px;"><?php echo e($document->verification_url); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php if($company->invoice_footer): ?>
    <div style="font-size:6px;color:#ccc;text-align:center;margin-top:3px;"><?php echo e($company->invoice_footer); ?></div>
    <?php endif; ?>
    <?php if($document->integrity_hash): ?>
    <div class="lux-footer-hash">Intégrité : <?php echo e($document->integrity_hash); ?></div>
    <?php endif; ?>
</div>

</body>
</html>
