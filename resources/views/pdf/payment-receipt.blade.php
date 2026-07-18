<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Reçu {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #1f2937;
            background: #fff;
        }
        .page { padding: 30px 40px; }

        /* En-tête */
        .header {
            background-color: #002D5B;
            color: #fff;
            padding: 20px 24px;
            border-radius: 6px 6px 0 0;
            margin-bottom: 0;
        }
        .header-inner { display: table; width: 100%; }
        .header-left { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .brand { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
        .receipt-label {
            font-size: 13px;
            font-weight: 700;
            color: #F0C040;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Bannière statut */
        .status-bar {
            background-color: #0062CC;
            color: #fff;
            text-align: center;
            padding: 10px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-bottom: 3px solid #F0C040;
            margin-bottom: 24px;
        }

        /* Titre */
        .title-block { text-align: center; margin-bottom: 24px; }
        .title-block h1 { font-size: 22px; color: #002D5B; font-weight: 700; }
        .title-block .receipt-number { font-size: 13px; color: #6b7280; margin-top: 4px; }

        /* Meta info */
        .meta-row { display: table; width: 100%; margin-bottom: 24px; }
        .meta-col { display: table-cell; width: 50%; vertical-align: top; }
        .meta-col:last-child { text-align: right; }
        .meta-label { font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .meta-value { font-size: 12px; color: #1f2937; font-weight: 600; }

        /* Séparateur */
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }

        /* Table détails */
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .details-table th {
            background-color: #002D5B;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 9px 12px;
            text-align: left;
        }
        .details-table td {
            padding: 9px 12px;
            font-size: 12px;
            border-bottom: 1px solid #f3f4f6;
        }
        .details-table tr:nth-child(even) td { background-color: #f9fafb; }
        .details-table td.label { color: #6b7280; }
        .details-table td.value { font-weight: 600; color: #1f2937; text-align: right; }

        /* Total */
        .total-block {
            background-color: #002D5B;
            color: #fff;
            padding: 14px 16px;
            border-radius: 6px;
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }
        .total-label { display: table-cell; font-size: 15px; font-weight: 700; }
        .total-amount { display: table-cell; text-align: right; font-size: 20px; font-weight: 700; color: #F0C040; }

        /* Statut badge */
        .paid-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
            font-weight: 700;
            font-size: 13px;
            padding: 4px 14px;
            border-radius: 20px;
            border: 1px solid #bbf7d0;
        }

        /* QR code section */
        .qr-section {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 14px 16px;
            display: table;
            width: 100%;
            margin-bottom: 24px;
        }
        .qr-text { display: table-cell; vertical-align: middle; }
        .qr-url { font-size: 10px; color: #6b7280; word-break: break-all; margin-top: 4px; }

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
    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <div class="brand">IBIG FactPro</div>
                <div style="font-size:11px; color:#93b3d4; margin-top:2px;">IBIG Soft — factpro.ibigsoft.com</div>
            </div>
            <div class="header-right">
                <div class="receipt-label">Reçu de paiement</div>
            </div>
        </div>
    </div>

    <div class="status-bar">✅ PAYÉ — Abonnement confirmé</div>

    {{-- Titre --}}
    <div class="title-block">
        <h1>REÇU DE PAIEMENT</h1>
        <div class="receipt-number">N° REC-{{ $order->order_number }}</div>
    </div>

    {{-- Meta : date + référence --}}
    <div class="meta-row">
        <div class="meta-col">
            <div class="meta-label">Date d'émission</div>
            <div class="meta-value">{{ $transaction->confirmed_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</div>
        </div>
        <div class="meta-col">
            <div class="meta-label">Référence transaction</div>
            <div class="meta-value">{{ $transaction->internal_reference }}</div>
        </div>
    </div>

    <hr class="divider">

    {{-- Informations client --}}
    <div class="meta-row" style="margin-bottom:8px;">
        <div class="meta-col">
            <div class="meta-label" style="margin-bottom:8px;">Client</div>
            <div class="meta-value">{{ $order->user?->name ?? '—' }}</div>
            <div style="font-size:11px; color:#6b7280; margin-top:2px;">{{ $order->user?->email ?? '—' }}</div>
            <div style="font-size:11px; color:#6b7280; margin-top:2px;">{{ $order->country ?? '—' }}</div>
        </div>
        <div class="meta-col" style="text-align:right;">
            <div class="meta-label" style="margin-bottom:8px;">Moyen de paiement</div>
            <div class="meta-value">{{ ucfirst(str_replace('_', ' ', $transaction->payment_provider ?? '—')) }}</div>
            @if($transaction->sender_name)
            <div style="font-size:11px; color:#6b7280; margin-top:2px;">{{ $transaction->sender_name }}</div>
            @endif
            @if($transaction->sender_number)
            <div style="font-size:11px; color:#6b7280; margin-top:2px;">{{ $transaction->sender_number }}</div>
            @endif
        </div>
    </div>

    <hr class="divider">

    {{-- Détails abonnement --}}
    <table class="details-table">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right;">Période</th>
                <th style="text-align:right;">Montant</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Abonnement {{ $order->plan?->name ?? '—' }}</td>
                <td style="text-align:right; font-size:11px; color:#6b7280;">
                    {{ $license->starts_at?->format('d/m/Y') ?? '—' }} → {{ $license->ends_at?->format('d/m/Y') ?? '—' }}
                </td>
                <td class="value">{{ number_format((float)($transaction->amount_received ?? $order->total_amount), 0, ',', ' ') }} {{ $order->currency }}</td>
            </tr>
            @if($order->discount_amount > 0)
            <tr>
                <td class="label">Remise</td>
                <td></td>
                <td class="value" style="color:#dc2626;">-{{ number_format((float)$order->discount_amount, 0, ',', ' ') }} {{ $order->currency }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    {{-- Total --}}
    <div class="total-block">
        <div class="total-label">Total payé</div>
        <div class="total-amount">{{ number_format((float)($transaction->amount_received ?? $order->total_amount), 0, ',', ' ') }} {{ $order->currency }}</div>
    </div>

    {{-- Statut --}}
    <div style="text-align:center; margin-bottom:20px;">
        <span class="paid-badge">✅ PAYÉ</span>
    </div>

    {{-- Vérification --}}
    <div class="qr-section">
        <div class="qr-text">
            <div style="font-size:11px; font-weight:700; color:#002D5B; margin-bottom:4px;">Vérification en ligne</div>
            <div style="font-size:11px; color:#374151;">Vérifiez l'authenticité de ce reçu à l'adresse suivante :</div>
            <div class="qr-url">{{ url('/public/verify/' . $order->id) }}</div>
        </div>
    </div>

    {{-- Pied de page --}}
    <div class="footer">
        <strong>IBIG Soft</strong> — Ce reçu constitue une preuve d'abonnement valide.<br>
        Pour toute question : support@ibigsoft.com — factpro.ibigsoft.com<br>
        Document généré le {{ now()->format('d/m/Y à H:i') }}
    </div>
</div>
</body>
</html>
