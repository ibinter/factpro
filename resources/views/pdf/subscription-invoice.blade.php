<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background: #fff;
        }
        .page { padding: 30px 40px; }

        /* En-tête facture */
        .invoice-header { display: table; width: 100%; margin-bottom: 30px; }
        .invoice-left { display: table-cell; vertical-align: top; width: 50%; }
        .invoice-right { display: table-cell; vertical-align: top; width: 50%; text-align: right; }

        .brand { font-size: 22px; font-weight: 700; color: #002D5B; }
        .brand-sub { font-size: 11px; color: #6b7280; margin-top: 3px; }

        .invoice-title {
            font-size: 28px;
            font-weight: 700;
            color: #002D5B;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .invoice-meta { font-size: 12px; color: #374151; margin-top: 8px; line-height: 1.8; }
        .invoice-meta strong { color: #002D5B; }

        /* Séparateur accent */
        .accent-bar {
            height: 4px;
            background: linear-gradient(to right, #002D5B, #0062CC);
            margin: 20px 0;
            border-radius: 2px;
        }

        /* Infos émetteur / destinataire */
        .parties { display: table; width: 100%; margin-bottom: 24px; }
        .party { display: table-cell; width: 50%; vertical-align: top; }
        .party:last-child { padding-left: 20px; }
        .party-label {
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }
        .party-name { font-size: 14px; font-weight: 700; color: #002D5B; margin-bottom: 3px; }
        .party-detail { font-size: 11px; color: #374151; line-height: 1.7; }

        /* Table lignes de facturation */
        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .invoice-table thead tr { background-color: #002D5B; color: #fff; }
        .invoice-table th {
            padding: 10px 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }
        .invoice-table th.right { text-align: right; }
        .invoice-table td {
            padding: 10px 12px;
            font-size: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        .invoice-table tr:nth-child(even) td { background-color: #f9fafb; }
        .invoice-table td.right { text-align: right; font-weight: 600; }

        /* Totaux */
        .totals { width: 280px; margin-left: auto; margin-bottom: 24px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 7px 10px; font-size: 12px; }
        .totals td:last-child { text-align: right; font-weight: 600; }
        .totals .total-ht td { border-bottom: 1px solid #e5e7eb; }
        .totals .total-ttc {
            background-color: #002D5B;
            color: #fff;
            border-radius: 4px;
        }
        .totals .total-ttc td { font-size: 14px; font-weight: 700; }
        .totals .total-ttc td:last-child { color: #F0C040; font-size: 16px; }

        /* Conditions --*/
        .conditions {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px;
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 24px;
            line-height: 1.6;
        }
        .conditions strong { color: #002D5B; display: block; margin-bottom: 4px; }

        /* Pied de page */
        .footer {
            border-top: 2px solid #0062CC;
            padding-top: 14px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            line-height: 1.6;
        }
        .footer strong { color: #002D5B; }
    </style>
</head>
<body>
<div class="page">

    {{-- En-tête --}}
    <div class="invoice-header">
        <div class="invoice-left">
            <div class="brand">IBIG FactPro</div>
            <div class="brand-sub">IBIG Soft — factpro.ibigsoft.com<br>support@ibigsoft.com</div>
        </div>
        <div class="invoice-right">
            <div class="invoice-title">Facture</div>
            <div class="invoice-meta">
                <strong>N°</strong> FAC-{{ now()->year }}-{{ str_pad($order->id ?? 1, 6, '0', STR_PAD_LEFT) }}<br>
                <strong>Date :</strong> {{ ($transaction->confirmed_at ?? now())->format('d/m/Y') }}<br>
                <strong>Commande :</strong> {{ $order->order_number }}
            </div>
        </div>
    </div>

    <div class="accent-bar"></div>

    {{-- Émetteur / Destinataire --}}
    <div class="parties">
        <div class="party">
            <div class="party-label">Émetteur</div>
            <div class="party-name">IBIG Soft</div>
            <div class="party-detail">
                Éditeur logiciel SaaS<br>
                factpro.ibigsoft.com<br>
                support@ibigsoft.com
            </div>
        </div>
        <div class="party">
            <div class="party-label">Destinataire</div>
            <div class="party-name">{{ $order->user?->name ?? '—' }}</div>
            <div class="party-detail">
                {{ $order->user?->email ?? '—' }}<br>
                {{ $order->country ?? '—' }}
            </div>
        </div>
    </div>

    {{-- Lignes de facturation --}}
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Description</th>
                <th>Période</th>
                <th class="right">Durée</th>
                <th class="right">Prix HT</th>
                @if($order->tax_amount > 0)
                <th class="right">TVA</th>
                @endif
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Abonnement {{ $order->plan?->name ?? '—' }}</strong><br>
                    <span style="font-size:10px; color:#6b7280;">Logiciel de facturation en ligne</span>
                </td>
                <td>
                    <span style="font-size:11px;">
                        {{ $license->starts_at?->format('d/m/Y') ?? '—' }}<br>→ {{ $license->ends_at?->format('d/m/Y') ?? '—' }}
                    </span>
                </td>
                <td class="right">{{ $order->duration_months ?? '—' }} mois</td>
                <td class="right">{{ number_format((float)$order->amount, 0, ',', ' ') }} {{ $order->currency }}</td>
                @if($order->tax_amount > 0)
                <td class="right">{{ number_format((float)$order->tax_amount, 0, ',', ' ') }} {{ $order->currency }}</td>
                @endif
                <td class="right">{{ number_format((float)$order->total_amount, 0, ',', ' ') }} {{ $order->currency }}</td>
            </tr>
            @if($order->discount_amount > 0)
            <tr>
                <td colspan="{{ $order->tax_amount > 0 ? 3 : 2 }}"><em style="color:#6b7280;">Remise appliquée</em></td>
                <td></td>
                <td class="right" style="color:#dc2626;">-{{ number_format((float)$order->discount_amount, 0, ',', ' ') }} {{ $order->currency }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Totaux --}}
    <div class="totals">
        <table>
            <tr class="total-ht">
                <td style="color:#6b7280;">Total HT</td>
                <td>{{ number_format((float)$order->amount, 0, ',', ' ') }} {{ $order->currency }}</td>
            </tr>
            @if($order->discount_amount > 0)
            <tr class="total-ht">
                <td style="color:#6b7280;">Remise</td>
                <td style="color:#dc2626;">-{{ number_format((float)$order->discount_amount, 0, ',', ' ') }} {{ $order->currency }}</td>
            </tr>
            @endif
            @if($order->tax_amount > 0)
            <tr class="total-ht">
                <td style="color:#6b7280;">TVA</td>
                <td>{{ number_format((float)$order->tax_amount, 0, ',', ' ') }} {{ $order->currency }}</td>
            </tr>
            @endif
            <tr class="total-ttc">
                <td>Total TTC</td>
                <td>{{ number_format((float)$order->total_amount, 0, ',', ' ') }} {{ $order->currency }}</td>
            </tr>
        </table>
    </div>

    {{-- Conditions --}}
    <div class="conditions">
        <strong>Conditions de paiement</strong>
        Paiement reçu le {{ ($transaction->confirmed_at ?? now())->format('d/m/Y') }}
        via {{ ucfirst(str_replace('_', ' ', $transaction->payment_provider ?? '—')) }}.
        Cette facture est soldée. Aucun règlement supplémentaire n'est requis.
    </div>

    {{-- Pied de page --}}
    <div class="footer">
        <strong>IBIG Soft</strong> — Cette facture est émise au titre de l'abonnement au logiciel IBIG FactPro.<br>
        Pour toute question : support@ibigsoft.com — factpro.ibigsoft.com<br>
        Document généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</div>
</body>
</html>
