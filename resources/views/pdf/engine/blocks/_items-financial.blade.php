<table style="width:100%;border-collapse:collapse;margin-bottom:14px;border-radius:4px;overflow:hidden;">
  <thead>
    <tr style="background:{{ $primaryColor }};color:#ffffff;">
      <th style="padding:8px 10px;text-align:left;font-size:7.5px;text-transform:uppercase;letter-spacing:0.6px;width:40%;">Description</th>
      <th style="padding:8px 10px;text-align:center;font-size:7.5px;text-transform:uppercase;letter-spacing:0.6px;width:8%;">Qté</th>
      <th style="padding:8px 10px;text-align:left;font-size:7.5px;text-transform:uppercase;letter-spacing:0.6px;width:10%;">Unité</th>
      <th style="padding:8px 10px;text-align:right;font-size:7.5px;text-transform:uppercase;letter-spacing:0.6px;width:14%;">PU HT</th>
      <th style="padding:8px 10px;text-align:center;font-size:7.5px;text-transform:uppercase;letter-spacing:0.6px;width:8%;">Remise</th>
      <th style="padding:8px 10px;text-align:center;font-size:7.5px;text-transform:uppercase;letter-spacing:0.6px;width:7%;">TVA</th>
      <th style="padding:8px 10px;text-align:right;font-size:7.5px;text-transform:uppercase;letter-spacing:0.6px;width:13%;">Total HT</th>
    </tr>
  </thead>
  <tbody>
    @forelse($document->lines ?? [] as $i => $line)
      <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f8f9fb' }};border-bottom:1px solid #eef0f3;">
        <td style="padding:8px 10px;font-size:9px;vertical-align:top;">
          <div style="font-weight:bold;color:#111827;line-height:1.3;">{{ $line->description ?? $line->label ?? '' }}</div>
          @if(!empty($line->detail))
            <div style="font-size:7.5px;color:#6b7280;margin-top:2px;line-height:1.4;">{{ $line->detail }}</div>
          @endif
        </td>
        <td style="padding:8px 10px;text-align:center;font-size:9px;color:#374151;">{{ $line->quantity ?? 1 }}</td>
        <td style="padding:8px 10px;text-align:left;font-size:8.5px;color:#6b7280;">{{ $line->unit ?? '' }}</td>
        <td style="padding:8px 10px;text-align:right;font-size:9px;font-family:monospace;color:#374151;">
          {{ number_format((float)($line->unit_price ?? 0), 0, ',', ' ') }}
        </td>
        <td style="padding:8px 10px;text-align:center;font-size:8.5px;color:#6b7280;">
          @if(!empty($line->discount) && (float)$line->discount > 0)
            <span style="background:#fef3c7;color:#92400e;padding:1px 5px;border-radius:3px;">{{ $line->discount }}%</span>
          @else
            <span style="color:#d1d5db;">—</span>
          @endif
        </td>
        <td style="padding:8px 10px;text-align:center;font-size:8.5px;color:#374151;">
          @if(!empty($line->tax_rate))
            {{ $line->tax_rate }}%
          @else
            <span style="color:#d1d5db;">—</span>
          @endif
        </td>
        <td style="padding:8px 10px;text-align:right;font-size:9px;font-family:monospace;font-weight:bold;color:#111827;">
          {{ number_format((float)($line->line_total ?? $line->total ?? $line->total_ht ?? ((float)($line->unit_price ?? 0) * (float)($line->quantity ?? 1))), 0, ',', ' ') }}
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="7" style="padding:16px;text-align:center;font-size:9px;color:#9ca3af;font-style:italic;border-bottom:1px solid #e5e7eb;">
          Aucune ligne de produit ou service
        </td>
      </tr>
    @endforelse
    {{-- Ligne de fermeture --}}
    <tr style="background:#f1f3f5;border-top:2px solid #e5e7eb;">
      <td colspan="6" style="padding:6px 10px;text-align:right;font-size:8px;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;">
        Total Hors Taxes
      </td>
      <td style="padding:6px 10px;text-align:right;font-size:9.5px;font-family:monospace;font-weight:bold;color:#111827;">
        @php
          $totalHT = collect($document->lines ?? [])->sum(fn($l) =>
            (float)($l->line_total ?? $l->total ?? $l->total_ht ?? ((float)($l->unit_price ?? 0) * (float)($l->quantity ?? 1)))
          );
        @endphp
        {{ number_format($totalHT, 0, ',', ' ') }} {{ $document->currency ?? '' }}
      </td>
    </tr>
  </tbody>
</table>
