<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?php echo e($document->type_label ?? 'Document'); ?> <?php echo e($document->number ?? ''); ?></title>
<style>
@page { margin: 42mm 13mm 22mm 13mm; }
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #333; }

/* ── HEADER FIXE ── */
#header {
    position: fixed;
    top: -42mm; left: -13mm; right: -13mm;
    height: 40mm;
    background: #fff;
    border-bottom: 3px solid <?php echo e($primaryColor); ?>;
    padding: 5mm 13mm;
}
#header table { width: 100%; }
#header .co-name { font-size: 15px; font-weight: bold; color: #002D5B; }
#header .co-info { font-size: 7.5px; color: #555; line-height: 1.6; margin-top: 1mm; }
#header .doc-type { font-size: 20px; font-weight: bold; color: <?php echo e($primaryColor); ?>; text-transform: uppercase; text-align: right; }
#header .doc-num  { font-size: 8.5px; color: #888; text-align: right; margin-top: 1mm; }
#header .doc-status { font-size: 7.5px; font-weight: bold; text-transform: uppercase; text-align: right;
    <?php
        $sc = ['paid'=>['#D1FAE5','#065F46'],'draft'=>['#F3F4F6','#6B7280'],'sent'=>['#DBEAFE','#1E40AF'],
               'overdue'=>['#FEE2E2','#991B1B'],'cancelled'=>['#FEF3C7','#92400E'],'partial'=>['#EDE9FE','#5B21B6']];
        $sca = $sc[$document->status ?? ''] ?? ['#F3F4F6','#374151'];
    ?>
    background: <?php echo e($sca[0]); ?>; color: <?php echo e($sca[1]); ?>; padding: 1mm 3mm; display: inline-block;
}

/* ── FILIGRANE ── */
#watermark {
    position: fixed; top: 60mm; left: 30mm;
    font-size: 52px; font-weight: bold;
    color: rgba(200,0,0,.10);
    transform: rotate(-35deg);
    white-space: nowrap; z-index: 0;
}

/* ── FOOTER FIXE ── */
#footer {
    position: fixed;
    bottom: -22mm; left: -13mm; right: -13mm;
    height: 20mm;
    border-top: 1px solid #e5e7eb;
    padding: 2mm 13mm 0 13mm;
    font-size: 7px; color: #888;
}
#footer table { width: 100%; }
#footer .ft-brand { color: <?php echo e($primaryColor); ?>; font-weight: bold; }

/* ── FILET ACCENT ── */
.accent-rule { height: 3px; background: <?php echo e($primaryColor); ?>; margin-bottom: 4mm; }

/* ── SECTION ADRESSES ── */
.sect-label { font-size: 7px; font-weight: bold; text-transform: uppercase; color: <?php echo e($primaryColor); ?>;
    letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; padding-bottom: 1mm; margin-bottom: 2mm; }
.info-card { background: #F8F9FA; padding: 3mm 4mm; }
.client-card { background: #fff; border-left: 3px solid <?php echo e($primaryColor); ?>; padding: 3mm 4mm; }
.client-name { font-size: 11px; font-weight: bold; color: #002D5B; margin-bottom: 1mm; }
.client-detail { font-size: 8px; color: #555; line-height: 1.6; }
.meta-label { color: #888; font-size: 8px; }
.meta-value { font-weight: bold; font-size: 8.5px; color: #222; }

/* ── OBJET ── */
.subject-bar { background: #EFF6FF; border-left: 3px solid <?php echo e($primaryColor); ?>;
    padding: 2mm 3mm; margin-bottom: 4mm; font-size: 8.5px; color: #1e3a5f; }

/* ── TABLEAU LIGNES ── */
.lines-table { width: 100%; border-collapse: collapse; margin-bottom: 4mm; }
.lines-table thead tr { background: #002D5B; color: #fff; }
.lines-table thead th { padding: 2mm 2mm; font-size: 7.5px; text-align: left; }
.lines-table thead th.r { text-align: right; }
.lines-table tbody tr.even { background: #F8F9FA; }
.lines-table tbody td { padding: 2mm 2mm; font-size: 8px; color: #333; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
.lines-table tbody td.r { text-align: right; }
.lines-table .desc { font-size: 7.5px; color: #666; }
.discount-badge { color: #b45309; font-size: 7px; }

/* ── TOTAUX + QR ── */
.bottom-row { display: table; width: 100%; margin-bottom: 4mm; }
.bottom-qr  { display: table-cell; width: 28mm; vertical-align: bottom; padding-right: 4mm; }
.bottom-qr img { width: 24mm; height: 24mm; }
.bottom-qr .qr-lbl { font-size: 6px; color: #888; text-align: center; margin-top: 1mm; }
.bottom-totals { display: table-cell; vertical-align: top; }
.totals-table { width: 100%; font-size: 8.5px; }
.totals-table td { padding: 1mm 2mm; }
.totals-table td:last-child { text-align: right; font-weight: bold; }
.totals-table .row-total td { font-size: 12px; color: <?php echo e($primaryColor); ?>; border-top: 2px solid <?php echo e($primaryColor); ?>; padding-top: 2mm; }
.totals-table .row-due td { color: #c00; font-size: 10px; }

/* ── PAIEMENT ── */
.payment-section { border: 1px solid #e5e7eb; padding: 3mm 4mm; margin-bottom: 4mm; font-size: 8px; }
.payment-title { font-weight: bold; color: <?php echo e($primaryColor); ?>; margin-bottom: 1.5mm; font-size: 8.5px; }

/* ── NOTES / CONDITIONS ── */
.notes-box { font-size: 8px; color: #555; margin-bottom: 3mm; border-top: 1px solid #e5e7eb; padding-top: 2mm; }
.notes-title { font-weight: bold; color: #333; margin-bottom: 1mm; }

/* ── SIGNATURES ── */
.sig-row { display: table; width: 100%; margin-top: 6mm; }
.sig-col { display: table-cell; width: 50%; padding: 0 4mm; }
.sig-label-top { font-size: 8px; font-weight: bold; color: #333; margin-bottom: 1mm; }
.sig-photo { height: 16mm; border: 1px dashed #ccc; text-align: center;
    font-size: 7px; color: #aaa; padding-top: 4mm; margin-bottom: 2mm; }
.sig-line { border-bottom: 1px solid #333; height: 8mm; margin-bottom: 1.5mm; }
.sig-meta { font-size: 7px; color: #555; line-height: 1.7; }
</style>
</head>
<body>


<div id="header">
    <table><tr>
        <td style="width:60%;vertical-align:middle;">
            <?php if($logoBase64 ?? null): ?>
                <img src="<?php echo e($logoBase64); ?>" style="max-height:14mm;max-width:45mm;display:block;margin-bottom:1.5mm;" alt="Logo"/>
            <?php endif; ?>
            <div class="co-name"><?php echo e($company->name ?? ''); ?></div>
            <div class="co-info">
                <?php if(!empty($company->address)): ?><?php echo e($company->address); ?><br/><?php endif; ?>
                <?php if(!empty($company->city)): ?><?php echo e($company->city); ?><?php if(!empty($company->country)): ?>, <?php echo e($company->country); ?><?php endif; ?><br/><?php endif; ?>
                <?php if(!empty($company->phone)): ?>Tél : <?php echo e($company->phone); ?><?php if(!empty($company->email)): ?>  |  <?php echo e($company->email); ?><?php endif; ?><br/><?php endif; ?>
                <?php if(!empty($company->tax_id)): ?>N° Fiscal : <?php echo e($company->tax_id); ?><?php endif; ?>
            </div>
        </td>
        <td style="width:40%;vertical-align:middle;">
            <div class="doc-type"><?php echo e($document->type_label ?? 'Document'); ?></div>
            <div class="doc-num">N° <?php echo e($document->number ?? '—'); ?></div>
            <div class="doc-status"><?php echo e($document->status_label ?? ''); ?></div>
        </td>
    </tr></table>
</div>


<?php if($watermark ?? false): ?>
<div id="watermark"><?php echo e($watermark); ?></div>
<?php endif; ?>


<div id="footer">
    <table><tr>
        <td><?php echo e($company->name ?? ''); ?> — <?php echo e($company->legal_name ?? ''); ?></td>
        <td style="text-align:center;"><?php echo e($document->number ?? ''); ?></td>
        <td style="text-align:right;"><span class="ft-brand">IBIG FactPro</span></td>
    </tr></table>
</div>


<div id="main">

    <div class="accent-rule"></div>

    
    <table style="width:100%;margin-bottom:4mm;">
        <tr>
            <td style="width:45%;vertical-align:top;padding-right:4mm;">
                <div class="info-card">
                    <div class="sect-label">Informations du document</div>
                    <table style="width:100%;">
                        <tr><td class="meta-label">Date d'émission</td><td class="meta-value"><?php echo e($document->issue_date ? \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') : '—'); ?></td></tr>
                        <?php if(!empty($document->due_date)): ?>
                        <tr><td class="meta-label">Échéance</td><td class="meta-value"><?php echo e(\Carbon\Carbon::parse($document->due_date)->format('d/m/Y')); ?></td></tr>
                        <?php endif; ?>
                        <?php if(!empty($document->reference)): ?>
                        <tr><td class="meta-label">Référence</td><td class="meta-value"><?php echo e($document->reference); ?></td></tr>
                        <?php endif; ?>
                        <tr><td class="meta-label">Devise</td><td class="meta-value"><?php echo e($document->currency ?? 'XOF'); ?></td></tr>
                    </table>
                </div>
            </td>
            <td style="width:55%;vertical-align:top;padding-left:4mm;">
                <div class="client-card">
                    <div class="sect-label">Destinataire</div>
                    <div class="client-name"><?php echo e($document->customer->name ?? '—'); ?></div>
                    <div class="client-detail">
                        <?php if(!empty($document->customer->address)): ?><?php echo e($document->customer->address); ?><br/><?php endif; ?>
                        <?php if(!empty($document->customer->city)): ?><?php echo e($document->customer->city); ?><?php if(!empty($document->customer->country)): ?>, <?php echo e($document->customer->country); ?><?php endif; ?><br/><?php endif; ?>
                        <?php if(!empty($document->customer->phone)): ?>Tél : <?php echo e($document->customer->phone); ?><br/><?php endif; ?>
                        <?php if(!empty($document->customer->email)): ?><?php echo e($document->customer->email); ?><br/><?php endif; ?>
                        <?php if(!empty($document->customer->tax_number)): ?>N° Fiscal : <?php echo e($document->customer->tax_number); ?><?php endif; ?>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    
    <?php if(!empty($document->subject)): ?>
    <div class="subject-bar"><strong>Objet :</strong> <?php echo e($document->subject); ?></div>
    <?php endif; ?>

    
    <?php $totalHT = collect($document->lines)->sum(fn($l) => (float)($l->line_total ?? 0)); ?>
    <table class="lines-table">
        <thead><tr>
            <th style="width:4%">#</th>
            <th style="width:38%">Désignation</th>
            <th class="r" style="width:10%">Qté</th>
            <th class="r" style="width:13%">P.U. HT</th>
            <th class="r" style="width:10%">Remise</th>
            <th class="r" style="width:13%">TVA</th>
            <th class="r" style="width:12%">Total HT</th>
        </tr></thead>
        <tbody>
        <?php $__currentLoopData = $document->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr class="<?php echo e($index % 2 === 0 ? 'even' : ''); ?>">
            <td><?php echo e($index + 1); ?></td>
            <td><strong><?php echo e($line->description ?? $line->name ?? '—'); ?></strong>
                <?php if(!empty($line->note)): ?><br/><span class="desc"><?php echo e($line->note); ?></span><?php endif; ?>
            </td>
            <td class="r"><?php echo e(number_format((float)($line->quantity ?? 1), 2)); ?></td>
            <td class="r"><?php echo e(number_format((float)($line->unit_price ?? 0), 0, ',', ' ')); ?></td>
            <td class="r">
                <?php if((float)($line->discount_percent ?? 0) > 0): ?>
                    <span class="discount-badge"><?php echo e(number_format((float)$line->discount_percent, 1)); ?>%</span>
                <?php else: ?> —<?php endif; ?>
            </td>
            <td class="r"><?php if(!empty($line->tax_rate)): ?><?php echo e(number_format((float)$line->tax_rate, 0)); ?>%<?php else: ?> —<?php endif; ?></td>
            <td class="r"><?php echo e(number_format((float)($line->line_total ?? 0), 0, ',', ' ')); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td colspan="6" style="text-align:right;font-size:8px;color:#888;padding:2mm 2mm;">Total HT</td>
            <td class="r"><?php echo e(number_format($totalHT, 0, ',', ' ')); ?></td>
        </tr>
        </tbody>
    </table>

    
    <div class="bottom-row">
        <div class="bottom-qr">
            <?php if($qrDataUri ?? null): ?>
                <img src="<?php echo e($qrDataUri); ?>" alt="QR"/>
                <div class="qr-lbl">Vérification</div>
            <?php endif; ?>
        </div>
        <div class="bottom-totals">
            <table class="totals-table">
                <tr><td>Sous-total HT</td><td><?php echo e(number_format((float)($document->subtotal ?? $totalHT), 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td></tr>
                <?php if((float)($document->tax_amount ?? 0) > 0): ?>
                <tr><td>TVA</td><td><?php echo e(number_format((float)$document->tax_amount, 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td></tr>
                <?php endif; ?>
                <?php if((float)($document->discount ?? 0) > 0): ?>
                <tr><td>Remise globale</td><td>- <?php echo e(number_format((float)$document->discount, 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td></tr>
                <?php endif; ?>
                <tr class="row-total">
                    <td>TOTAL TTC</td>
                    <td><?php echo e(number_format((float)($document->total ?? 0), 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td>
                </tr>
                <?php if((float)($document->amount_paid ?? 0) > 0): ?>
                <tr><td>Montant payé</td><td>- <?php echo e(number_format((float)$document->amount_paid, 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td></tr>
                <tr class="row-due">
                    <td>RESTE À PAYER</td>
                    <td><?php echo e(number_format((float)(($document->total ?? 0) - ($document->amount_paid ?? 0)), 0, ',', ' ')); ?> <?php echo e($document->currency ?? 'XOF'); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    
    <?php echo $__env->make('pdf.engine.blocks._payment-info', ['company' => $company, 'document' => $document, 'primaryColor' => $primaryColor], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    
    <?php if(!empty($document->notes)): ?>
    <div class="notes-box"><div class="notes-title">Notes</div><?php echo e($document->notes); ?></div>
    <?php endif; ?>

    
    <?php if(!empty($document->terms)): ?>
    <div class="notes-box"><div class="notes-title">Conditions générales</div><?php echo e($document->terms); ?></div>
    <?php endif; ?>

    
    <?php
        $sigShowEmitter = $sigConfig['show_emitter'] ?? true;
        $sigShowClient  = $sigConfig['show_client']  ?? true;
        $sigMode        = $sigConfig['mode']          ?? 'photo';
        $sigMention     = $sigConfig['mention']       ?? 'Lu et approuvé';
        $emitterLabel   = $sigConfig['emitter_label'] ?? 'Émetteur';
        $clientLabel    = $sigConfig['client_label']  ?? 'Client';
    ?>
    <?php if($sigShowEmitter || $sigShowClient): ?>
    <div class="sig-row">
        <?php if($sigShowEmitter): ?>
        <div class="sig-col">
            <div class="sig-label-top"><?php echo e($emitterLabel); ?></div>
            <div class="sig-photo">
                <?php if($sigDigitalBase64 ?? null): ?>
                    <img src="<?php echo e($sigDigitalBase64); ?>" style="max-height:14mm;max-width:40mm;" alt="Signature"/>
                <?php elseif($sigStampBase64 ?? null): ?>
                    <img src="<?php echo e($sigStampBase64); ?>" style="max-height:14mm;max-width:40mm;" alt="Cachet"/>
                <?php else: ?>
                    Signature / Cachet
                <?php endif; ?>
            </div>
            <?php if($sigMode === 'mention'): ?><div style="font-size:7.5px;text-align:center;margin-bottom:1mm;"><?php echo e($sigMention); ?></div><?php endif; ?>
            <div class="sig-line"></div>
            <div class="sig-meta">Nom : ___________________________<br/>Date : __________________________</div>
        </div>
        <?php endif; ?>
        <?php if($sigShowClient): ?>
        <div class="sig-col">
            <div class="sig-label-top"><?php echo e($clientLabel); ?></div>
            <div class="sig-photo">Signature / Cachet</div>
            <?php if($sigMode === 'mention'): ?><div style="font-size:7.5px;text-align:center;margin-bottom:1mm;"><?php echo e($sigMention); ?></div><?php endif; ?>
            <div class="sig-line"></div>
            <div class="sig-meta">Nom : ___________________________<br/>Date : __________________________</div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>
</body>
</html>
