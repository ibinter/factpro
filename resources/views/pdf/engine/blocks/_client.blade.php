<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <tr>
    <td style="width:50%;vertical-align:top;padding-right:10px;"></td>
    <td style="width:50%;vertical-align:top;">
      <div style="border:1px solid #e5e7eb;border-left:3px solid {{ $primaryColor }};border-radius:3px;padding:10px 12px;background:#f9fafb;">
        <div style="font-size:8px;text-transform:uppercase;color:#6b7280;letter-spacing:1px;margin-bottom:5px;">
          {{ $clientLabel ?? 'Destinataire / Client' }}
        </div>
        @if($document->customer)
          <div style="font-size:12px;font-weight:bold;color:#111827;">
            {{ $document->customer->name ?? $document->customer->company_name ?? '—' }}
          </div>
          @if(!empty($document->customer->company_name) && !empty($document->customer->name))
            <div style="font-size:9px;color:#374151;">{{ $document->customer->company_name }}</div>
          @endif
          <div style="font-size:8.5px;color:#374151;margin-top:5px;line-height:1.7;">
            @if(!empty($document->customer->address)){{ $document->customer->address }}<br>@endif
            @if(!empty($document->customer->city)){{ $document->customer->city }}@if(!empty($document->customer->postal_code)) – {{ $document->customer->postal_code }}@endif<br>@endif
            @if(!empty($document->customer->country)){{ $document->customer->country }}<br>@endif
            @if(!empty($document->customer->phone))<span style="color:#6b7280;">Tél:</span> {{ $document->customer->phone }}<br>@endif
            @if(!empty($document->customer->email))<span style="color:#6b7280;">Email:</span> {{ $document->customer->email }}<br>@endif
            @if(!empty($document->customer->tax_number))<span style="color:#6b7280;">N° fiscal:</span> {{ $document->customer->tax_number }}<br>@endif
          </div>
        @else
          <div style="font-size:10px;color:#9ca3af;font-style:italic;">— Non renseigné —</div>
        @endif
      </div>
    </td>
  </tr>
</table>
