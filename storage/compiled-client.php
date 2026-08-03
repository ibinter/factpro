<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <tr>
    
    <td style="width:48%;vertical-align:top;padding-right:14px;">
      <div style="background:#f8f9fb;border:1px solid #e5e7eb;border-radius:3px;padding:9px 12px;">
        <div style="font-size:7.5px;text-transform:uppercase;color:#9ca3af;letter-spacing:1px;margin-bottom:6px;font-weight:bold;">
          Informations du document
        </div>
        <table style="width:100%;border-collapse:collapse;">
          <?php if(!empty($document->issue_date)): ?>
          <tr>
            <td style="font-size:8px;color:#6b7280;padding-bottom:4px;width:45%;">Date d'émission</td>
            <td style="font-size:8.5px;color:#111827;font-weight:600;padding-bottom:4px;">
              <?php echo e(\Carbon\Carbon::parse($document->issue_date)->format('d/m/Y')); ?>

            </td>
          </tr>
          <?php endif; ?>
          <?php if(!empty($document->due_date)): ?>
          <tr>
            <td style="font-size:8px;color:#6b7280;padding-bottom:4px;"><?php echo e(in_array($document->type ?? '', ['quote','estimate']) ? 'Valide jusqu\'au' : 'Date d\'échéance'); ?></td>
            <td style="font-size:8.5px;color:#111827;font-weight:600;padding-bottom:4px;">
              <?php echo e(\Carbon\Carbon::parse($document->due_date)->format('d/m/Y')); ?>

            </td>
          </tr>
          <?php endif; ?>
          <?php if(!empty($document->reference)): ?>
          <tr>
            <td style="font-size:8px;color:#6b7280;padding-bottom:4px;">Référence</td>
            <td style="font-size:8.5px;color:#111827;font-weight:600;padding-bottom:4px;"><?php echo e($document->reference); ?></td>
          </tr>
          <?php endif; ?>
          <?php if(!empty($document->subject) || !empty($document->object)): ?>
          <tr>
            <td style="font-size:8px;color:#6b7280;padding-bottom:4px;">Objet</td>
            <td style="font-size:8.5px;color:#374151;padding-bottom:4px;"><?php echo e($document->subject ?? $document->object); ?></td>
          </tr>
          <?php endif; ?>
          <?php if(!empty($document->project_name)): ?>
          <tr>
            <td style="font-size:8px;color:#6b7280;padding-bottom:4px;">Projet</td>
            <td style="font-size:8.5px;color:#374151;padding-bottom:4px;"><?php echo e($document->project_name); ?></td>
          </tr>
          <?php endif; ?>
          <?php $curr = $document->currency ?? ''; ?>
          <?php if(!empty($curr)): ?>
          <tr>
            <td style="font-size:8px;color:#6b7280;">Devise</td>
            <td style="font-size:8.5px;color:#374151;"><?php echo e($curr); ?></td>
          </tr>
          <?php endif; ?>
        </table>
      </div>
    </td>

    
    <td style="width:52%;vertical-align:top;">
      <div style="border:1px solid #e5e7eb;border-left:3px solid <?php echo e($primaryColor); ?>;border-radius:3px;padding:9px 12px;background:#f9fafb;">
        <div style="font-size:7.5px;text-transform:uppercase;color:#9ca3af;letter-spacing:1px;margin-bottom:6px;font-weight:bold;">
          <?php echo e($clientLabel ?? 'Destinataire / Client'); ?>

        </div>
        <?php if($document->customer): ?>
          <div style="font-size:12px;font-weight:bold;color:#111827;line-height:1.2;">
            <?php echo e($document->customer->name ?? $document->customer->company_name ?? '—'); ?>

          </div>
          <?php if(!empty($document->customer->company_name) && !empty($document->customer->name) && $document->customer->company_name !== $document->customer->name): ?>
            <div style="font-size:9px;color:#374151;margin-top:1px;"><?php echo e($document->customer->company_name); ?></div>
          <?php endif; ?>
          <div style="font-size:8.5px;color:#374151;margin-top:6px;line-height:1.75;">
            <?php if(!empty($document->customer->address)): ?><?php echo e($document->customer->address); ?><br><?php endif; ?>
            <?php if(!empty($document->customer->city)): ?><?php echo e($document->customer->city); ?><?php if(!empty($document->customer->postal_code)): ?> – <?php echo e($document->customer->postal_code); ?><?php endif; ?>
            <?php if(!empty($document->customer->country)): ?> · <?php echo e($document->customer->country); ?><?php endif; ?><br><?php endif; ?>
            <?php if(!empty($document->customer->phone)): ?><span style="color:#9ca3af;">Tél :</span> <?php echo e($document->customer->phone); ?><br><?php endif; ?>
            <?php if(!empty($document->customer->email)): ?><span style="color:#9ca3af;">Email :</span> <?php echo e($document->customer->email); ?><br><?php endif; ?>
            <?php if(!empty($document->customer->tax_number)): ?><span style="color:#9ca3af;">N° fiscal :</span> <?php echo e($document->customer->tax_number); ?><br><?php endif; ?>
          </div>
        <?php else: ?>
          <div style="font-size:10px;color:#9ca3af;font-style:italic;margin-top:8px;">— Non renseigné —</div>
        <?php endif; ?>
      </div>
    </td>
  </tr>
</table>
