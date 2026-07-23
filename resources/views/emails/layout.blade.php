<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $subject ?? 'IBIG FactPro' }}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; color: #374151; }
.wrapper { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
.header { background: linear-gradient(135deg, #002D5B 0%, #0062CC 100%); padding: 32px 40px; text-align: center; }
.header-logo { font-size: 28px; font-weight: 800; color: #fff; letter-spacing: -0.5px; }
.header-logo span { color: #F0C040; }
.header-tagline { font-size: 13px; color: rgba(255,255,255,.7); margin-top: 4px; }
.content { padding: 40px; }
.greeting { font-size: 18px; font-weight: 700; color: #002D5B; margin-bottom: 16px; }
.text { font-size: 15px; line-height: 1.7; color: #4b5563; margin-bottom: 16px; }
.btn { display: inline-block; padding: 14px 32px; background: #F0C040; color: #002D5B; font-weight: 800; font-size: 15px; border-radius: 8px; text-decoration: none; margin: 8px 0; }
.btn-secondary { background: #002D5B; color: #fff; }
.highlight-box { background: #f0f7ff; border-left: 4px solid #0062CC; border-radius: 0 8px 8px 0; padding: 16px 20px; margin: 20px 0; }
.highlight-box.gold { background: #fffbeb; border-left-color: #F0C040; }
.highlight-box.green { background: #f0fdf4; border-left-color: #059669; }
.highlight-box.red { background: #fff1f2; border-left-color: #e11d48; }
.stats-row { display: flex; gap: 16px; margin: 20px 0; }
.stat-box { flex: 1; background: #f9fafb; border-radius: 8px; padding: 16px; text-align: center; }
.stat-box .value { font-size: 24px; font-weight: 800; color: #002D5B; }
.stat-box .label { font-size: 12px; color: #6b7280; margin-top: 4px; }
.divider { border: none; border-top: 1px solid #e5e7eb; margin: 24px 0; }
.footer { background: #f9fafb; padding: 24px 40px; text-align: center; border-top: 1px solid #e5e7eb; }
.footer-text { font-size: 12px; color: #9ca3af; line-height: 1.6; }
.footer-links a { color: #6b7280; text-decoration: none; margin: 0 8px; }
.badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.badge-green { background: #d1fae5; color: #065f46; }
.badge-gold { background: #fef3c7; color: #92400e; }
.badge-blue { background: #dbeafe; color: #1e40af; }
.badge-red { background: #fee2e2; color: #991b1b; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="header-logo">IBIG <span>FactPro</span></div>
    <div class="header-tagline">Logiciel de facturation pour les entreprises africaines</div>
  </div>
  <div class="content">
    @yield('content')
  </div>
  <div class="footer">
    <div class="footer-links">
      <a href="https://factpro.ibigsoft.com">FactPro</a>
      <a href="https://factpro.ibigsoft.com/help">Aide</a>
      <a href="https://factpro.ibigsoft.com/contact">Contact</a>
      <a href="https://factpro.ibigsoft.com/legal/confidentialite">Confidentialité</a>
    </div>
    <div class="footer-text" style="margin-top:12px">
      © {{ date('Y') }} IBIG Soft SARL · Abidjan, Côte d'Ivoire<br>
      Vous recevez cet email car vous êtes inscrit sur IBIG FactPro.<br>
      @yield('footer_extra')
    </div>
  </div>
</div>
</body>
</html>
