<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @@page { margin: 38mm 13mm 24mm 13mm; }
  * { box-sizing: border-box; }
  body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 9.5px;
    color: #1a2332;
    margin: 0; padding: 0;
    line-height: 1.5;
  }

  /* ── EN-TÊTE FIXE ────────────────────────────────────────── */
  #hdr {
    position: fixed;
    top: -38mm;
    left: -13mm;
    right: -13mm;
    height: 36mm;
    background: #ffffff;
    border-bottom: 3px solid {{ $primaryColor }};
  }
  #hdr-inner {
    height: 36mm;
    /* table layout handled by real <table> inside */
  }
  #hdr-logo-cell {
    width: 52%;
    padding: 6mm 4mm 5mm 13mm;
    vertical-align: middle;
  }
  #hdr-doc-cell {
    width: 48%;
    background: {{ $primaryColor }};
    padding: 5mm 13mm 5mm 6mm;
    vertical-align: middle;
    text-align: right;
  }

  /* ── PIED DE PAGE FIXE ───────────────────────────────────── */
  #ftr {
    position: fixed;
    bottom: -22mm;
    left: -13mm;
    right: -13mm;
    height: 20mm;
    background: #f8f9fb;
    border-top: 2px solid {{ $primaryColor }};
    padding: 3mm 13mm 0;
  }

  /* ── TABLEAU LIGNES ─────────────────────────────────────── */
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  .items-table thead tr { background: {{ $primaryColor }}; color: #ffffff; }
  .items-table thead th {
    padding: 6px 8px;
    font-size: 7px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.6px;
  }
  .items-table tbody tr { border-bottom: 1px solid #eef0f3; }
  .items-table tbody tr.alt { background: #f8f9fb; }
  .items-table tbody td { padding: 7px 8px; font-size: 9px; vertical-align: top; }
  .items-table tfoot tr { background: #1a2332; }
  .items-table tfoot td {
    padding: 7px 8px;
    font-size: 9.5px;
    font-weight: bold;
    color: #ffffff;
    text-align: right;
  }

  /* ── ÉTIQUETTE ──────────────────────────────────────────── */
  .badge {
    display: inline-block;
    padding: 2px 7px;
    border-radius: 3px;
    font-size: 7px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  /* ── SECTION TITRE ──────────────────────────────────────── */
  .section-lbl {
    font-size: 7px;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: {{ $primaryColor }};
    margin-bottom: 5px;
    padding-bottom: 3px;
    border-bottom: 1px solid {{ $primaryColor }};
  }

  /* ── LIGNE DE SÉPARATION ────────────────────────────────── */
  .divider { border: none; border-top: 1px solid #e5e7eb; margin: 10px 0; }
</style>
</head>
<body>
<!-- INVOICE-REDESIGN-V2 -->
@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

{{-- ═══════════════════════════════════════════════════════
     EN-TÊTE : logo + société | badge document
     ═══════════════════════════════════════════════════════ --}}
<div id="hdr">
  <table id="hdr-inner" style="width:100%;height:36mm;border-collapse:collapse;">
    <tr>
      {{-- Colonne gauche : logo + société --}}
      <td id="hdr-logo-cell">
        @if(!empty($logoBase64))
          <img src="{{ $logoBase64 }}"
               style="max-height:14mm;max-width:36mm;display:block;margin-bottom:3mm;">
        @endif
        <div style="font-size:14px;font-weight:bold;color:{{ $primaryColor }};line-height:1.1;letter-spacing:-0.3px;">
          {{ $company->name }}
        </div>
        @if(!empty($company->tagline))
          <div style="font-size:7.5px;color:#6b7280;margin-top:1px;font-style:italic;">{{ $company->tagline }}</div>
        @elseif(!empty($company->legal_name) && $company->legal_name !== $company->name)
          <div style="font-size:7.5px;color:#6b7280;margin-top:1px;">{{ $company->legal_name }}</div>
        @endif
        <div style="font-size:7.5px;color:#374151;margin-top:2.5mm;line-height:1.7;">
          @if(!empty($company->address)){{ $company->address }}<br>@endif
          @if(!empty($company->city)){{ $company->city }}
@if(!empty($company->postal_code)) {{ $company->postal_code }}
@endif
          @if(!empty($company->country)) · {{ $company->country }}
@endif<br>@endif
          @if(!empty($company->phone))Tél : {{ $company->phone }}
@if(!empty($company->email))  ·  {{ $company->email }}
@endif<br>@elseif(!empty($company->email)){{ $company->email }}<br>@endif
        </div>
      </td>

      {{-- Colonne droite : identité du document --}}
      <td id="hdr-doc-cell">
        <div style="font-size:9px;font-weight:bold;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:1.5px;margin-bottom:1mm;">
          {{ $document->type_label ?? strtoupper(str_replace('_',' ',$document->type ?? 'FACTURE')) }}
        </div>
        <div style="font-size:20px;font-weight:bold;color:#ffffff;line-height:1;letter-spacing:-0.5px;margin-bottom:2.5mm;">
          {{ $document->number }}
        </div>
        <div style="font-size:8px;color:rgba(255,255,255,0.85);line-height:2;">
          @if(!empty($document->issue_date))
            <span style="opacity:0.75;">Émission :</span>&nbsp; {{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}<br>
          @endif
          @if(!empty($document->due_date))
            <span style="opacity:0.75;">Échéance :</span>&nbsp; {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}<br>
          @endif
          @if(!empty($document->reference))
            <span style="opacity:0.75;">Réf :</span>&nbsp; {{ $document->reference }}
          @endif
        </div>
        @if(!empty($document->status))
          @php
            $statusColors = [
              'paid'    => ['bg'=>'#d1fae5','txt'=>'#065f46'],
              'overdue' => ['bg'=>'#fee2e2','txt'=>'#991b1b'],
              'draft'   => ['bg'=>'#f3f4f6','txt'=>'#374151'],
              'sent'    => ['bg'=>'#dbeafe','txt'=>'#1e40af'],
            ];
            $sc = $statusColors[$document->status] ?? ['bg'=>'#f3f4f6','txt'=>'#374151'];
          @endphp
          <div style="margin-top:2mm;">
            <span style="background:{{ $sc['bg'] }};color:{{ $sc['txt'] }};padding:2px 8px;border-radius:10px;font-size:7px;font-weight:bold;text-transform:uppercase;letter-spacing:0.5px;">
              {{ $document->status_label ?? $document->status }}
            </span>
          </div>
        @endif
      </td>
    </tr>
  </table>
</div>

{{-- ═══════════════════════════════════════════════════════
     PIED DE PAGE FIXE : mentions légales compactes
     ═══════════════════════════════════════════════════════ --}}
<div id="ftr">
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      <td style="font-size:6.5px;color:#6b7280;line-height:1.7;vertical-align:top;">
        <strong style="color:#374151;">{{ $company->name }}</strong>
        @if(!empty($company->address)) · {{ $company->address }}
@endif
        @if(!empty($company->city)), {{ $company->city }}
@endif
        @php
          $ftrIds = array_filter([
            !empty($company->trade_register) ? 'RCCM '.$company->trade_register : null,
            !empty($company->rccm)           ? 'RCCM '.$company->rccm           : null,
            !empty($company->tax_id)         ? 'N°Fiscal '.$company->tax_id     : null,
            !empty($company->tax_number)     ? 'N°Fiscal '.$company->tax_number : null,
            !empty($company->capital)        ? 'Capital '.$company->capital     : null,
          ]);
        @endphp
        @if(count($ftrIds)) · {{ implode(' · ', $ftrIds) }}
@endif
        @if(!empty($company->invoice_footer))
          &nbsp;·&nbsp; <em>{{ $company->invoice_footer }}</em>
        @endif
      </td>
      <td style="font-size:6.5px;color:#9ca3af;text-align:right;vertical-align:top;white-space:nowrap;">
        Document certifié · IBIG FactPro<br>
        <span style="content:counter(page);">Page </span><span style="font-weight:bold;color:#374151;"></span>
        <span style="font-size:6px;"> · </span>{{ $document->number }}
      </td>
    </tr>
  </table>
</div>

{{-- ═══════════════════════════════════════════════════════
     CORPS DU DOCUMENT
     ═══════════════════════════════════════════════════════ --}}

{{-- ── BLOC CLIENT + INFOS ──────────────────────────────── --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:12px;">
  <tr>

    {{-- Informations complémentaires du document --}}
    <td style="width:44%;vertical-align:top;padding-right:12px;">
      @if(!empty($document->subject) || !empty($document->object) || !empty($document->project_name))
      <div style="background:#f8f9fb;border-left:3px solid #e5e7eb;padding:7px 10px;margin-bottom:8px;">
        @if(!empty($document->subject) || !empty($document->object))
          <div style="font-size:7px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.5px;">Objet</div>
          <div style="font-size:8.5px;color:#1a2332;font-weight:600;margin-top:1px;">{{ $document->subject ?? $document->object }}</div>
        @endif
        @if(!empty($document->project_name))
          <div style="font-size:7px;color:#9ca3af;text-transform:uppercase;letter-spacing:0.5px;margin-top:4px;">Projet</div>
          <div style="font-size:8.5px;color:#1a2332;margin-top:1px;">{{ $document->project_name }}</div>
        @endif
      </div>
      @endif

      @php
        $curr = $document->currency ?? '';
        $payTerms = $document->payment_terms ?? null;
      @endphp
      @if($payTerms || $curr)
      <div style="font-size:8px;color:#374151;line-height:1.9;">
        @if($payTerms)
          <span style="color:#9ca3af;">Conditions :</span> {{ $payTerms }}<br>
        @endif
        @if($curr)
          <span style="color:#9ca3af;">Devise :</span> {{ $curr }}
        @endif
      </div>
      @endif
    </td>

    {{-- Destinataire --}}
    <td style="width:56%;vertical-align:top;">
      <div style="border:1px solid #e5e7eb;border-top:3px solid {{ $primaryColor }};border-radius:3px;padding:8px 12px;background:#ffffff;">
        <div style="font-size:7px;font-weight:bold;text-transform:uppercase;letter-spacing:0.8px;color:#9ca3af;margin-bottom:5px;">
          {{ $clientLabel ?? 'Facturé à' }}
        </div>
        @if($document->customer)
          <div style="font-size:12px;font-weight:bold;color:#111827;line-height:1.1;margin-bottom:3px;">
            {{ $document->customer->name ?? $document->customer->company_name ?? '—' }}
          </div>
          @if(!empty($document->customer->company_name) && !empty($document->customer->name) && $document->customer->company_name !== $document->customer->name)
            <div style="font-size:8px;color:#374151;margin-bottom:4px;">{{ $document->customer->company_name }}</div>
          @endif
          <div style="font-size:8px;color:#374151;line-height:1.9;">
            @if(!empty($document->customer->address)){{ $document->customer->address }}<br>@endif
            @if(!empty($document->customer->city)){{ $document->customer->city }}
@if(!empty($document->customer->postal_code)) {{ $document->customer->postal_code }}
@endif
            @if(!empty($document->customer->country)) · {{ $document->customer->country }}
@endif<br>@endif
            @if(!empty($document->customer->phone))<span style="color:#9ca3af;">Tél :</span> {{ $document->customer->phone }}<br>@endif
            @if(!empty($document->customer->email))<span style="color:#9ca3af;">Email :</span> {{ $document->customer->email }}<br>@endif
            @if(!empty($document->customer->tax_number))<span style="color:#9ca3af;">N° fiscal :</span> {{ $document->customer->tax_number }}
@endif
          </div>
        @else
          <div style="font-size:9px;color:#9ca3af;font-style:italic;">— Non renseigné —</div>
        @endif
      </div>
    </td>

  </tr>
</table>

{{-- ── TABLEAU DES LIGNES ───────────────────────────────── --}}
<table class="items-table">
  <thead>
    <tr>
      <th style="text-align:left;width:42%;">Description</th>
      <th style="text-align:center;width:8%;">Qté</th>
      <th style="text-align:left;width:9%;">Unité</th>
      <th style="text-align:right;width:14%;">PU HT</th>
      <th style="text-align:center;width:8%;">Remise</th>
      <th style="text-align:center;width:6%;">TVA</th>
      <th style="text-align:right;width:13%;">Total HT</th>
    </tr>
  </thead>
  <tbody>
    @forelse($document->lines ?? [] as $i => $line)
      <tr class="{{ $i % 2 !== 0 ? 'alt' : '' }}">
        <td>
          <div style="font-weight:bold;color:#111827;line-height:1.3;">{{ $line->description ?? $line->label ?? '' }}</div>
          @if(!empty($line->detail))
            <div style="font-size:7.5px;color:#6b7280;margin-top:2px;line-height:1.4;">{{ $line->detail }}</div>
          @endif
        </td>
        <td style="text-align:center;color:#374151;font-family:monospace;">{{ $line->quantity ?? 1 }}</td>
        <td style="color:#6b7280;font-size:8px;">{{ $line->unit ?? '' }}</td>
        <td style="text-align:right;font-family:monospace;color:#374151;">
          {{ number_format((float)($line->unit_price ?? 0), 0, ',', ' ') }}
        </td>
        <td style="text-align:center;font-size:8px;color:#6b7280;">
          @if(!empty($line->discount) && (float)$line->discount > 0)
            <span style="background:#fef3c7;color:#92400e;padding:1px 5px;border-radius:3px;font-weight:bold;">{{ $line->discount }}%</span>
          @else
            <span style="color:#d1d5db;">—</span>
          @endif
        </td>
        <td style="text-align:center;font-size:8px;color:#374151;">
          @if(!empty($line->tax_rate)){{ $line->tax_rate }}%@else<span style="color:#d1d5db;">—</span>@endif
        </td>
        <td style="text-align:right;font-family:monospace;font-weight:bold;color:#111827;">
          {{ number_format((float)($line->line_total ?? $line->total ?? $line->total_ht ?? ((float)($line->unit_price ?? 0) * (float)($line->quantity ?? 1))), 0, ',', ' ') }}
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="7" style="padding:16px;text-align:center;font-size:9px;color:#9ca3af;font-style:italic;">
          Aucune ligne de produit ou service
        </td>
      </tr>
    @endforelse
  </tbody>
  <tfoot>
    <tr>
      <td colspan="6" style="font-size:8px;text-transform:uppercase;letter-spacing:0.5px;text-align:right;">Total Hors Taxes</td>
      <td style="font-family:monospace;text-align:right;">
        @php
          $totalHT = collect($document->lines ?? [])->sum(fn($l) =>
            (float)($l->line_total ?? $l->total ?? $l->total_ht ?? ((float)($l->unit_price ?? 0) * (float)($l->quantity ?? 1)))
          );
        @endphp
        {{ number_format($totalHT, 0, ',', ' ') }} {{ $document->currency ?? '' }}
      </td>
    </tr>
  </tfoot>
</table>

{{-- ── TOTAUX + QR ──────────────────────────────────────── --}}
@php
  $subtotal  = (float)($document->subtotal ?? $document->total_ht ?? 0);
  $discount  = (float)($document->discount_amount ?? $document->discount ?? 0);
  $taxAmount = (float)($document->tax_amount ?? $document->total_tva ?? 0);
  $total     = (float)($document->total ?? $document->total_ttc ?? 0);
  $paid      = (float)($document->paid_amount ?? $document->amount_paid ?? 0);
  $remaining = $total - $paid;
  $currency  = $document->currency ?? '';
  $taxRate   = $document->tax_rate ?? null;
@endphp

<table style="width:100%;border-collapse:collapse;margin-bottom:12px;">
  <tr>

    {{-- QR code --}}
    <td style="width:38%;vertical-align:middle;padding-right:12px;">
      @if(!empty($qrDataUri))
      <div style="border:1px solid #e5e7eb;border-radius:4px;padding:8px 10px;background:#fafafa;text-align:center;">
        <div style="font-size:6.5px;font-weight:bold;text-transform:uppercase;color:{{ $primaryColor }};letter-spacing:0.8px;margin-bottom:5px;">
          Document certifié
        </div>
        <img src="{{ $qrDataUri }}" style="width:18mm;height:18mm;display:block;margin:0 auto 4px;">
        <div style="font-size:6px;color:#9ca3af;line-height:1.5;">
          Scannez pour vérifier l'authenticité<br>
          <span style="font-style:italic;">Anti-falsification · IBIG FactPro</span>
        </div>
      </div>
      @endif
    </td>

    {{-- Tableau des totaux --}}
    <td style="width:62%;vertical-align:top;">
      <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:4px;overflow:hidden;">

        <tr style="border-bottom:1px solid #eef0f3;">
          <td style="padding:6px 12px;font-size:8.5px;color:#374151;">Sous-total HT</td>
          <td style="padding:6px 12px;text-align:right;font-size:8.5px;font-family:monospace;font-weight:600;color:#111827;">
            {{ number_format($subtotal, 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>

        @if($discount > 0)
        <tr style="border-bottom:1px solid #eef0f3;background:#fff5f5;">
          <td style="padding:6px 12px;font-size:8.5px;color:#dc2626;">Remise accordée</td>
          <td style="padding:6px 12px;text-align:right;font-size:8.5px;font-family:monospace;color:#dc2626;font-weight:600;">
            − {{ number_format($discount, 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>
        @endif

        @if($taxAmount > 0)
        <tr style="border-bottom:1px solid #eef0f3;">
          <td style="padding:6px 12px;font-size:8.5px;color:#374151;">
            TVA{{ $taxRate ? ' ('.$taxRate.'%)' : '' }}
          </td>
          <td style="padding:6px 12px;text-align:right;font-size:8.5px;font-family:monospace;color:#374151;">
            {{ number_format($taxAmount, 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>
        @else
        <tr style="border-bottom:1px solid #eef0f3;background:#f9fafb;">
          <td colspan="2" style="padding:5px 12px;font-size:7px;color:#6b7280;font-style:italic;">
            TVA non applicable — exonération applicable
          </td>
        </tr>
        @endif

        <tr style="background:{{ $primaryColor }};">
          <td style="padding:10px 12px;font-size:11px;font-weight:bold;color:#ffffff;text-transform:uppercase;letter-spacing:0.5px;">TOTAL TTC</td>
          <td style="padding:10px 12px;text-align:right;font-size:13px;font-weight:bold;color:#ffffff;font-family:monospace;letter-spacing:-0.3px;">
            {{ number_format($total, 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>

        @if($paid > 0)
        <tr style="border-top:1px solid #eef0f3;">
          <td style="padding:5px 12px;font-size:8px;color:#059669;">Montant réglé</td>
          <td style="padding:5px 12px;text-align:right;font-size:8px;font-family:monospace;color:#059669;font-weight:600;">
            {{ number_format($paid, 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>
        <tr style="background:{{ $remaining > 0 ? '#fff5f5' : '#f0fdf4' }};">
          <td style="padding:7px 12px;font-size:9.5px;font-weight:bold;color:{{ $remaining > 0 ? '#dc2626' : '#059669' }};">
            {{ $remaining > 0 ? 'Reste à payer' : 'Soldé' }}
          </td>
          <td style="padding:7px 12px;text-align:right;font-size:10px;font-weight:bold;font-family:monospace;color:{{ $remaining > 0 ? '#dc2626' : '#059669' }};">
            {{ number_format(abs($remaining), 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>
        @endif

      </table>
    </td>
  </tr>
</table>

{{-- ── PAIEMENT + NOTES EN UNE RANGÉE ──────────────────── --}}
@php
  $hasPayment = isset($company);
  $hasNotes   = !empty($document->notes);
@endphp

@if($hasNotes)
<div style="border-left:3px solid #fbbf24;background:#fffbeb;padding:7px 11px;margin-bottom:10px;border-radius:0 3px 3px 0;">
  <div style="font-size:7px;font-weight:bold;text-transform:uppercase;color:#92400e;margin-bottom:3px;letter-spacing:0.5px;">Notes</div>
  <div style="font-size:8px;color:#374151;line-height:1.6;">{!! nl2br(e($document->notes)) !!}</div>
</div>
@endif

{{-- Moyens de paiement --}}
@include('pdf.engine.blocks._payment-info', ['company' => $company, 'document' => $document, 'primaryColor' => $primaryColor])

{{-- ── MENTIONS LÉGALES ─────────────────────────────────── --}}
@php
  $conditions = $document->terms ?? $company->default_terms ?? null;
  $isInvoice  = in_array($document->type ?? '', ['invoice','simple_invoice','proforma','deposit_invoice','export_invoice','tax_exempt_invoice','rectification_invoice','balance_invoice']);
@endphp

<div style="border-top:1px solid #e5e7eb;padding-top:8px;margin-top:14px;">
  @if($isInvoice)
  <div style="font-size:6.5px;color:#9ca3af;line-height:1.8;margin-bottom:4px;">
    En cas de retard de paiement, des pénalités au taux légal en vigueur seront appliquées de plein droit, ainsi qu'une indemnité forfaitaire pour frais de recouvrement de 40 €/40 000 FCFA. Escompte pour règlement anticipé : néant. Tout litige est soumis à la juridiction compétente du ressort du siège social de l'émetteur.
  </div>
  @endif
  @if(!empty($conditions))
  <div style="font-size:6.5px;color:#9ca3af;line-height:1.7;">{!! nl2br(e($conditions)) !!}</div>
  @endif
</div>

{{-- ── SIGNATURES ────────────────────────────────────────── --}}
@php $labels = $signatureLabels ?? ['Émetteur', 'Destinataire']; @endphp
<div style="margin-top:30px;page-break-inside:avoid;">
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      @foreach($labels as $label)
      <td style="width:{{ round(100/count($labels)) }}%;vertical-align:top;padding:0 14px;text-align:center;">
        <div style="border:1px solid #d1d5db;border-radius:5px;padding:16px 18px 14px;background:#fafafa;">
          <div style="font-size:9.5px;font-weight:bold;color:#1a2332;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">{{ $label }}</div>
          <div style="font-size:7.5px;color:#9ca3af;margin-bottom:14px;">Lu et approuvé &nbsp;·&nbsp; Date : _____________________</div>
          <div style="height:160px;"></div>
          <div style="border-top:1px solid #9ca3af;padding-top:6px;">
            <div style="font-size:7px;color:#9ca3af;">Signature et cachet</div>
          </div>
        </div>
      </td>
      @endforeach
    </tr>
  </table>
</div>

</body>
</html>
