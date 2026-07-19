@php
  $labels = $signatureLabels ?? ['Émetteur', 'Destinataire'];
  $cols = count($labels);
  $width = $cols > 0 ? round(100 / $cols) . '%' : '50%';
@endphp

<table style="width:100%;border-collapse:collapse;margin-top:20px;">
  <tr>
    @foreach($labels as $label)
      <td style="width:{{ $width }};vertical-align:top;padding:0 8px;text-align:center;">
        <div style="border-top:1px solid #9ca3af;padding-top:6px;">
          <div style="font-size:8.5px;font-weight:bold;color:#374151;text-transform:uppercase;">{{ $label }}</div>
          <div style="font-size:8px;color:#9ca3af;margin-top:2px;">Lu et approuvé</div>
          <div style="height:40px;border-bottom:1px dotted #d1d5db;margin:8px 4px 0;"></div>
          <div style="font-size:7.5px;color:#9ca3af;margin-top:4px;">Date : ________________</div>
        </div>
      </td>
    @endforeach
  </tr>
</table>
