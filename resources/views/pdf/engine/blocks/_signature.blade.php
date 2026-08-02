@php
  $labels = $signatureLabels ?? ['Émetteur', 'Destinataire'];
  $cols = count($labels);
  $width = $cols > 0 ? round(100 / $cols) . '%' : '50%';
@endphp

<table style="width:100%;border-collapse:collapse;margin-top:60px;">
  <tr>
    @foreach($labels as $label)
      <td style="width:{{ $width }};vertical-align:top;padding:0 30px;text-align:center;">
        <div style="border:1px solid #d1d5db;border-radius:4px;padding:16px 20px 14px;">
          <div style="font-size:9.5px;font-weight:bold;color:#1a2332;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">{{ $label }}</div>
          <div style="font-size:8px;color:#9ca3af;margin-bottom:16px;">Lu et approuvé · Date : _______________</div>
          <div style="height:90px;border-bottom:1px solid #9ca3af;margin:0 8px;"></div>
          <div style="font-size:7.5px;color:#d1d5db;margin-top:8px;">Signature</div>
        </div>
      </td>
    @endforeach
  </tr>
</table>
