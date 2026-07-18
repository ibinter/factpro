<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        width: 148mm;
        height: 105mm;
        font-family: 'DejaVu Sans', sans-serif;
        background: {{ $level['color'] }};
        color: #fff;
        overflow: hidden;
    }
    .card {
        width: 148mm;
        height: 105mm;
        padding: 8mm;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background: linear-gradient(135deg, {{ $level['color'] }} 0%, #000000aa 100%);
        position: relative;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    .company-name {
        font-size: 14pt;
        font-weight: bold;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    .program-label {
        font-size: 8pt;
        opacity: 0.85;
        margin-top: 2px;
    }
    .level-badge {
        text-align: right;
    }
    .level-icon {
        font-size: 24pt;
    }
    .level-name {
        font-size: 10pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    .customer-section {
        text-align: center;
    }
    .customer-name {
        font-size: 18pt;
        font-weight: bold;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.6);
    }
    .points-section {
        text-align: center;
        background: rgba(0,0,0,0.25);
        border-radius: 4mm;
        padding: 4mm 6mm;
    }
    .points-label {
        font-size: 8pt;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.85;
    }
    .points-value {
        font-size: 28pt;
        font-weight: bold;
        line-height: 1;
    }
    .footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
    }
    .validity {
        font-size: 7pt;
        opacity: 0.75;
    }
    .qr-placeholder {
        width: 18mm;
        height: 18mm;
        background: #fff;
        border-radius: 2mm;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #333;
        font-size: 5pt;
        text-align: center;
        padding: 2px;
    }
</style>
</head>
<body>
<div class="card">
    <div class="header">
        <div>
            <div class="company-name">{{ $company->name }}</div>
            <div class="program-label">{{ $program?->name ?? 'Programme Fidélité' }}</div>
        </div>
        <div class="level-badge">
            <div class="level-icon">{{ $level['icon'] }}</div>
            <div class="level-name">Niveau {{ $level['name'] }}</div>
        </div>
    </div>

    <div class="customer-section">
        <div class="customer-name">{{ $customer->name }}</div>
    </div>

    <div class="points-section">
        <div class="points-label">Solde de points</div>
        <div class="points-value">{{ number_format($balance) }}</div>
        <div class="points-label">points</div>
    </div>

    <div class="footer">
        <div class="validity">Valable chez {{ $company->name }}</div>
        <div class="qr-placeholder">ID:{{ $customer->id }}</div>
    </div>
</div>
</body>
</html>
