<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $document->number }}</title>
<style>
  @page { margin: 15mm; }
  * { box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a2332; margin: 0; padding: 0; background: #f9fafb; }
</style>
</head>
<body>

@include('pdf.engine.blocks._watermark', ['watermark' => $watermark ?? null])

{{-- En-tête --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <tr>
    <td style="width:50%;vertical-align:top;padding-right:12px;">
      @include('pdf.engine.blocks._company', ['company' => $company, 'primaryColor' => $primaryColor])
    </td>
    <td style="width:50%;vertical-align:top;text-align:right;">
      <div style="font-size:8.5px;color:#6b7280;">
        @if(!empty($document->issue_date))Date : {{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}<br>@endif
      </div>
    </td>
  </tr>
</table>

<hr style="border:none;border-top:2px solid {{ $primaryColor }};margin-bottom:20px;">

{{-- Grand titre centré --}}
<div style="text-align:center;margin-bottom:20px;">
  <div style="font-size:20px;font-weight:bold;text-transform:uppercase;letter-spacing:3px;color:{{ $primaryColor }};">
    REÇU DE PAIEMENT
  </div>
  <div style="font-size:13px;color:#374151;margin-top:4px;">N° {{ $document->number }}</div>
</div>

{{-- Émetteur / Destinataire --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
  <tr>
    <td style="width:48%;vertical-align:top;padding-right:10px;">
      <div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px 12px;background:#ffffff;">
        <div style="font-size:8px;text-transform:uppercase;color:#6b7280;margin-bottom:4px;">Émetteur</div>
        <div style="font-size:11px;font-weight:bold;">{{ $company->name ?? '' }}</div>
        <div style="font-size:8.5px;color:#374151;margin-top:3px;line-height:1.6;">
          @if(!empty($company->address)){{ $company->address }}<br>@endif
          @if(!empty($company->phone))Tél: {{ $company->phone }}<br>@endif
        </div>
      </div>
    </td>
    <td style="width:4%;"></td>
    <td style="width:48%;vertical-align:top;">
      <div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px 12px;background:#ffffff;">
        <div style="font-size:8px;text-transform:uppercase;color:#6b7280;margin-bottom:4px;">Reçu de</div>
        @if($document->customer)
          <div style="font-size:11px;font-weight:bold;">{{ $document->customer->name ?? '' }}</div>
          <div style="font-size:8.5px;color:#374151;margin-top:3px;line-height:1.6;">
            @if(!empty($document->customer->address)){{ $document->customer->address }}<br>@endif
            @if(!empty($document->customer->phone))Tél: {{ $document->customer->phone }}@endif
          </div>
        @else
          <div style="font-size:10px;color:#9ca3af;font-style:italic;">— Non renseigné —</div>
        @endif
      </div>
    </td>
  </tr>
</table>

{{-- Montant en grand --}}
@php
  $total = (float)($document->total ?? $document->total_ttc ?? $document->paid_amount ?? 0);
  $currency = $document->currency ?? '';
@endphp
<div style="background:{{ $primaryColor }};color:#ffffff;border-radius:6px;padding:18px;text-align:center;margin-bottom:16px;">
  <div style="font-size:10px;text-transform:uppercase;opacity:0.8;margin-bottom:6px;">Montant reçu</div>
  <div style="font-size:28px;font-weight:bold;font-family:monospace;letter-spacing:1px;">
    {{ number_format($total, 0, ',', ' ') }} {{ $currency }}
  </div>
  @if(!empty($document->amount_in_words))
    <div style="font-size:9px;margin-top:6px;opacity:0.9;font-style:italic;">
      {{ $document->amount_in_words }}
    </div>
  @endif
</div>

{{-- Mode de paiement --}}
<div style="border:1px solid #e5e7eb;border-radius:4px;padding:10px 14px;margin-bottom:14px;background:#ffffff;">
  <div style="font-size:8.5px;font-weight:bold;color:{{ $primaryColor }};text-transform:uppercase;margin-bottom:8px;letter-spacing:0.5px;">
    Mode de paiement
  </div>
  @php
    $paymentMethod = $document->payment_method ?? '';
    $modes = ['cash' => 'Espèces', 'transfer' => 'Virement', 'card' => 'Carte bancaire', 'mobile_money' => 'Mobile Money', 'check' => 'Chèque'];
  @endphp
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      @foreach($modes as $key => $label)
        <td style="width:20%;text-align:center;font-size:8.5px;">
          <div style="width:12px;height:12px;border:1px solid {{ $paymentMethod === $key ? $primaryColor : '#9ca3af' }};border-radius:50%;background:{{ $paymentMethod === $key ? $primaryColor : '#ffffff' }};margin:0 auto 3px;"></div>
          {{ $label }}
        </td>
      @endforeach
    </tr>
  </table>
  @if(!empty($document->payment_reference))
    <div style="font-size:8px;color:#6b7280;margin-top:6px;">Référence : {{ $document->payment_reference }}</div>
  @endif
</div>

{{-- Date et lieu --}}
<div style="font-size:8.5px;color:#374151;margin-bottom:14px;">
  <span style="color:#6b7280;">Fait à</span>
  @if(!empty($company->city)){{ $company->city }}@else____________________@endif,
  <span style="color:#6b7280;">le</span>
  @if(!empty($document->issue_date)){{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}@else____________________@endif
</div>

{{-- QR --}}
@include('pdf.engine.blocks._qr-auth', ['qrDataUri' => $qrDataUri ?? null, 'document' => $document])

{{-- Signature --}}
<div style="text-align:center;margin-top:16px;">
  <div style="display:inline-block;border-top:1px solid #9ca3af;padding-top:6px;min-width:200px;">
    <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">Signature de l'émetteur</div>
    <div style="height:40px;"></div>
    <div style="font-size:8px;color:#9ca3af;">Cachet et signature</div>
  </div>
</div>

{{-- Conditions --}}
@include('pdf.engine.blocks._conditions', ['company' => $company, 'document' => $document])

</body>
</html>
