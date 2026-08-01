<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <tr>
    {{-- Colonne gauche : métadonnées du document (dates, référence, objet) --}}
    <td style="width:48%;vertical-align:top;padding-right:14px;">
      <div style="background:#f8f9fb;border:1px solid #e5e7eb;border-radius:3px;padding:9px 12px;">
        <div style="font-size:7.5px;text-transform:uppercase;color:#9ca3af;letter-spacing:1px;margin-bottom:6px;font-weight:bold;">
          Informations du document
        </div>
        <table style="width:100%;border-collapse:collapse;">
          @if(!empty($document->issue_date))
          <tr>
            <td style="font-size:8px;color:#6b7280;padding-bottom:4px;width:45%;">Date d'émission</td>
            <td style="font-size:8.5px;color:#111827;font-weight:600;padding-bottom:4px;">
              {{ \Carbon\Carbon::parse($document->issue_date)->format('d/m/Y') }}
            </td>
          </tr>
          @endif
          @if(!empty($document->due_date))
          <tr>
            <td style="font-size:8px;color:#6b7280;padding-bottom:4px;">{{ in_array($document->type ?? '', ['quote','estimate']) ? 'Valide jusqu\'au' : 'Date d\'échéance' }}</td>
            <td style="font-size:8.5px;color:#111827;font-weight:600;padding-bottom:4px;">
              {{ \Carbon\Carbon::parse($document->due_date)->format('d/m/Y') }}
            </td>
          </tr>
          @endif
          @if(!empty($document->reference))
          <tr>
            <td style="font-size:8px;color:#6b7280;padding-bottom:4px;">Référence</td>
            <td style="font-size:8.5px;color:#111827;font-weight:600;padding-bottom:4px;">{{ $document->reference }}</td>
          </tr>
          @endif
          @if(!empty($document->subject) || !empty($document->object))
          <tr>
            <td style="font-size:8px;color:#6b7280;padding-bottom:4px;">Objet</td>
            <td style="font-size:8.5px;color:#374151;padding-bottom:4px;">{{ $document->subject ?? $document->object }}</td>
          </tr>
          @endif
          @if(!empty($document->project_name))
          <tr>
            <td style="font-size:8px;color:#6b7280;padding-bottom:4px;">Projet</td>
            <td style="font-size:8.5px;color:#374151;padding-bottom:4px;">{{ $document->project_name }}</td>
          </tr>
          @endif
          @php $curr = $document->currency ?? ''; @endphp
          @if(!empty($curr))
          <tr>
            <td style="font-size:8px;color:#6b7280;">Devise</td>
            <td style="font-size:8.5px;color:#374151;">{{ $curr }}</td>
          </tr>
          @endif
        </table>
      </div>
    </td>

    {{-- Colonne droite : destinataire --}}
    <td style="width:52%;vertical-align:top;">
      <div style="border:1px solid #e5e7eb;border-left:3px solid {{ $primaryColor }};border-radius:3px;padding:9px 12px;background:#f9fafb;">
        <div style="font-size:7.5px;text-transform:uppercase;color:#9ca3af;letter-spacing:1px;margin-bottom:6px;font-weight:bold;">
          {{ $clientLabel ?? 'Destinataire / Client' }}
        </div>
        @if($document->customer)
          <div style="font-size:12px;font-weight:bold;color:#111827;line-height:1.2;">
            {{ $document->customer->name ?? $document->customer->company_name ?? '—' }}
          </div>
          @if(!empty($document->customer->company_name) && !empty($document->customer->name) && $document->customer->company_name !== $document->customer->name)
            <div style="font-size:9px;color:#374151;margin-top:1px;">{{ $document->customer->company_name }}</div>
          @endif
          <div style="font-size:8.5px;color:#374151;margin-top:6px;line-height:1.75;">
            @if(!empty($document->customer->address)){{ $document->customer->address }}<br>@endif
            @if(!empty($document->customer->city)){{ $document->customer->city }}
@if(!empty($document->customer->postal_code)) – {{ $document->customer->postal_code }}
@endif
            @if(!empty($document->customer->country)) · {{ $document->customer->country }}
@endif<br>@endif
            @if(!empty($document->customer->phone))<span style="color:#9ca3af;">Tél :</span> {{ $document->customer->phone }}<br>@endif
            @if(!empty($document->customer->email))<span style="color:#9ca3af;">Email :</span> {{ $document->customer->email }}<br>@endif
            @if(!empty($document->customer->tax_number))<span style="color:#9ca3af;">N° fiscal :</span> {{ $document->customer->tax_number }}<br>@endif
          </div>
        @else
          <div style="font-size:10px;color:#9ca3af;font-style:italic;margin-top:8px;">— Non renseigné —</div>
        @endif
      </div>
    </td>
  </tr>
</table>
