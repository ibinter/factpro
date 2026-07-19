<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <tr>
    <td style="width:55%;vertical-align:top;"></td>
    <td style="width:45%;vertical-align:top;">
      <table style="width:100%;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:4px;">
        @php
          $subtotal   = (float)($document->subtotal ?? $document->total_ht ?? 0);
          $discount   = (float)($document->discount_amount ?? $document->discount ?? 0);
          $taxAmount  = (float)($document->tax_amount ?? $document->total_tva ?? 0);
          $total      = (float)($document->total ?? $document->total_ttc ?? 0);
          $paid       = (float)($document->paid_amount ?? $document->amount_paid ?? 0);
          $remaining  = $total - $paid;
          $currency   = $document->currency ?? '';
        @endphp

        <tr style="border-bottom:1px solid #e5e7eb;">
          <td style="padding:6px 10px;font-size:9px;color:#374151;">Sous-total HT</td>
          <td style="padding:6px 10px;text-align:right;font-size:9px;font-family:monospace;">
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
            <td style="padding:6px 10px;font-size:9px;color:#374151;">TVA</td>
            <td style="padding:6px 10px;text-align:right;font-size:9px;font-family:monospace;">
              {{ number_format($taxAmount, 0, ',', ' ') }} {{ $currency }}
            </td>
          </tr>
        @endif

        <tr style="background:{{ $primaryColor }};">
          <td style="padding:8px 10px;font-size:11px;font-weight:bold;color:#ffffff;">Total TTC</td>
          <td style="padding:8px 10px;text-align:right;font-size:11px;font-weight:bold;color:#ffffff;font-family:monospace;">
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
              {{ $remaining > 0 ? 'Reste à payer' : 'Soldé' }}
            </td>
            <td style="padding:6px 10px;text-align:right;font-size:10px;font-weight:bold;font-family:monospace;color:{{ $remaining > 0 ? '#dc2626' : '#059669' }};">
              {{ number_format(abs($remaining), 0, ',', ' ') }} {{ $currency }}
            </td>
          </tr>
        @endif
      </table>
    </td>
  </tr>
</table>
