@php
  $labels = $signatureLabels ?? ['Émetteur', 'Destinataire'];
  $cols = count($labels);
  $width = $cols > 0 ? round(100 / $cols) . '%' : '50%';
@endphp

<div style="margin-top:80px;page-break-inside:avoid;">
  <table style="width:100%;border-collapse:collapse;">
    <tr>
      @foreach($labels as $label)
        <td style="width:{{ $width }};vertical-align:top;padding:0 18px;text-align:center;">
          <div style="border:1px solid #d1d5db;border-radius:5px;padding:18px 20px 14px;background:#fafafa;">
            <div style="font-size:10px;font-weight:bold;color:#1a2332;text-transform:uppercase;letter-spacing:1.2px;margin-bottom:8px;">{{ $label }}</div>
            <div style="font-size:8px;color:#9ca3af;margin-bottom:20px;">Lu et approuvé &nbsp;·&nbsp; Date : _____________________</div>
            <div style="height:110px;"></div>
            <div style="border-top:1px solid #6b7280;margin:0 12px;padding-top:8px;">
              <div style="font-size:7.5px;color:#9ca3af;">Signature et cachet</div>
            </div>
          </div>
        </td>
      @endforeach
    </tr>
  </table>
</div>
