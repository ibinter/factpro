<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title><?php echo e($document->number); ?></title>
<style>
  @page { margin: 15mm; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5px; color: #1a2332; margin: 0; padding: 0; }
  .page-footer {
    position: fixed;
    bottom: -10mm;
    left: 0; right: 0;
    font-size: 7.5px;
    color: #9ca3af;
    border-top: 1px solid #e5e7eb;
    padding-top: 4px;
    text-align: center;
  }
  .page-footer .page-num:after { content: " — Page " counter(page) " / " counter(pages); }
  .section { margin-bottom: 14px; }
  .section-title { font-size: 9px; font-weight: bold; text-transform: uppercase; background: <?php echo e($primaryColor); ?>; color: #ffffff; padding: 5px 10px; border-radius: 3px; margin-bottom: 8px; }
</style>
</head>
<body>

<?php echo $__env->make('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div class="page-footer">
  <span><?php echo e($document->type_label ?? 'Rapport de chantier'); ?> — <?php echo e($document->number); ?></span>
  <span class="page-num"></span>
</div>


<table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
  <tr>
    <td style="width:55%;vertical-align:top;padding-right:12px;">
      <?php echo $__env->make('pdf.engine.blocks._company', ['company' => $company, 'primaryColor' => $primaryColor], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </td>
    <td style="width:45%;vertical-align:top;">
      <?php echo $__env->make('pdf.engine.blocks._document-title', ['document' => $document, 'primaryColor' => $primaryColor], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </td>
  </tr>
</table>


<div class="section">
  <div class="section-title">Identification du chantier</div>
  <table style="width:100%;border-collapse:collapse;background:#f9fafb;border:1px solid #e5e7eb;">
    <tr>
      <td style="padding:6px 10px;font-size:9px;width:50%;border-right:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb;">
        <span style="color:#6b7280;">Projet :</span> <strong><?php echo e($document->project_name ?? $document->subject ?? '____________________'); ?></strong>
      </td>
      <td style="padding:6px 10px;font-size:9px;width:50%;border-bottom:1px solid #e5e7eb;">
        <span style="color:#6b7280;">Maître d'ouvrage :</span>
        <strong><?php echo e($document->customer->name ?? '____________________'); ?></strong>
      </td>
    </tr>
    <tr>
      <td style="padding:6px 10px;font-size:9px;border-right:1px solid #e5e7eb;">
        <span style="color:#6b7280;">Entreprise :</span> <strong><?php echo e($company->name ?? ''); ?></strong>
      </td>
      <td style="padding:6px 10px;font-size:9px;">
        <span style="color:#6b7280;">Date :</span>
        <strong><?php if(!empty($document->issue_date)): ?><?php echo e(\Carbon\Carbon::parse($document->issue_date)->format('d/m/Y')); ?>@else____________________@endif</strong>
      </td>
    </tr>
  </table>
</div>


<div class="section">
  <div class="section-title">Météo & Main d'œuvre du jour</div>
  <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;">
    <tr>
      <td style="padding:6px 10px;font-size:9px;width:25%;border-right:1px solid #e5e7eb;">
        <span style="color:#6b7280;">Météo :</span> <?php echo e($document->weather ?? '____________________'); ?>

      </td>
      <td style="padding:6px 10px;font-size:9px;width:25%;border-right:1px solid #e5e7eb;">
        <span style="color:#6b7280;">Temp. :</span> <?php echo e($document->temperature ?? '____'); ?> °C
      </td>
      <td style="padding:6px 10px;font-size:9px;width:25%;border-right:1px solid #e5e7eb;">
        <span style="color:#6b7280;">Ouvriers :</span> <?php echo e($document->workers_count ?? '____'); ?>

      </td>
      <td style="padding:6px 10px;font-size:9px;width:25%;">
        <span style="color:#6b7280;">Avancement global :</span> <?php echo e($document->progress_percent ?? '____'); ?> %
      </td>
    </tr>
  </table>
</div>


<div class="section">
  <div class="section-title">Travaux réalisés</div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#fee2e2;color:#7f1d1d;">
        <th style="padding:5px 10px;text-align:left;font-size:8px;text-transform:uppercase;width:40%;">Description</th>
        <th style="padding:5px 10px;text-align:left;font-size:8px;text-transform:uppercase;width:20%;">Lot / Poste</th>
        <th style="padding:5px 10px;text-align:center;font-size:8px;text-transform:uppercase;width:10%;">Avancement</th>
        <th style="padding:5px 10px;text-align:left;font-size:8px;text-transform:uppercase;width:30%;">Observations</th>
      </tr>
    </thead>
    <tbody>
      <?php $__empty_1 = true; $__currentLoopData = $document->lines ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr style="background:<?php echo e($i % 2 === 0 ? '#ffffff' : '#fef2f2'); ?>;border-bottom:1px solid #e5e7eb;">
          <td style="padding:6px 10px;font-size:9px;"><?php echo e($line->description ?? $line->label ?? ''); ?></td>
          <td style="padding:6px 10px;font-size:9px;color:#6b7280;"><?php echo e($line->unit ?? ''); ?></td>
          <td style="padding:6px 10px;text-align:center;font-size:9px;font-weight:bold;">
            <?php echo e(!empty($line->quantity) ? $line->quantity . ' %' : '—'); ?>

          </td>
          <td style="padding:6px 10px;font-size:8.5px;color:#6b7280;font-style:italic;"><?php echo e($line->note ?? ''); ?></td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
          <td colspan="4" style="padding:14px;text-align:center;color:#9ca3af;font-style:italic;">Aucun travail enregistré</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>


<div class="section">
  <div class="section-title">Problèmes rencontrés / Décisions</div>
  <div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px;background:#fff7ed;min-height:40px;font-size:9px;color:#374151;line-height:1.7;">
    <?php if(!empty($document->notes)): ?>
      <?php echo nl2br(e($document->notes)); ?>

    <?php else: ?>
      <span style="color:#9ca3af;font-style:italic;">À compléter</span>
    <?php endif; ?>
  </div>
</div>


<?php echo $__env->make('pdf.engine.blocks._signature', ['document' => $document, 'signatureLabels' => $signatureLabels ?? ['Chef de chantier', 'Conducteur de travaux']], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>
