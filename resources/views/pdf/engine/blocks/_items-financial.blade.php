<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <thead>
    <tr style="background:{{ $primaryColor }};color:#ffffff;">
      <th style="padding:7px 10px;text-align:left;font-size:8.5px;text-transform:uppercase;width:40%;">Description</th>
      <th style="padding:7px 10px;text-align:center;font-size:8.5px;text-transform:uppercase;width:10%;">Qté</th>
      <th style="padding:7px 10px;text-align:right;font-size:8.5px;text-transform:uppercase;width:12%;">Unité</th>
      <th style="padding:7px 10px;text-align:right;font-size:8.5px;text-transform:uppercase;width:15%;">PU HT</th>
      <th style="padding:7px 10px;text-align:center;font-size:8.5px;text-transform:uppercase;width:8%;">Remise</th>
      <th style="padding:7px 10px;text-align:center;font-size:8.5px;text-transform:uppercase;width:8%;">TVA</th>
      <th style="padding:7px 10px;text-align:right;font-size:8.5px;text-transform:uppercase;width:15%;">Total HT</th>
    </tr>
  </thead>
  <tbody>
    @forelse($document->lines ?? [] as $i => $line)
      <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f9fafb' }};border-bottom:1px solid #e5e7eb;">
        <td style="padding:7px 10px;font-size:9px;vertical-align:top;">
          <div style="font-weight:bold;color:#111827;">{{ $line->description ?? $line->label ?? '' }}</div>
          @if(!empty($line->detail))
            <div style="font-size:8px;color:#6b7280;margin-top:2px;">{{ $line->detail }}</div>
          @endif
        </td>
        <td style="padding:7px 10px;text-align:center;font-size:9px;">{{ $line->quantity ?? 1 }}</td>
        <td style="padding:7px 10px;text-align:right;font-size:9px;color:#6b7280;">{{ $line->unit ?? '' }}</td>
        <td style="padding:7px 10px;text-align:right;font-size:9px;font-family:monospace;">
          {{ number_format((float)($line->unit_price ?? 0), 0, ',', ' ') }}
        </td>
        <td style="padding:7px 10px;text-align:center;font-size:9px;">
          @if(!empty($line->discount) && (float)$line->discount > 0)
            {{ $line->discount }}%
          @else
            —
          @endif
        </td>
        <td style="padding:7px 10px;text-align:center;font-size:9px;">
          @if(!empty($line->tax_rate))
            {{ $line->tax_rate }}%
          @else
            —
          @endif
        </td>
        <td style="padding:7px 10px;text-align:right;font-size:9px;font-family:monospace;font-weight:bold;">
          {{ number_format((float)($line->total ?? $line->total_ht ?? ($line->unit_price * $line->quantity) ?? 0), 0, ',', ' ') }}
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="7" style="padding:14px;text-align:center;font-size:9px;color:#9ca3af;font-style:italic;">
          Aucune ligne de produit ou service
        </td>
      </tr>
    @endforelse
  </tbody>
</table>
