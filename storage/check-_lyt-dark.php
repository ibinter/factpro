<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?php echo e($document->number ?? ''); ?></title>
<style>
@page { margin: 150px 45px 30px 45px; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; background: #fff; }

/* ═══════════════════════════════════════════════
   HEADER FIXE SOMBRE
   ═══════════════════════════════════════════════ */
#lyt-dark-header {
    position: fixed;
    top: -135px;
    left: 0;
    right: 0;
    height: 120px;
    background: #0F172A;
    padding: 12px 16px;
}

/* Ligne 1 : nom société + type document */
#ldh-row1 {
    display: table;
    width: 100%;
    margin-bottom: 4px;
}
#ldh-row1-left  { display: table-cell; vertical-align: middle; }
#ldh-row1-right { display: table-cell; vertical-align: middle; text-align: right; }

.ldh-logo {
    display: inline-block;
    background: rgba(255,255,255,0.10);
    border-radius: 4px;
    padding: 3px 5px;
    margin-right: 8px;
    vertical-align: middle;
}
.ldh-logo img { max-height: 28px; max-width: 60px; vertical-align: middle; }

.ldh-company-name {
    color: #ffffff;
    font-size: 14px;
    font-weight: bold;
    vertical-align: middle;
}

.ldh-doc-type {
    color: <?php echo e($accentColor); ?>;
    font-size: 7px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 2px;
}

/* Ligne 2 : sous-titre société */
#ldh-row2 {
    color: rgba(255,255,255,0.45);
    font-size: 7px;
    margin-bottom: 7px;
}

/* Séparateur */
#ldh-sep {
    border: none;
    border-top: 1px solid rgba(255,255,255,0.12);
    margin-bottom: 7px;
}

/* Ligne 3 : méta doc */
#ldh-row3 { display: table; width: 100%; }
#ldh-row3 .ldh-meta-item {
    display: table-cell;
    padding-right: 16px;
    vertical-align: top;
}
#ldh-row3 .ldh-meta-item:last-child { padding-right: 0; text-align: right; }
.ldh-meta-label {
    display: block;
    font-size: 5px;
    color: rgba(255,255,255,0.40);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 1px;
}
.ldh-meta-value {
    display: block;
    font-size: 7px;
    color: #ffffff;
    font-weight: bold;
}

/* ═══════════════════════════════════════════════
   WATERMARK
   ═══════════════════════════════════════════════ */
#lyt-dark-watermark {
    position: fixed;
    top: 80mm;
    left: 15mm;
    font-size: 52px;
    font-weight: bold;
    color: rgba(200,0,0,0.07);
    transform: rotate(-35deg);
    white-space: nowrap;
    z-index: 0;
}

/* ═══════════════════════════════════════════════
   CORPS PRINCIPAL
   ═══════════════════════════════════════════════ */
#lyt-dark-main { margin-top: 15px; }

/* ── Bloc client + infos doc ── */
.lyt-dark-parties {
    display: table;
    width: 100%;
    margin-bottom: 14px;
}
.lyt-dark-party-cell {
    display: table-cell;
    vertical-align: top;
    padding: 10px 12px;
}
.lyt-dark-client-cell {
    width: 58%;
    background: rgba(0,229,255,0.03);
    border-left: 3px solid <?php echo e($accentColor); ?>;
    border-radius: 0 4px 4px 0;
}
.lyt-dark-doc-cell {
    width: 38%;
    background: #f8fafc;
    border-radius: 4px;
    border: 1px solid #e2e8f0;
}
.lyt-dark-parties-gap { display: table-cell; width: 4%; }

.party-label {
    font-size: 6px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: rgba(100,116,139,0.8);
    margin-bottom: 5px;
}
.party-name {
    font-size: 11px;
    font-weight: bold;
    color: #0f172a;
    margin-bottom: 4px;
}
.party-detail {
    font-size: 7.5px;
    color: #475569;
    line-height: 1.6;
}

.doc-meta-row {
    display: table;
    width: 100%;
    border-bottom: 1px solid #e2e8f0;
    padding: 3px 0;
}
.doc-meta-row:last-child { border-bottom: none; }
.doc-meta-key {
    display: table-cell;
    font-size: 7px;
    color: #94a3b8;
    width: 45%;
}
.doc-meta-val {
    display: table-cell;
    font-size: 7px;
    color: #0f172a;
    font-weight: bold;
    text-align: right;
}

/* ── Tableau des lignes ── */
.lyt-dark-items {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 14px;
}
.lyt-dark-items thead tr {
    background: #1E293B;
}
.lyt-dark-items thead th {
    padding: 6px 5px;
    font-size: 7px;
    font-weight: bold;
    color: <?php echo e($accentColor); ?>;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.lyt-dark-items thead th.r { text-align: right; }
.lyt-dark-items tbody tr:nth-child(odd)  { background: #ffffff; }
.lyt-dark-items tbody tr:nth-child(even) { background: #f8fafc; }
.lyt-dark-items tbody td {
    padding: 5px 5px;
    font-size: 8px;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}
.lyt-dark-items tbody td.r { text-align: right; }
.lyt-dark-items tbody td .line-detail {
    font-size: 6.5px;
    color: #94a3b8;
    margin-top: 1px;
}

/* ── Zone totaux + QR ── */
.lyt-dark-totaux-wrap {
    display: table;
    width: 100%;
    margin-bottom: 14px;
}
.lyt-dark-totaux-qr {
    display: table-cell;
    vertical-align: bottom;
    width: 56px;
    padding-right: 10px;
}
.lyt-dark-totaux-qr img { width: 46px; height: 46px; }
.lyt-dark-totaux-qr-label {
    font-size: 5.5px;
    color: #94a3b8;
    text-align: center;
    margin-top: 2px;
}
.lyt-dark-totaux-fill { display: table-cell; }
.lyt-dark-totaux-box-wrap {
    display: table-cell;
    vertical-align: top;
    width: 200px;
}

.totaux-sub-row {
    display: table;
    width: 100%;
    padding: 3px 0;
    border-bottom: 1px solid #e2e8f0;
}
.totaux-sub-row:last-child { border-bottom: none; }
.totaux-sub-row .ts-label {
    display: table-cell;
    font-size: 7.5px;
    color: #64748b;
}
.totaux-sub-row .ts-val {
    display: table-cell;
    font-size: 7.5px;
    color: #334155;
    font-weight: bold;
    text-align: right;
}

.lyt-dark-grand-total {
    background: #0F172A;
    border-radius: 4px;
    padding: 8px 10px;
    margin-top: 5px;
    display: table;
    width: 100%;
}
.lyt-dark-grand-total .gt-label {
    display: table-cell;
    font-size: 9px;
    font-weight: bold;
    color: rgba(255,255,255,0.75);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.lyt-dark-grand-total .gt-val {
    display: table-cell;
    font-size: 13px;
    font-weight: bold;
    color: <?php echo e($accentColor); ?>;
    text-align: right;
}
.lyt-dark-reste-row {
    display: table;
    width: 100%;
    padding: 3px 0;
    border-top: 1px solid rgba(255,255,255,0.08);
    margin-top: 4px;
}
.lyt-dark-reste-row .rr-label {
    display: table-cell;
    font-size: 7px;
    color: rgba(255,255,255,0.5);
}
.lyt-dark-reste-row .rr-val {
    display: table-cell;
    font-size: 7.5px;
    font-weight: bold;
    color: #f87171;
    text-align: right;
}

/* ── Paiement ── */
.lyt-dark-payment {
    margin-bottom: 12px;
}

/* ── Notes & Conditions ── */
.lyt-dark-notes {
    font-size: 7.5px;
    color: #475569;
    border-top: 1px solid #e2e8f0;
    padding-top: 8px;
    margin-bottom: 10px;
    line-height: 1.6;
}
.lyt-dark-notes strong { color: #334155; }

.lyt-dark-terms {
    font-size: 7px;
    color: #64748b;
    border-top: 1px solid #e2e8f0;
    padding-top: 6px;
    margin-bottom: 10px;
    line-height: 1.6;
}

/* ── QR auth (bloc vérification) ── */
.lyt-dark-qr-block {
    border-top: 2px solid <?php echo e($accentColor); ?>;
    padding-top: 8px;
    margin-bottom: 12px;
    display: table;
    width: 100%;
}
.lyt-dark-qr-block .qr-img-cell {
    display: table-cell;
    vertical-align: top;
    width: 52px;
    padding-right: 10px;
}
.lyt-dark-qr-block .qr-img-cell img { width: 44px; height: 44px; }
.lyt-dark-qr-block .qr-text-cell {
    display: table-cell;
    vertical-align: middle;
    font-size: 7px;
    color: #64748b;
    line-height: 1.6;
}
.lyt-dark-qr-block .qr-text-cell strong {
    display: block;
    font-size: 7.5px;
    color: #334155;
    margin-bottom: 2px;
}

/* ── Signatures ── */
.lyt-dark-sig-section {
    border-top: 2px solid <?php echo e($accentColor); ?>;
    padding-top: 10px;
    margin-bottom: 12px;
}
.lyt-dark-sig-row {
    display: table;
    width: 100%;
}
.lyt-dark-sig-col {
    display: table-cell;
    width: 50%;
    padding: 0 8px;
    vertical-align: top;
}
.lyt-dark-sig-col:first-child { padding-left: 0; }
.lyt-dark-sig-col:last-child  { padding-right: 0; }

.sig-box {
    border: 1px solid rgba(<?php echo e(implode(',', sscanf(ltrim($accentColor,'#'),'%02x%02x%02x'))); ?>, 0.3);
    border-radius: 4px;
    min-height: 48px;
    padding: 5px 7px;
    position: relative;
    margin-bottom: 4px;
}
.sig-box-label {
    font-size: 6.5px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.sig-box-img img { max-height: 36px; max-width: 110px; }
.sig-box-stamp img { max-height: 38px; max-width: 38px; float: right; }
.sig-name {
    font-size: 7px;
    color: #334155;
    font-weight: bold;
    margin-top: 2px;
}
.sig-date-line {
    font-size: 6.5px;
    color: #94a3b8;
    border-top: 1px solid #e2e8f0;
    padding-top: 3px;
    margin-top: 6px;
}

/* ── Pied de page dans le flux ── */
.lyt-dark-flow-footer {
    border-top: 1px solid #e2e8f0;
    padding-top: 8px;
    margin-top: 14px;
    display: table;
    width: 100%;
    font-size: 7px;
    color: #94a3b8;
}
.lyt-dark-flow-footer .ff-left  { display: table-cell; vertical-align: middle; }
.lyt-dark-flow-footer .ff-right {
    display: table-cell;
    vertical-align: middle;
    text-align: right;
}
.lyt-dark-flow-footer .ff-brand { color: <?php echo e($accentColor); ?>; font-weight: bold; }

</style>
</head>
<body>

<?php
    /* ── Totaux calculés ── */
    $totalHT = collect($document->lines)->sum(fn($l) => (float)($l->line_total ?? 0));
    $taxAmt  = (float)($document->tax_amount  ?? 0);
    $total   = (float)($document->total        ?? 0);
    $paid    = (float)($document->amount_paid  ?? 0);
    $reste   = $total - $paid;
    $cur     = $document->currency ?? '';

    /* ── Dates ── */
    $issueDate = !empty($document->issue_date)
        ? \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y')
        : (!empty($document->date) ? \Carbon\Carbon::parse($document->date)->format('d/m/Y') : '—');
    $dueDate = !empty($document->due_date)
        ? \Carbon\Carbon::parse($document->due_date)->format('d/m/Y')
        : '—';

    /* ── Signatures ── */
    $sigShowEmitter = $sigConfig['show_emitter'] ?? true;
    $sigShowClient  = $sigConfig['show_client']  ?? true;
    $showSigSection = $sigShowEmitter || $sigShowClient;
?>


<?php if($watermark ?? false): ?>
<div id="lyt-dark-watermark"><?php echo e($watermark); ?></div>
<?php endif; ?>


<div id="lyt-dark-header">

    
    <div id="ldh-row1">
        <div id="ldh-row1-left">
            <?php if(!empty($logoBase64)): ?>
                <span class="ldh-logo"><img src="<?php echo e($logoBase64); ?>" alt="logo"></span>
            <?php endif; ?>
            <span class="ldh-company-name"><?php echo e($company->name); ?></span>
        </div>
        <div id="ldh-row1-right">
            <span class="ldh-doc-type"><?php echo e($document->type_label ?? $document->type ?? ''); ?></span>
        </div>
    </div>

    
    <div id="ldh-row2">
        <?php if(!empty($company->tagline)): ?><?php echo e($company->tagline); ?><?php endif; ?>
        <?php if(!empty($company->tagline) && (!empty($company->phone) || !empty($company->email))): ?> &nbsp;·&nbsp; <?php endif; ?>
        <?php if(!empty($company->phone)): ?><?php echo e($company->phone); ?><?php endif; ?>
        <?php if(!empty($company->phone) && !empty($company->email)): ?> &nbsp;·&nbsp; <?php endif; ?>
        <?php if(!empty($company->email)): ?><?php echo e($company->email); ?><?php endif; ?>
    </div>

    
    <hr id="ldh-sep">

    
    <div id="ldh-row3">
        <div class="ldh-meta-item">
            <span class="ldh-meta-label">Date</span>
            <span class="ldh-meta-value"><?php echo e($issueDate); ?></span>
        </div>
        <div class="ldh-meta-item">
            <span class="ldh-meta-label">Échéance</span>
            <span class="ldh-meta-value"><?php echo e($dueDate); ?></span>
        </div>
        <div class="ldh-meta-item">
            <span class="ldh-meta-label">N° document</span>
            <span class="ldh-meta-value"><?php echo e($document->number ?? '—'); ?></span>
        </div>
        <?php if(!empty($document->reference)): ?>
        <div class="ldh-meta-item">
            <span class="ldh-meta-label">Référence</span>
            <span class="ldh-meta-value"><?php echo e($document->reference); ?></span>
        </div>
        <?php endif; ?>
        <div class="ldh-meta-item">
            <span class="ldh-meta-label">Devise</span>
            <span class="ldh-meta-value"><?php echo e($cur); ?></span>
        </div>
    </div>

</div>


<div id="lyt-dark-main">

    
    <div class="lyt-dark-parties">

        
        <div class="lyt-dark-party-cell lyt-dark-client-cell">
            <div class="party-label">Facturé à</div>
            <?php if(!empty($document->customer)): ?>
                <div class="party-name"><?php echo e($document->customer->name); ?></div>
                <div class="party-detail">
                    <?php if(!empty($document->customer->address)): ?><?php echo e($document->customer->address); ?><br><?php endif; ?>
                    <?php if(!empty($document->customer->city)): ?><?php echo e($document->customer->city); ?><?php endif; ?>
                    <?php if(!empty($document->customer->country)): ?> <?php echo e($document->customer->country); ?><?php endif; ?>
                    <?php if(!empty($document->customer->city) || !empty($document->customer->country)): ?><br><?php endif; ?>
                    <?php if(!empty($document->customer->phone)): ?><?php echo e($document->customer->phone); ?><br><?php endif; ?>
                    <?php if(!empty($document->customer->email)): ?><?php echo e($document->customer->email); ?><br><?php endif; ?>
                    <?php if(!empty($document->customer->tax_number)): ?>NIF : <?php echo e($document->customer->tax_number); ?><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="lyt-dark-parties-gap"></div>

        
        <div class="lyt-dark-party-cell lyt-dark-doc-cell">
            <div class="party-label">Informations document</div>

            <div class="doc-meta-row">
                <span class="doc-meta-key">Émetteur</span>
                <span class="doc-meta-val"><?php echo e($company->name); ?></span>
            </div>
            <?php if(!empty($company->tax_id)): ?>
            <div class="doc-meta-row">
                <span class="doc-meta-key">NIF / Tax ID</span>
                <span class="doc-meta-val"><?php echo e($company->tax_id); ?></span>
            </div>
            <?php endif; ?>
            <?php if(!empty($company->trade_register)): ?>
            <div class="doc-meta-row">
                <span class="doc-meta-key">RCCM</span>
                <span class="doc-meta-val"><?php echo e($company->trade_register); ?></span>
            </div>
            <?php endif; ?>
            <?php if(!empty($document->subject)): ?>
            <div class="doc-meta-row">
                <span class="doc-meta-key">Objet</span>
                <span class="doc-meta-val"><?php echo e($document->subject); ?></span>
            </div>
            <?php endif; ?>
            <div class="doc-meta-row">
                <span class="doc-meta-key">Statut</span>
                <span class="doc-meta-val"><?php echo e($document->status_label ?? $document->status ?? ''); ?></span>
            </div>
        </div>

    </div>

    
    <table class="lyt-dark-items">
        <thead>
            <tr>
                <th style="width:40%">Description</th>
                <th class="r" style="width:9%">Qté</th>
                <th style="width:7%">Unité</th>
                <th class="r" style="width:13%">P.U. HT</th>
                <th class="r" style="width:9%">Remise</th>
                <th class="r" style="width:9%">TVA %</th>
                <th class="r" style="width:13%">Total HT</th>
            </tr>
        </thead>
        <tbody>
        <?php $__currentLoopData = $document->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td>
                    <?php echo e($line->description ?? ''); ?>

                    <?php if(!empty($line->detail)): ?>
                        <div class="line-detail"><?php echo e($line->detail); ?></div>
                    <?php endif; ?>
                </td>
                <td class="r"><?php echo e(number_format((float)($line->quantity ?? 0), 2, ',', ' ')); ?></td>
                <td><?php echo e($line->unit ?? ''); ?></td>
                <td class="r"><?php echo e(number_format((float)($line->unit_price ?? 0), 0, ',', ' ')); ?></td>
                <td class="r">
                    <?php if(($line->discount_percent ?? 0) > 0): ?>
                        <?php echo e(number_format((float)$line->discount_percent, 0, ',', ' ')); ?> %
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td class="r"><?php echo e(number_format((float)($line->tax_rate ?? 0), 0, ',', ' ')); ?> %</td>
                <td class="r"><?php echo e(number_format((float)($line->line_total ?? 0), 0, ',', ' ')); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    
    <div class="lyt-dark-totaux-wrap">

        
        <div class="lyt-dark-totaux-qr">
            <?php if(!empty($qrDataUri)): ?>
                <img src="<?php echo e($qrDataUri); ?>" alt="QR vérification">
                <div class="lyt-dark-totaux-qr-label">Vérification</div>
            <?php endif; ?>
        </div>

        <div class="lyt-dark-totaux-fill"></div>

        
        <div class="lyt-dark-totaux-box-wrap">
            <div class="totaux-sub-row">
                <span class="ts-label">Sous-total HT</span>
                <span class="ts-val"><?php echo e(number_format($totalHT, 0, ',', ' ')); ?> <?php echo e($cur); ?></span>
            </div>
            <?php if(($document->discount_amount ?? 0) > 0): ?>
            <div class="totaux-sub-row">
                <span class="ts-label">Remise</span>
                <span class="ts-val">- <?php echo e(number_format((float)$document->discount_amount, 0, ',', ' ')); ?> <?php echo e($cur); ?></span>
            </div>
            <?php endif; ?>
            <div class="totaux-sub-row">
                <span class="ts-label">TVA (<?php echo e(number_format((float)($document->tax_rate ?? 0), 0, ',', ' ')); ?> %)</span>
                <span class="ts-val"><?php echo e(number_format($taxAmt, 0, ',', ' ')); ?> <?php echo e($cur); ?></span>
            </div>

            
            <div class="lyt-dark-grand-total">
                <span class="gt-label">Total TTC</span>
                <span class="gt-val"><?php echo e(number_format($total, 0, ',', ' ')); ?> <?php echo e($cur); ?></span>
            </div>

            <?php if($paid > 0): ?>
            <div class="totaux-sub-row" style="margin-top:4px;">
                <span class="ts-label">Déjà payé</span>
                <span class="ts-val"><?php echo e(number_format($paid, 0, ',', ' ')); ?> <?php echo e($cur); ?></span>
            </div>
            <div class="lyt-dark-grand-total" style="background:#7f1d1d;">
                <span class="gt-label">Reste à payer</span>
                <span class="gt-val" style="color:#fca5a5;"><?php echo e(number_format($reste, 0, ',', ' ')); ?> <?php echo e($cur); ?></span>
            </div>
            <?php endif; ?>
        </div>

    </div>

    
    <div class="lyt-dark-payment">
        <?php echo $__env->make('pdf.engine.blocks._payment-info', [
            'company'      => $company,
            'document'     => $document,
            'primaryColor' => $primaryColor,
        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    
    <?php if(!empty($document->notes)): ?>
    <div class="lyt-dark-notes">
        <strong>Notes :</strong> <?php echo e($document->notes); ?>

    </div>
    <?php endif; ?>

    
    <?php if(!empty($document->terms)): ?>
    <div class="lyt-dark-terms">
        <strong>Conditions :</strong> <?php echo e($document->terms); ?>

    </div>
    <?php endif; ?>

    
    <?php if(!empty($qrDataUri) && !empty($document->verification_url)): ?>
    <div class="lyt-dark-qr-block">
        <div class="qr-img-cell">
            <img src="<?php echo e($qrDataUri); ?>" alt="QR">
        </div>
        <div class="qr-text-cell">
            <strong>Vérifier l'authenticité de ce document</strong>
            <?php echo e($document->verification_url); ?>

            <?php if(!empty($integrity_hash)): ?>
                <br><span style="font-family:monospace;font-size:6px;color:#94a3b8;"><?php echo e($integrity_hash); ?></span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($showSigSection): ?>
    <div class="lyt-dark-sig-section">
        <div class="lyt-dark-sig-row">

            <?php if($sigShowEmitter): ?>
            <div class="lyt-dark-sig-col">
                <div class="sig-box">
                    <?php if(!empty($sigDigitalBase64)): ?>
                        <div class="sig-box-img"><img src="<?php echo e($sigDigitalBase64); ?>" alt="Signature"></div>
                    <?php endif; ?>
                    <?php if(!empty($sigStampBase64)): ?>
                        <div class="sig-box-stamp"><img src="<?php echo e($sigStampBase64); ?>" alt="Cachet"></div>
                    <?php endif; ?>
                </div>
                <div class="sig-box-label">Signature émetteur</div>
                <div class="sig-name"><?php echo e($company->name); ?></div>
                <div class="sig-date-line">Date : ____/____/________</div>
            </div>
            <?php endif; ?>

            <?php if($sigShowClient): ?>
            <div class="lyt-dark-sig-col">
                <div class="sig-box" style="min-height:48px;"></div>
                <div class="sig-box-label">Signature client</div>
                <?php if(!empty($document->customer)): ?>
                    <div class="sig-name"><?php echo e($document->customer->name); ?></div>
                <?php endif; ?>
                <div class="sig-date-line">Date : ____/____/________</div>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endif; ?>

    
    <?php if(!empty($company->invoice_footer)): ?>
    <div style="font-size:7px;color:#94a3b8;text-align:center;margin-bottom:6px;padding:0 10px;">
        <?php echo e($company->invoice_footer); ?>

    </div>
    <?php endif; ?>

    <div class="lyt-dark-flow-footer">
        <div class="ff-left">
            <?php echo e($company->name); ?>

            <?php if(!empty($company->address)): ?> — <?php echo e($company->address); ?><?php endif; ?>
            <?php if(!empty($company->city)): ?> <?php echo e($company->city); ?><?php endif; ?>
            <?php if(!empty($company->country)): ?>, <?php echo e($company->country); ?><?php endif; ?>
            <?php if(!empty($company->phone)): ?> · <?php echo e($company->phone); ?><?php endif; ?>
            <?php if(!empty($company->email)): ?> · <?php echo e($company->email); ?><?php endif; ?>
        </div>
        <div class="ff-right">
            Propulsé par <span class="ff-brand">IBIG FactPro</span>
        </div>
    </div>

</div>

</body>
</html>
