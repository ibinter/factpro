<!DOCTYPE html>
<html>
<body style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:24px;">
<h2>Demande de prix de {{ $token->company->name }}</h2>
<p>Bonjour {{ $token->supplier_name }},</p>
<p>{{ $token->company->name }} vous invite à soumettre votre offre de prix pour une demande de consultation.</p>
<p style="text-align:center;margin:32px 0;">
    <a href="{{ url('/supplier/portal/'.$token->token) }}"
       style="background:#1a56db;color:#fff;padding:14px 28px;border-radius:6px;text-decoration:none;font-weight:bold;">
        Voir la demande et soumettre mon offre
    </a>
</p>
<p style="color:#999;font-size:12px;">Ce lien expire le {{ $token->expires_at->format('d/m/Y') }}.</p>
</body>
</html>
