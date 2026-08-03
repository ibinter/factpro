
<?php
    $totalHT = collect($document->lines)->sum(fn($l) => (float)($l->line_total ?? 0));

    $sigShowEmitter = $sigConfig['show_emitter'] ?? true;
    $sigShowClient  = $sigConfig['show_client']  ?? true;
    $showSigSection = $sigShowEmitter || $sigShowClient;

    $primary = $primaryColor ?? '#1B4FA8';
    $accent  = $accentColor  ?? '#F0C040';

    $hasWatermark     = !empty($watermark);
    $hasLogo          = !empty($logoBase64);
    $hasQr            = !empty($qrDataUri);
    $hasSigDigital    = !empty($sigDigitalBase64);
    $hasSigStamp      = !empty($sigStampBase64);

    $customer = $document->customer ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
/* ── Reset & page ─────────────────────────────────────────── */
* { margin:0; padding:0; box-sizing:border-box; }

@page {
    margin: 165px 45px 30px 45px;
}

body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 8px;
    color: #2c2c2c;
    line-height: 1.45;
}

/* ── Watermark ────────────────────────────────────────────── */
.watermark {
    position: fixed;
    top: 38%;
    left: 50%;
    transform: translateX(-50%) translateY(-50%) rotate(-35deg);
    font-size: 72px;
    font-weight: 900;
    color: rgba(0,0,0,0.045);
    letter-spacing: 6px;
    text-transform: uppercase;
    z-index: 0;
    white-space: nowrap;
}

/* ── Header hero fixé ────────────────────────────────────── */
.hero-header {
    position: fixed;
    top: -150px;
    left: 0;
    right: 0;
    height: 135px;
    background-color: <?php echo e($primary); ?>;
    overflow: hidden;
}

/* Cercles décoratifs */
.hero-circle-1 {
    position: absolute;
    right: -30px;
    top: -30px;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: rgba(255,255,255,0.07);
}
.hero-circle-2 {
    position: absolute;
    right: 60px;
    top: 50px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    background-color: rgba(255,255,255,0.04);
}
.hero-circle-3 {
    position: absolute;
    left: -20px;
    bottom: -20px;
    width: 75px;
    height: 75px;
    border-radius: 50%;
    background-color: rgba(255,255,255,0.05);
}

/* Contenu interne du hero */
.hero-inner {
    position: relative;
    z-index: 2;
    padding: 15px 22px 12px 22px;
    height: 100%;
}

/* Ligne supérieure : logo + infos doc */
.hero-top {
    width: 100%;
    display: table;
    margin-bottom: 10px;
}
.hero-top-left {
    display: table-cell;
    vertical-align: middle;
    width: 60px;
}
.hero-top-center {
    display: table-cell;
    vertical-align: middle;
    padding-left: 14px;
}
.hero-top-right {
    display: table-cell;
    vertical-align: middle;
    text-align: right;
    width: 120px;
}

/* Logo */
.hero-logo-wrap {
    display: inline-block;
    background-color: rgba(255,255,255,0.15);
    border-radius: 5px;
    padding: 4px 6px;
    line-height: 0;
}
.hero-logo-wrap img {
    max-height: 40px;
    max-width: 52px;
}
.hero-company-name {
    font-size: 8px;
    font-weight: 700;
    color: rgba(255,255,255,0.90);
    letter-spacing: 0.3px;
}

/* Badge type document */
.hero-doc-label {
    font-size: 6px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: rgba(255,255,255,0.55);
    margin-bottom: 3px;
}
.hero-doc-type {
    font-size: 24px;
    font-weight: 900;
    color: #ffffff;
    letter-spacing: -1px;
    line-height: 1;
}
.hero-doc-number {
    font-size: 7px;
    color: rgba(255,255,255,0.45);
    margin-top: 2px;
}

/* Status badge */
.hero-status-badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 10px;
    font-size: 6px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    background-color: rgba(255,255,255,0.18);
    color: #ffffff;
    margin-top: 4px;
}

/* Ligne meta : émetteur / destinataire / dates */
.hero-meta {
    display: table;
    width: 100%;
    margin-top: 8px;
    border-top: 1px solid rgba(255,255,255,0.12);
    padding-top: 8px;
}
.hero-meta-cell {
    display: table-cell;
    vertical-align: top;
    padding-right: 14px;
}
.hero-meta-cell:last-child { padding-right: 0; text-align: right; }
.hero-meta-label {
    font-size: 4.5px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: rgba(255,255,255,0.40);
    margin-bottom: 2px;
}
.hero-meta-value {
    font-size: 6px;
    font-weight: 700;
    color: #ffffff;
}

/* ── Filet accent ─────────────────────────────────────────── */
.hero-accent-bar {
    height: 3px;
    background-color: <?php echo e($accent); ?>;
    margin-bottom: 0;
}

/* ── Corps principal ──────────────────────────────────────── */
.main-content {
    margin-top: 10px;
}

/* Référence / objet */
.doc-ref-row {
    display: table;
    width: 100%;
    margin-bottom: 10px;
}
.doc-ref-cell {
    display: table-cell;
    vertical-align: middle;
}
.doc-ref-cell:last-child { text-align: right; }
.ref-label {
    font-size: 6px;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.7px;
}
.ref-value {
    font-size: 8px;
    font-weight: 700;
    color: #333;
}

/* Adresses en 2 colonnes */
.addr-table {
    display: table;
    width: 100%;
    margin-bottom: 14px;
}
.addr-col {
    display: table-cell;
    vertical-align: top;
    width: 50%;
    padding: 9px 11px;
    background-color: #f7f8fa;
}
.addr-col:first-child {
    border-right: 3px solid #ffffff;
}
.addr-col-label {
    font-size: 5.5px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #aaa;
    margin-bottom: 4px;
}
.addr-name {
    font-size: 9px;
    font-weight: 700;
    color: #222;
    margin-bottom: 2px;
}
.addr-line {
    font-size: 7px;
    color: #555;
    line-height: 1.55;
}
.addr-meta {
    font-size: 6.5px;
    color: #777;
    margin-top: 3px;
}

/* Objet / référence externe */
.subject-bar {
    background-color: #f0f2f5;
    border-left: 3px solid <?php echo e($primary); ?>;
    padding: 5px 10px;
    margin-bottom: 12px;
    font-size: 7.5px;
    color: #333;
}
.subject-bar span {
    font-weight: 700;
}

/* ── Tableau des lignes ───────────────────────────────────── */
.lines-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4px;
    font-size: 7.5px;
}
.lines-table thead tr {
    background-color: <?php echo e($primary); ?>;
    color: #ffffff;
}
.lines-table thead th {
    padding: 6px 7px;
    font-size: 6.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.lines-table thead th.text-right { text-align: right; }
.lines-table thead th.text-center { text-align: center; }

.lines-table tbody tr { border-bottom: 1px solid #ebebeb; }
.lines-table tbody tr:nth-child(even) { background-color: #fafbfc; }
.lines-table tbody td {
    padding: 5px 7px;
    vertical-align: top;
    color: #333;
}
.lines-table tbody td.text-right { text-align: right; }
.lines-table tbody td.text-center { text-align: center; }

.line-desc { font-weight: 600; color: #222; }
.line-detail { font-size: 6.5px; color: #777; margin-top: 1px; }

/* ── Bloc totaux ──────────────────────────────────────────── */
.totals-wrap {
    display: table;
    width: 100%;
    margin-top: 8px;
    margin-bottom: 14px;
}
.totals-spacer { display: table-cell; width: 55%; }
.totals-block  { display: table-cell; width: 45%; vertical-align: top; }

.totals-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 7.5px;
}
.totals-table tr td {
    padding: 4px 8px;
    border-bottom: 1px solid #eee;
}
.totals-table tr td:last-child { text-align: right; font-weight: 600; }
.totals-table .total-grand td {
    background-color: <?php echo e($primary); ?>;
    color: #ffffff;
    font-size: 9px;
    font-weight: 900;
    border-bottom: none;
    padding: 7px 8px;
}
.totals-table .total-paid td {
    color: #2e7d32;
    font-weight: 700;
}
.totals-table .total-due td {
    background-color: #fff3cd;
    font-weight: 800;
    font-size: 8.5px;
    border-bottom: none;
}

/* ── Montant en lettres ───────────────────────────────────── */
.amount-words {
    background-color: #f7f8fa;
    border: 1px solid #e8e8e8;
    padding: 5px 10px;
    margin-bottom: 12px;
    font-size: 7px;
    color: #555;
}
.amount-words strong { color: #222; }

/* ── Section signatures ───────────────────────────────────── */
.sig-section {
    display: table;
    width: 100%;
    margin-bottom: 14px;
    margin-top: 10px;
}
.sig-col {
    display: table-cell;
    width: 50%;
    vertical-align: top;
    padding: 8px 10px;
    border: 1px solid #e8e8e8;
}
.sig-col:first-child { border-right: none; }
.sig-col-label {
    font-size: 5.5px;
    text-transform: uppercase;
    letter-spacing: 0.9px;
    color: #aaa;
    margin-bottom: 6px;
}
.sig-col-name {
    font-size: 7.5px;
    font-weight: 700;
    color: #333;
    margin-bottom: 4px;
}
.sig-img-wrap {
    height: 42px;
    line-height: 42px;
    text-align: center;
}
.sig-img-wrap img { max-height: 40px; max-width: 100%; }
.sig-line {
    border-top: 1px solid #ccc;
    margin-top: 8px;
    padding-top: 3px;
    font-size: 6px;
    color: #aaa;
    text-align: center;
}

/* ── Notes / conditions ───────────────────────────────────── */
.notes-terms-wrap {
    display: table;
    width: 100%;
    margin-bottom: 12px;
}
.notes-col {
    display: table-cell;
    vertical-align: top;
    width: 50%;
    padding-right: 10px;
}
.notes-col:last-child { padding-right: 0; padding-left: 10px; }
.notes-label {
    font-size: 6px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #aaa;
    border-bottom: 1px solid #e5e5e5;
    padding-bottom: 3px;
    margin-bottom: 5px;
}
.notes-body {
    font-size: 7px;
    color: #555;
    line-height: 1.5;
}

/* ── QR & hash ────────────────────────────────────────────── */
.verify-row {
    display: table;
    width: 100%;
    margin-bottom: 10px;
}
.verify-qr { display: table-cell; vertical-align: middle; width: 55px; }
.verify-qr img { width: 48px; height: 48px; }
.verify-info {
    display: table-cell;
    vertical-align: middle;
    padding-left: 8px;
}
.verify-url { font-size: 6.5px; color: #555; word-break: break-all; }
.verify-hash {
    font-size: 5.5px;
    color: #aaa;
    word-break: break-all;
    margin-top: 2px;
    font-family: monospace;
}

/* ── Pied de page (flux normal) ───────────────────────────── */
.doc-footer {
    border-top: 2px solid <?php echo e($accent); ?>;
    padding-top: 6px;
    margin-top: 10px;
    font-size: 6px;
    color: #888;
    text-align: center;
    line-height: 1.6;
}

/* ── Utilitaires ──────────────────────────────────────────── */
.text-right  { text-align: right; }
.text-center { text-align: center; }
.fw-700 { font-weight: 700; }
.mt-6  { margin-top: 6px; }
.mb-6  { margin-bottom: 6px; }
.page-break { page-break-after: always; }
</style>
</head>
<body>


<?php if($hasWatermark): ?>
<div class="watermark"><?php echo e($watermark); ?></div>
<?php endif; ?>


<div class="hero-header">
    
    <div class="hero-circle-1"></div>
    <div class="hero-circle-2"></div>
    <div class="hero-circle-3"></div>

    <div class="hero-inner">
        
        <div class="hero-top">
            <div class="hero-top-left">
                <?php if($hasLogo): ?>
                <div class="hero-logo-wrap">
                    <img src="<?php echo e($logoBase64); ?>" alt="logo">
                </div>
                <?php else: ?>
                <div class="hero-company-name"><?php echo e($company->name ?? ''); ?></div>
                <?php endif; ?>
            </div>
            <div class="hero-top-center">
                <?php if($hasLogo): ?>
                <div class="hero-company-name"><?php echo e($company->name ?? ''); ?></div>
                <?php endif; ?>
            </div>
            <div class="hero-top-right">
                <div class="hero-doc-label"><?php echo e($document->type_label ?? 'Document'); ?></div>
                <div class="hero-doc-type"><?php echo e(strtoupper($document->type_label ?? 'DOC')); ?></div>
                <div class="hero-doc-number"><?php echo e($document->number ?? ''); ?></div>
                <?php if(!empty($document->status_label)): ?>
                <div class="hero-status-badge"><?php echo e($document->status_label); ?></div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="hero-meta">
            <div class="hero-meta-cell">
                <div class="hero-meta-label">Émetteur</div>
                <div class="hero-meta-value"><?php echo e($company->name ?? '—'); ?></div>
            </div>
            <div class="hero-meta-cell">
                <div class="hero-meta-label">Destinataire</div>
                <div class="hero-meta-value"><?php echo e($customer->name ?? '—'); ?></div>
            </div>
            <div class="hero-meta-cell">
                <div class="hero-meta-label">Date d'émission</div>
                <div class="hero-meta-value"><?php echo e($document->issue_date ?? '—'); ?></div>
            </div>
            <?php if(!empty($document->due_date)): ?>
            <div class="hero-meta-cell">
                <div class="hero-meta-label">Échéance</div>
                <div class="hero-meta-value"><?php echo e($document->due_date); ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<div class="hero-accent-bar"></div>


<div class="main-content">

    
    <?php if(!empty($document->reference) || !empty($document->subject)): ?>
    <div class="doc-ref-row">
        <?php if(!empty($document->reference)): ?>
        <div class="doc-ref-cell">
            <div class="ref-label">Référence</div>
            <div class="ref-value"><?php echo e($document->reference); ?></div>
        </div>
        <?php endif; ?>
        <?php if(!empty($document->subject)): ?>
        <div class="doc-ref-cell">
            <div class="ref-label">Objet</div>
            <div class="ref-value"><?php echo e($document->subject); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    
    <?php if(empty($document->reference) && !empty($document->subject)): ?>
    <div class="subject-bar">Objet : <span><?php echo e($document->subject); ?></span></div>
    <?php endif; ?>

    
    <div class="addr-table">
        
        <div class="addr-col">
            <div class="addr-col-label">Émetteur</div>
            <div class="addr-name"><?php echo e($company->name ?? ''); ?></div>
            <?php if(!empty($company->legal_name) && $company->legal_name !== $company->name): ?>
            <div class="addr-line"><?php echo e($company->legal_name); ?></div>
            <?php endif; ?>
            <?php if(!empty($company->address)): ?>
            <div class="addr-line"><?php echo e($company->address); ?></div>
            <?php endif; ?>
            <?php if(!empty($company->city) || !empty($company->country)): ?>
            <div class="addr-line"><?php echo e(implode(', ', array_filter([$company->city ?? null, $company->country ?? null]))); ?></div>
            <?php endif; ?>
            <div class="addr-meta">
                <?php if(!empty($company->phone)): ?> Tél : <?php echo e($company->phone); ?><br><?php endif; ?>
                <?php if(!empty($company->email)): ?> <?php echo e($company->email); ?><br><?php endif; ?>
                <?php if(!empty($company->tax_id)): ?> NIF : <?php echo e($company->tax_id); ?><br><?php endif; ?>
                <?php if(!empty($company->trade_register)): ?> RC : <?php echo e($company->trade_register); ?><br><?php endif; ?>
                <?php if(!empty($company->rccm)): ?> RCCM : <?php echo e($company->rccm); ?><br><?php endif; ?>
                <?php if(!empty($company->capital)): ?> Capital : <?php echo e($company->capital); ?><?php endif; ?>
            </div>
        </div>

        
        <div class="addr-col">
            <div class="addr-col-label">Destinataire</div>
            <?php if($customer): ?>
            <div class="addr-name"><?php echo e($customer->name ?? ''); ?></div>
            <?php if(!empty($customer->address)): ?>
            <div class="addr-line"><?php echo e($customer->address); ?></div>
            <?php endif; ?>
            <?php if(!empty($customer->city) || !empty($customer->country)): ?>
            <div class="addr-line"><?php echo e(implode(', ', array_filter([$customer->city ?? null, $customer->country ?? null]))); ?></div>
            <?php endif; ?>
            <div class="addr-meta">
                <?php if(!empty($customer->phone)): ?> Tél : <?php echo e($customer->phone); ?><br><?php endif; ?>
                <?php if(!empty($customer->email)): ?> <?php echo e($customer->email); ?><br><?php endif; ?>
                <?php if(!empty($customer->tax_number)): ?> NIF : <?php echo e($customer->tax_number); ?><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <table class="lines-table">
        <thead>
            <tr>
                <th style="width:38%">Description</th>
                <th class="text-center" style="width:9%">Qté</th>
                <th class="text-center" style="width:8%">Unité</th>
                <th class="text-right" style="width:13%">Prix unit.</th>
                <th class="text-right" style="width:9%">Rem.%</th>
                <th class="text-right" style="width:9%">TVA%</th>
                <th class="text-right" style="width:14%">Total HT</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $document->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <div class="line-desc"><?php echo e($line->description ?? ''); ?></div>
                    <?php if(!empty($line->detail)): ?>
                    <div class="line-detail"><?php echo e($line->detail); ?></div>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?php echo e($line->quantity ?? ''); ?></td>
                <td class="text-center"><?php echo e($line->unit ?? ''); ?></td>
                <td class="text-right"><?php echo e(number_format((float)($line->unit_price ?? 0), 2, ',', ' ')); ?></td>
                <td class="text-right">
                    <?php if(!empty($line->discount_percent) && (float)$line->discount_percent > 0): ?>
                    <?php echo e(number_format((float)$line->discount_percent, 1, ',', '')); ?>%
                    <?php else: ?>
                    —
                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <?php echo e(number_format((float)($line->tax_rate ?? 0), 0, ',', '')); ?>%
                </td>
                <td class="text-right fw-700">
                    <?php echo e(number_format((float)($line->line_total ?? 0), 2, ',', ' ')); ?>

                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    
    <div class="totals-wrap">
        <div class="totals-spacer"></div>
        <div class="totals-block">
            <table class="totals-table">
                <tr>
                    <td>Sous-total HT</td>
                    <td><?php echo e(number_format($totalHT, 2, ',', ' ')); ?> <?php echo e($document->currency ?? ''); ?></td>
                </tr>
                <?php if(!empty($document->discount_amount) && (float)$document->discount_amount > 0): ?>
                <tr>
                    <td>Remise</td>
                    <td>- <?php echo e(number_format((float)$document->discount_amount, 2, ',', ' ')); ?> <?php echo e($document->currency ?? ''); ?></td>
                </tr>
                <?php endif; ?>
                <?php if(!empty($document->tax_amount)): ?>
                <tr>
                    <td>TVA (<?php echo e($document->tax_rate ?? 0); ?>%)</td>
                    <td><?php echo e(number_format((float)$document->tax_amount, 2, ',', ' ')); ?> <?php echo e($document->currency ?? ''); ?></td>
                </tr>
                <?php endif; ?>
                <tr class="total-grand">
                    <td>TOTAL TTC</td>
                    <td><?php echo e(number_format((float)($document->total ?? 0), 2, ',', ' ')); ?> <?php echo e($document->currency ?? ''); ?></td>
                </tr>
                <?php if(!empty($document->amount_paid) && (float)$document->amount_paid > 0): ?>
                <tr class="total-paid">
                    <td>Déjà réglé</td>
                    <td>- <?php echo e(number_format((float)$document->amount_paid, 2, ',', ' ')); ?> <?php echo e($document->currency ?? ''); ?></td>
                </tr>
                <tr class="total-due">
                    <td>Reste à payer</td>
                    <td><?php echo e(number_format(max(0, (float)($document->total ?? 0) - (float)$document->amount_paid), 2, ',', ' ')); ?> <?php echo e($document->currency ?? ''); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    
    <?php if ($__env->exists('pdf.partials._payment-info')) echo $__env->make('pdf.partials._payment-info', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php if(!empty($document->notes) || !empty($document->terms)): ?>
    <div class="notes-terms-wrap">
        <?php if(!empty($document->notes)): ?>
        <div class="notes-col">
            <div class="notes-label">Notes</div>
            <div class="notes-body"><?php echo e($document->notes); ?></div>
        </div>
        <?php endif; ?>
        <?php if(!empty($document->terms)): ?>
        <div class="notes-col">
            <div class="notes-label">Conditions</div>
            <div class="notes-body"><?php echo e($document->terms); ?></div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    
    <?php if($showSigSection): ?>
    <div class="sig-section">
        <?php if($sigShowEmitter): ?>
        <div class="sig-col">
            <div class="sig-col-label">Signature émetteur</div>
            <div class="sig-col-name"><?php echo e($company->name ?? ''); ?></div>
            <div class="sig-img-wrap">
                <?php if($hasSigDigital): ?>
                <img src="<?php echo e($sigDigitalBase64); ?>" alt="signature">
                <?php elseif($hasSigStamp): ?>
                <img src="<?php echo e($sigStampBase64); ?>" alt="cachet">
                <?php endif; ?>
            </div>
            <div class="sig-line">Signature &amp; cachet</div>
        </div>
        <?php endif; ?>
        <?php if($sigShowClient): ?>
        <div class="sig-col">
            <div class="sig-col-label">Signature client</div>
            <div class="sig-col-name"><?php echo e($customer->name ?? ''); ?></div>
            <div class="sig-img-wrap"></div>
            <div class="sig-line">Lu et approuvé</div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    
    <?php if($hasQr || !empty($verification_url) || !empty($integrity_hash)): ?>
    <div class="verify-row">
        <?php if($hasQr): ?>
        <div class="verify-qr">
            <img src="<?php echo e($qrDataUri); ?>" alt="QR vérification">
        </div>
        <?php endif; ?>
        <div class="verify-info">
            <?php if(!empty($verification_url)): ?>
            <div class="verify-url">Vérification : <?php echo e($verification_url); ?></div>
            <?php endif; ?>
            <?php if(!empty($integrity_hash)): ?>
            <div class="verify-hash">Hash : <?php echo e($integrity_hash); ?></div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="doc-footer">
        <?php if(!empty($company->invoice_footer)): ?>
        <?php echo e($company->invoice_footer); ?>

        <?php else: ?>
        <?php echo e($company->legal_name ?? $company->name ?? ''); ?>

        <?php if(!empty($company->address)): ?> — <?php echo e($company->address); ?><?php endif; ?>
        <?php if(!empty($company->city)): ?> <?php echo e($company->city); ?><?php endif; ?>
        <?php if(!empty($company->phone)): ?> — Tél : <?php echo e($company->phone); ?><?php endif; ?>
        <?php if(!empty($company->email)): ?> — <?php echo e($company->email); ?><?php endif; ?>
        <?php if(!empty($company->tax_id)): ?> | NIF : <?php echo e($company->tax_id); ?><?php endif; ?>
        <?php if(!empty($company->trade_register)): ?> | RC : <?php echo e($company->trade_register); ?><?php endif; ?>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
