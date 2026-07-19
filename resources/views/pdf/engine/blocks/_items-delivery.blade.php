<table style="width:100%;border-collapse:collapse;margin-bottom:14px;">
  <thead>
    <tr style="background:{{ $primaryColor }};color:#ffffff;">
      <th style="padding:7px 10px;text-align:left;font-size:8.5px;text-transform:uppercase;width:12%;">Réf.</th>
      <th style="padding:7px 10px;text-align:left;font-size:8.5px;text-transform:uppercase;width:38%;">Désignation</th>
      <th style="padding:7px 10px;text-align:center;font-size:8.5px;text-transform:uppercase;width:12%;">Qté commandée</th>
      <th style="padding:7px 10px;text-align:center;font-size:8.5px;text-transform:uppercase;width:12%;">Qté livrée</th>
      <th style="padding:7px 10px;text-align:center;font-size:8.5px;text-transform:uppercase;width:10%;">Unité</th>
      <th style="padding:7px 10px;text-align:left;font-size:8.5px;text-transform:uppercase;width:16%;">Observations</th>
    </tr>
  </thead>
  <tbody>
    @forelse($document->lines ?? [] as $i => $line)
      <tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f9fafb' }};border-bottom:1px solid #e5e7eb;">
        <td style="padding:7px 10px;font-size:9px;color:#6b7280;">{{ $line->reference ?? $line->sku ?? '—' }}</td>
        <td style="padding:7px 10px;font-size:9px;">
          <div style="font-weight:bold;">{{ $line->description ?? $line->label ?? '' }}</div>
          @if(!empty($line->detail))
            <div style="font-size:8px;color:#6b7280;">{{ $line->detail }}</div>
          @endif
        </td>
        <td style="padding:7px 10px;text-align:center;font-size:9px;">{{ $line->quantity_ordered ?? $line->quantity ?? 0 }}</td>
        <td style="padding:7px 10px;text-align:center;font-size:9px;font-weight:bold;">{{ $line->quantity_delivered ?? $line->quantity ?? 0 }}</td>
        <td style="padding:7px 10px;text-align:center;font-size:9px;color:#6b7280;">{{ $line->unit ?? '' }}</td>
        <td style="padding:7px 10px;font-size:9px;color:#6b7280;">{{ $line->note ?? $line->observations ?? '' }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="6" style="padding:14px;text-align:center;font-size:9px;color:#9ca3af;font-style:italic;">
          Aucune ligne de livraison
        </td>
      </tr>
    @endforelse
  </tbody>
</table>
