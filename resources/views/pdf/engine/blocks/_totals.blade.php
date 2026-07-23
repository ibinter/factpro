@php
  $subtotal  = (float)($document->subtotal ?? $document->total_ht ?? 0);
  $discount  = (float)($document->discount_amount ?? $document->discount ?? 0);
  $taxAmount = (float)($document->tax_amount ?? $document->total_tva ?? 0);
  $total     = (float)($document->total ?? $document->total_ttc ?? 0);
  $paid      = (float)($document->paid_amount ?? $document->amount_paid ?? 0);
  $remaining = $total - $paid;
  $currency  = $document->currency ?? '';
@endphp

<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <tr>

    {{-- Colonne gauche : QR code anti-falsification --}}
    <td style="width:52%;vertical-align:bottom;padding-right:18px;">
      @if(!empty($qrDataUri))
      <div style="display:inline-block;border:1px solid #e5e7eb;border-radius:6px;padding:8px 12px;background:#fafafa;">
        <table style="border-collapse:collapse;">
          <tr>
            <td style="vertical-align:middle;padding-right:10px;">
              <img src="{{ $qrDataUri }}" style="width:22mm;height:22mm;display:block;">
            </td>
            <td style="vertical-align:middle;">
              <div style="font-size:8px;font-weight:bold;color:#374151;margin-bottom:3px;">
                Document certifié
              </div>
              <div style="font-size:7px;color:#6b7280;line-height:1.5;">
                Scannez ce QR code<br>pour vérifier l'authenticité<br>de ce document.
              </div>
              @if(!empty($document->verification_url))
              <div style="font-size:6.5px;color:#9ca3af;margin-top:4px;word-break:break-all;">
                {{ $document->verification_url }}
              </div>
              @endif
            </td>
          </tr>
        </table>
        <div style="font-size:6.5px;color:#9ca3af;margin-top:5px;border-top:1px solid #e5e7eb;padding-top:3px;text-align:center;">
          Certifié IBIG FactPro · Anti-falsification
        </div>
      </div>
      @endif
    </td>

    {{-- Colonne droite : tableau des totaux --}}
    <td style="width:48%;vertical-align:top;">
      <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:4px;">

        <tr style="border-bottom:1px solid #e5e7eb;">
          <td style="padding:6px 10px;font-size:9px;color:#374151;">Sous-total HT</td>
          <td style="padding:6px 10px;text-align:right;font-size:9px;font-family:monospace;font-weight:600;">
            {{ number_format($subtotal, 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>

        @if($discount > 0)
        <tr style="border-bottom:1px solid #e5e7eb;">
          <td style="padding:6px 10px;font-size:9px;color:#dc2626;">Remise</td>
          <td style="padding:6px 10px;text-align:right;font-size:9px;font-family:monospace;color:#dc2626;">
            - {{ number_format($discount, 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>
        @endif

        @if($taxAmount > 0)
        <tr style="border-bottom:1px solid #e5e7eb;">
          @php $taxRate = $document->tax_rate ?? null; @endphp
          <td style="padding:6px 10px;font-size:9px;color:#374151;">
            TVA@if($taxRate) ({{ $taxRate }}%)@endif
          </td>
          <td style="padding:6px 10px;text-align:right;font-size:9px;font-family:monospace;">
            {{ number_format($taxAmount, 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>
        @else
        <tr style="border-bottom:1px solid #e5e7eb;">
          <td style="padding:6px 10px;font-size:9px;color:#6b7280;font-style:italic;" colspan="2">
            TVA non applicable — Art. 293B CGI ou exonération OHADA
          </td>
        </tr>
        @endif

        <tr style="background:{{ $primaryColor }};">
          <td style="padding:9px 10px;font-size:12px;font-weight:bold;color:#ffffff;">TOTAL TTC</td>
          <td style="padding:9px 10px;text-align:right;font-size:12px;font-weight:bold;color:#ffffff;font-family:monospace;letter-spacing:0.3px;">
            {{ number_format($total, 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>

        @if($paid > 0)
        <tr style="border-top:1px solid #e5e7eb;">
          <td style="padding:6px 10px;font-size:9px;color:#059669;">Montant payé</td>
          <td style="padding:6px 10px;text-align:right;font-size:9px;font-family:monospace;color:#059669;">
            {{ number_format($paid, 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>
        <tr>
          <td style="padding:6px 10px;font-size:10px;font-weight:bold;color:{{ $remaining > 0 ? '#dc2626' : '#059669' }};">
            {{ $remaining > 0 ? 'Reste à payer' : '✓ Soldé' }}
          </td>
          <td style="padding:6px 10px;text-align:right;font-size:10px;font-weight:bold;font-family:monospace;color:{{ $remaining > 0 ? '#dc2626' : '#059669' }};">
            {{ number_format(abs($remaining), 0, ',', ' ') }} {{ $currency }}
          </td>
        </tr>
        @endif

        {{-- Échéance --}}
        @if(!empty($document->due_date))
        <tr style="border-top:1px dashed #e5e7eb;background:#fafafa;">
          <td style="padding:5px 10px;font-size:8px;color:#6b7280;">Date d'échéance</td>
          <td style="padding:5px 10px;text-align:right;font-size:8px;font-weight:600;color:#374151;">
            {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}
          </td>
        </tr>
        @endif

      </table>
    </td>

  </tr>
</table>
