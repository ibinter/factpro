<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    font-family: 'Courier New', Courier, monospace;
    font-size: 10px;
    color: #000;
    background: #fff;
    padding: 6px;
    width: 100%;
  }
  .center { text-align: center; }
  .right  { text-align: right; }
  .bold   { font-weight: bold; }
  .title  { font-size: 13px; font-weight: bold; text-align: center; margin-bottom: 2px; }
  .subtitle { font-size: 11px; text-align: center; margin-bottom: 8px; }
  .separator { border-top: 1px dashed #000; margin: 5px 0; }
  .separator-solid { border-top: 2px solid #000; margin: 5px 0; }
  table { width: 100%; border-collapse: collapse; }
  th, td { padding: 2px 3px; font-size: 9px; }
  th { border-bottom: 1px solid #000; text-align: left; font-weight: bold; }
  .td-right { text-align: right; }
  .section-title { font-weight: bold; font-size: 10px; margin: 5px 0 3px; }
  .row-highlight { font-weight: bold; }
  .ecart-positif { color: #007700; font-weight: bold; }
  .ecart-negatif { color: #cc0000; font-weight: bold; }
  .sign-block { margin-top: 20px; display: flex; justify-content: space-between; }
  .sign-line { border-top: 1px solid #000; width: 100px; padding-top: 2px; font-size: 8px; }
  .footer { font-size: 8px; text-align: center; margin-top: 10px; color: #555; }
  .kv { display: flex; justify-content: space-between; }
  .kv-label { font-weight: bold; }
  .kv-value { text-align: right; }
</style>
</head>
<body>

{{-- En-tête société --}}
<div class="center bold" style="font-size:12px;">
  {{ $session->company->name ?? 'FACTPRO' }}
</div>
@if(!empty($session->company->address))
<div class="center" style="font-size:9px;">{{ $session->company->address }}</div>
@endif
@if(!empty($session->company->rccm) || !empty($session->company->tax_id))
<div class="center" style="font-size:8px;">
  @if(!empty($session->company->rccm))RCCM : {{ $session->company->rccm }}@endif
  @if(!empty($session->company->rccm) && !empty($session->company->tax_id)) — @endif
  @if(!empty($session->company->tax_id))NINEA : {{ $session->company->tax_id }}@endif
</div>
@endif

<div class="separator-solid"></div>

<div class="title">RAPPORT Z — CLÔTURE JOURNALIÈRE</div>
<div class="subtitle">N° {{ $z_number ?? $session->z_report_number }}</div>

<div class="separator"></div>

{{-- Infos session --}}
<div class="section-title">SESSION DE CAISSE</div>
<table>
  <tr>
    <td class="kv-label">Ouverture</td>
    <td class="td-right">{{ optional($session->opened_at)->format('d/m/Y H:i') }}</td>
  </tr>
  <tr>
    <td class="kv-label">Clôture</td>
    <td class="td-right">{{ optional($z_report_generated_at)->format('d/m/Y H:i') }}</td>
  </tr>
  <tr>
    <td class="kv-label">Caissier</td>
    <td class="td-right">{{ $cashier ?? '—' }}</td>
  </tr>
  <tr>
    <td class="kv-label">Fonds d'ouverture</td>
    <td class="td-right">{{ number_format($opening_float, 0, ',', ' ') }} {{ $currency }}</td>
  </tr>
</table>

<div class="separator"></div>

{{-- Ventilation par mode de paiement --}}
<div class="section-title">VENTES PAR MODE DE PAIEMENT</div>
<table>
  <thead>
    <tr>
      <th>Mode</th>
      <th class="td-right">Montant</th>
    </tr>
  </thead>
  <tbody>
    @forelse($sales_by_payment_method as $item)
    <tr>
      <td>{{ $item['label'] }}</td>
      <td class="td-right">{{ number_format($item['amount'], 0, ',', ' ') }}</td>
    </tr>
    @empty
    <tr>
      <td colspan="2" class="center">— Aucune vente —</td>
    </tr>
    @endforelse
    <tr class="row-highlight" style="border-top:1px solid #000;">
      <td><strong>TOTAL</strong></td>
      <td class="td-right"><strong>{{ number_format($total_sales, 0, ',', ' ') }} {{ $currency }}</strong></td>
    </tr>
  </tbody>
</table>

<div class="separator"></div>

{{-- Statistiques --}}
<div class="section-title">RÉSUMÉ</div>
<table>
  <tr>
    <td>Nombre de tickets</td>
    <td class="td-right">{{ $total_tickets }}</td>
  </tr>
  <tr>
    <td>Panier moyen</td>
    <td class="td-right">{{ number_format($average_basket, 0, ',', ' ') }} {{ $currency }}</td>
  </tr>
  @if($vat_collected > 0)
  <tr>
    <td>TVA collectée</td>
    <td class="td-right">{{ number_format($vat_collected, 0, ',', ' ') }} {{ $currency }}</td>
  </tr>
  @endif
  @if($discounts > 0)
  <tr>
    <td>Remises accordées</td>
    <td class="td-right">{{ number_format($discounts, 0, ',', ' ') }} {{ $currency }}</td>
  </tr>
  @endif
  @if($refunds > 0)
  <tr>
    <td>Annulations / Remboursements</td>
    <td class="td-right">{{ number_format($refunds, 0, ',', ' ') }} {{ $currency }}</td>
  </tr>
  @endif
</table>

<div class="separator"></div>

{{-- Caisse --}}
<div class="section-title">CONTRÔLE DE CAISSE</div>
<table>
  <tr>
    <td>Fonds d'ouverture</td>
    <td class="td-right">{{ number_format($opening_float, 0, ',', ' ') }} {{ $currency }}</td>
  </tr>
  <tr>
    <td>Espèces vendues</td>
    <td class="td-right">
      @php
        $cashSales = collect($sales_by_payment_method)->where('method','cash')->sum('amount');
      @endphp
      {{ number_format($cashSales, 0, ',', ' ') }} {{ $currency }}
    </td>
  </tr>
  <tr class="row-highlight">
    <td>Caisse théorique</td>
    <td class="td-right">{{ number_format($expected_cash, 0, ',', ' ') }} {{ $currency }}</td>
  </tr>
  <tr class="row-highlight">
    <td>Caisse comptée</td>
    <td class="td-right">{{ number_format($actual_cash, 0, ',', ' ') }} {{ $currency }}</td>
  </tr>
  @php $diff = $cash_difference; @endphp
  <tr>
    <td class="bold">Écart</td>
    <td class="td-right {{ $diff > 0 ? 'ecart-positif' : ($diff < 0 ? 'ecart-negatif' : '') }}">
      {{ ($diff >= 0 ? '+' : '') }}{{ number_format($diff, 0, ',', ' ') }} {{ $currency }}
    </td>
  </tr>
</table>

@if(!empty($session->notes))
<div class="separator"></div>
<div class="section-title">NOTES</div>
<div style="font-size:9px;">{{ $session->notes }}</div>
@endif

<div class="separator"></div>

{{-- Signatures --}}
<table style="margin-top:15px;">
  <tr>
    <td style="width:50%;padding-right:10px;">
      <div style="border-top:1px solid #000;padding-top:3px;font-size:9px;">
        Signature Caissier
      </div>
    </td>
    <td style="width:50%;padding-left:10px;">
      <div style="border-top:1px solid #000;padding-top:3px;font-size:9px;">
        Signature Responsable
      </div>
    </td>
  </tr>
  <tr>
    <td style="height:30px;"></td>
    <td></td>
  </tr>
</table>

<div class="separator-solid"></div>
<div class="footer">
  Rapport Z irréversible — archivé le {{ optional($z_report_generated_at)->format('d/m/Y à H:i') }}<br>
  {{ $z_number ?? '' }} — FACTPRO
</div>

</body>
</html>
