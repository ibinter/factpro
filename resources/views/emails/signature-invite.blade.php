<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .header { background: #1e3a5f; color: white; padding: 20px 24px; border-radius: 8px 8px 0 0; }
        .body { background: #f9fafb; padding: 24px; border: 1px solid #e5e7eb; }
        .btn { display: inline-block; background: #2563eb; color: white; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: bold; margin: 16px 0; }
        .footer { color: #9ca3af; font-size: 12px; padding: 16px 0; }
        .info-box { background: #eff6ff; border: 1px solid #bfdbfe; padding: 12px 16px; border-radius: 6px; margin-top: 16px; font-size: 14px; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2 style="margin:0">IBIG FactPro — Demande de signature</h2>
    </div>
    <div class="body">
        <p>Bonjour <strong>{{ $signature->signer_name }}</strong>,</p>

        <p>Vous êtes invité(e) à signer le document suivant :</p>

        <div class="info-box">
            <strong>Document :</strong> {{ $documentName }}<br>
            <strong>Émetteur :</strong> {{ $emitterName }}<br>
            @if($signature->signer_role)
            <strong>Votre rôle :</strong> {{ $signature->signer_role }}<br>
            @endif
            <strong>Niveau de signature :</strong> {{ ucfirst($signature->signature_level) }}
        </div>

        <p style="margin-top:20px">Cliquez sur le bouton ci-dessous pour consulter et signer le document :</p>

        <a href="{{ url('/sign/' . $signature->token) }}" class="btn">
            Signer le document
        </a>

        <p style="color:#6b7280; font-size:13px">
            Ce lien expire le <strong>{{ $signature->expires_at->format('d/m/Y à H:i') }}</strong>.<br>
            Si vous n'êtes pas concerné(e) par cette demande, vous pouvez ignorer cet email.
        </p>
    </div>
    <div class="footer">
        © {{ date('Y') }} IBIG SARL — factpro.ibigsoft.com<br>
        Cet email a été envoyé automatiquement, merci de ne pas y répondre.
    </div>
</div>
</body>
</html>
