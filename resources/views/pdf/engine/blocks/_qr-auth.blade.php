@if(!empty($qrDataUri))
<div style="text-align:center;margin:10px 0;">
  <img src="{{ $qrDataUri }}" style="width:25mm;height:25mm;">
  <div style="font-size:7px;color:#9ca3af;margin-top:3px;">
    Scannez pour vérifier l'authenticité
    @if(!empty($document->verification_url))
      <br>{{ $document->verification_url }}
    @endif
  </div>
</div>
@endif
