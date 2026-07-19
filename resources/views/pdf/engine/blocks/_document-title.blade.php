<div style="background:{{ $primaryColor }};color:#ffffff;padding:12px 16px;border-radius:4px;">
  <div style="font-size:18px;font-weight:bold;text-transform:uppercase;letter-spacing:1px;">
    {{ $document->type_label ?? strtoupper(str_replace('_',' ',$document->type)) }}
  </div>
  <div style="font-size:13px;font-weight:bold;margin-top:4px;">
    N° {{ $document->number }}
  </div>
  <div style="font-size:8.5px;margin-top:8px;line-height:1.8;">
    @if(!empty($document->issue_date))
      <span style="opacity:0.8;">Date d'émission :</span> {{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}<br>
    @endif
    @if(!empty($document->due_date))
      <span style="opacity:0.8;">Date d'échéance :</span> {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}<br>
    @endif
    @if(!empty($document->reference))
      <span style="opacity:0.8;">Référence :</span> {{ $document->reference }}<br>
    @endif
    @if(!empty($document->status))
      <span style="opacity:0.8;">Statut :</span> {{ $document->status_label ?? $document->status }}
    @endif
  </div>
</div>
