<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<title>Guide Utilisateur IBIG FactPro</title>
<style>
/* ═══════════════════════════════════════════
   RESET & BASE
═══════════════════════════════════════════ */
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9pt;
    color: #1e293b;
    background: #ffffff;
    line-height: 1.6;
}

/* ═══════════════════════════════════════════
   PAGE SETUP
═══════════════════════════════════════════ */
@page {
    size: A4 portrait;
    margin: 22mm 16mm 22mm 16mm;
}
@page :first {
    margin: 0;
}

/* ═══════════════════════════════════════════
   HEADER FIXE
═══════════════════════════════════════════ */
.page-header {
    position: fixed;
    top: -18mm;
    left: -12mm;
    right: -12mm;
    height: 10mm;
    background-color: #1e3a8a;
    color: #ffffff;
    font-size: 7.5pt;
    font-weight: 700;
    padding: 0 16mm;
}
.page-header table { width:100%; border-collapse:collapse; height:10mm; }
.page-header td { vertical-align:middle; padding:0; }
.ph-right { text-align:right; color:#93c5fd; font-weight:400; font-size:7pt; }

/* ═══════════════════════════════════════════
   FOOTER FIXE
═══════════════════════════════════════════ */
.page-footer {
    position: fixed;
    bottom: -18mm;
    left: -12mm;
    right: -12mm;
    height: 10mm;
    border-top: 1px solid #e2e8f0;
    background-color: #f8fafc;
    color: #64748b;
    font-size: 7pt;
    padding: 0 16mm;
}
.page-footer table { width:100%; border-collapse:collapse; height:10mm; }
.page-footer td { vertical-align:middle; padding:0; }
.pf-right { text-align:right; font-weight:700; color:#1e3a8a; }
.pf-right .pn::before { content: counter(page); }
.pf-right .pnt::before { content: counter(pages); }

/* ═══════════════════════════════════════════
   COUVERTURE
═══════════════════════════════════════════ */
.cover { width:210mm; height:297mm; page-break-after:always; overflow:hidden; }
.cover-top {
    background-color: #1e3a8a;
    height: 185mm;
    padding: 18mm 18mm 14mm 18mm;
}
.cover-eyebrow {
    color: #93c5fd;
    font-size: 7.5pt;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-bottom: 12mm;
    display: block;
}
.cover-brand {
    color: #ffffff;
    font-size: 34pt;
    font-weight: 900;
    letter-spacing: -1px;
    display: block;
    margin-bottom: 2mm;
}
.cover-brand-sub {
    color: #bfdbfe;
    font-size: 13pt;
    font-weight: 400;
    display: block;
    margin-bottom: 11mm;
}
.cover-rule {
    border: none;
    border-top: 2px solid #3b82f6;
    width: 18mm;
    margin: 0 0 11mm 0;
}
.cover-title {
    color: #ffffff;
    font-size: 21pt;
    font-weight: 800;
    line-height: 1.2;
    display: block;
    margin-bottom: 5mm;
}
.cover-desc {
    color: #93c5fd;
    font-size: 10pt;
    line-height: 1.6;
    display: block;
}
.cover-bottom {
    background-color: #ffffff;
    height: 112mm;
    padding: 10mm 18mm;
}
.cover-stats { width:100%; border-collapse:collapse; margin-bottom:9mm; }
.cover-stats td {
    width: 33.33%;
    padding: 5mm 4mm 5mm 0;
    vertical-align: top;
    border-right: 1px solid #e2e8f0;
}
.cover-stats td:last-child { border-right:none; padding-right:0; padding-left:4mm; }
.cover-stats td:not(:first-child) { padding-left:4mm; }
.stat-num { font-size:24pt; font-weight:900; color:#1e3a8a; display:block; line-height:1; margin-bottom:1mm; }
.stat-label { font-size:8pt; color:#64748b; display:block; line-height:1.4; }
.cover-tags { margin-bottom:8mm; }
.cover-tag {
    display: inline-block;
    background-color: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1e40af;
    font-size: 7pt;
    font-weight: 700;
    padding: 1.5mm 3mm;
    border-radius: 2px;
    margin: 1mm 1mm 0 0;
}
.cover-colophon {
    border-top: 1px solid #e2e8f0;
    padding-top: 4mm;
    color: #94a3b8;
    font-size: 7.5pt;
    line-height: 1.8;
}

/* ═══════════════════════════════════════════
   SOMMAIRE
═══════════════════════════════════════════ */
.toc-page { page-break-after:always; padding-top:2mm; }
.section-eyebrow {
    font-size: 7pt; font-weight:700; letter-spacing:2px;
    text-transform:uppercase; color:#3b82f6;
    margin-bottom:2mm; display:block;
}
.page-title { font-size:20pt; font-weight:900; color:#0f172a; margin-bottom:1mm; display:block; }
.page-subtitle {
    font-size:9pt; color:#64748b; margin-bottom:6mm; display:block;
    border-bottom:2px solid #e2e8f0; padding-bottom:4mm;
}
.toc-item { border-bottom:1px solid #f1f5f9; padding:3mm 0; }
.toc-item table { width:100%; border-collapse:collapse; }
.toc-item td { vertical-align:middle; padding:0; }
.toc-num-cell { width:8mm; }
.toc-num-badge {
    width:6.5mm; height:6.5mm; border-radius:50%;
    background-color:#1e3a8a; color:#ffffff;
    font-size:6.5pt; font-weight:800;
    text-align:center; line-height:6.5mm; display:block;
}
.toc-icon-cell { width:8mm; text-align:center; font-size:12pt; }
.toc-content { padding-left:2mm; }
.toc-mod-title { font-size:9.5pt; font-weight:700; color:#0f172a; display:block; }
.toc-mod-desc { font-size:7.5pt; color:#64748b; display:block; margin-top:0.5mm; }
.toc-pg { width:12mm; text-align:right; font-size:8.5pt; font-weight:700; color:#1e3a8a; }

/* ═══════════════════════════════════════════
   MODULE PAGE
═══════════════════════════════════════════ */
.module-page { page-break-before:always; }
.module-banner {
    margin: -2mm -12mm 7mm -12mm;
    padding: 5.5mm 12mm;
    background-color: #1e3a8a;
    color: #ffffff;
}
.module-banner table { width:100%; border-collapse:collapse; }
.module-banner td { vertical-align:middle; padding:0; }
.mod-icon-cell { width:13mm; font-size:18pt; line-height:1; }
.mod-num { font-size:6.5pt; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#93c5fd; display:block; margin-bottom:1mm; }
.mod-title { font-size:14pt; font-weight:900; color:#ffffff; display:block; line-height:1.2; }
.mod-subtitle { font-size:7.5pt; color:#bfdbfe; display:block; margin-top:1mm; }

/* ═══════════════════════════════════════════
   SECTIONS
═══════════════════════════════════════════ */
.section-block { margin-bottom:6mm; page-break-inside:avoid; }
.section-title {
    font-size:10pt; font-weight:800; color:#0f172a;
    border-left:3px solid #3b82f6;
    padding:1.5mm 0 1.5mm 3mm;
    margin-bottom:3.5mm;
    background-color:#f8fafc;
    display:block;
}

/* ═══════════════════════════════════════════
   ÉTAPES
═══════════════════════════════════════════ */
.step-row { margin-bottom:2mm; }
.step-row table { width:100%; border-collapse:collapse; }
.step-row td { vertical-align:top; padding:0; }
.step-badge {
    width:5.5mm; height:5.5mm;
    background-color:#dbeafe; color:#1e40af;
    font-size:6.5pt; font-weight:800;
    text-align:center; line-height:5.5mm;
    border-radius:50%; display:block; margin-top:0.5mm;
}
.step-txt { padding-left:2.5mm; font-size:9pt; color:#334155; line-height:1.55; }
.bullet-row { margin-bottom:1.5mm; }
.bullet-row table { width:100%; border-collapse:collapse; }
.bullet-row td { vertical-align:top; }
.bullet-dot { width:4mm; font-size:9pt; color:#3b82f6; padding-top:0.3mm; }
.bullet-txt { font-size:9pt; color:#334155; line-height:1.55; }

/* ═══════════════════════════════════════════
   CALLOUTS
═══════════════════════════════════════════ */
.tip-box {
    background-color:#fefce8;
    border:1px solid #fef08a;
    border-left:3px solid #eab308;
    padding:3mm 4mm; margin-top:3.5mm; border-radius:2px;
}
.tip-box table { width:100%; border-collapse:collapse; }
.tip-box td { vertical-align:top; padding:0; }
.tip-icon { width:6mm; font-size:9pt; color:#854d0e; font-weight:900; }
.tip-content { padding-left:2mm; font-size:8.5pt; color:#713f12; line-height:1.5; }
.tip-label { font-weight:800; font-size:8pt; color:#854d0e; display:block; margin-bottom:1mm; }

.warn-box {
    background-color:#fff7ed;
    border:1px solid #fed7aa;
    border-left:3px solid #f97316;
    padding:3mm 4mm; margin-top:3.5mm; border-radius:2px;
}
.warn-box table { width:100%; border-collapse:collapse; }
.warn-box td { vertical-align:top; padding:0; }
.warn-icon { width:6mm; font-size:9pt; color:#9a3412; font-weight:900; }
.warn-content { padding-left:2mm; font-size:8.5pt; color:#7c2d12; line-height:1.5; }
.warn-label { font-weight:800; font-size:8pt; color:#9a3412; display:block; margin-bottom:1mm; }

.info-box {
    background-color:#eff6ff;
    border:1px solid #bfdbfe;
    border-left:3px solid #3b82f6;
    padding:3mm 4mm; margin-top:3.5mm; border-radius:2px;
}
.info-box table { width:100%; border-collapse:collapse; }
.info-box td { vertical-align:top; padding:0; }
.info-icon { width:6mm; font-size:9pt; color:#1d4ed8; font-weight:900; }
.info-content { padding-left:2mm; font-size:8.5pt; color:#1e40af; line-height:1.5; }
.info-label { font-weight:800; font-size:8pt; color:#1d4ed8; display:block; margin-bottom:1mm; }

/* ═══════════════════════════════════════════
   TABLEAUX
═══════════════════════════════════════════ */
.data-table { width:100%; border-collapse:collapse; font-size:8.5pt; margin-top:3mm; }
.data-table thead tr { background-color:#1e3a8a; color:#ffffff; }
.data-table thead th {
    padding:2.5mm 3mm; text-align:left;
    font-weight:700; font-size:7.5pt; letter-spacing:0.3px;
    border:1px solid #1e40af;
}
.data-table tbody tr { border-bottom:1px solid #e2e8f0; }
.data-table tbody tr:nth-child(even) { background-color:#f8fafc; }
.data-table tbody td {
    padding:2.5mm 3mm; color:#374151;
    border:1px solid #e2e8f0; vertical-align:middle;
}
.td-c { text-align:center; }
.ok { color:#16a34a; font-weight:800; font-size:10pt; }
.no { color:#cbd5e1; font-size:10pt; }
strong { font-weight:700; }
em { font-style:italic; }
code {
    background-color:#f1f5f9; border:1px solid #e2e8f0;
    padding:0.5mm 2mm; font-size:8pt;
    font-family:DejaVu Sans Mono, monospace; border-radius:2px;
}

/* ═══════════════════════════════════════════
   DEUX COLONNES
═══════════════════════════════════════════ */
.two-col { width:100%; border-collapse:collapse; margin-top:3mm; }
.two-col td { width:50%; vertical-align:top; padding-right:4mm; }
.two-col td:last-child { padding-right:0; padding-left:4mm; border-left:1px solid #e2e8f0; }
.col-label {
    font-size:7.5pt; font-weight:700; letter-spacing:1px;
    text-transform:uppercase; color:#3b82f6;
    margin-bottom:2mm; display:block;
}

/* ═══════════════════════════════════════════
   WORKFLOW
═══════════════════════════════════════════ */
.workflow { margin:4mm 0; }
.workflow table { border-collapse:collapse; }
.wf-badge {
    background-color:#1e3a8a; color:#ffffff;
    font-size:8pt; font-weight:700;
    padding:2mm 4mm; border-radius:3px;
}
.wf-arr { padding:0 3mm; color:#94a3b8; font-size:11pt; font-weight:700; }

/* ═══════════════════════════════════════════
   PAGE FINALE
═══════════════════════════════════════════ */
.final-page { page-break-before:always; text-align:center; padding-top:20mm; }
.final-icon { font-size:30pt; display:block; margin-bottom:5mm; }
.final-title { font-size:18pt; font-weight:900; color:#0f172a; margin-bottom:3mm; display:block; }
.final-sub { font-size:10pt; color:#64748b; line-height:1.7; margin-bottom:10mm; display:block; }
.contact-box {
    display:inline-block;
    border:1px solid #e2e8f0;
    border-radius:4px;
    padding:5mm 10mm;
    margin:0 auto 8mm auto;
    text-align:left;
}
.contact-box table { border-collapse:collapse; }
.contact-box td { padding:2mm 3mm; vertical-align:top; font-size:9pt; }
.contact-key { color:#64748b; width:24mm; font-size:8pt; }
.contact-val { color:#0f172a; font-weight:700; }
.final-footer {
    margin-top:15mm;
    border-top:1px solid #e2e8f0;
    padding-top:5mm;
    color:#94a3b8;
    font-size:7.5pt;
    line-height:2;
}
p { margin-bottom:3mm; }
</style>
</head>
<body>

{{-- ═══════════════ EN-TÊTE & PIED DE PAGE FIXES ═══════════════ --}}
<div class="page-header">
    <table><tr>
        <td>IBIG FactPro &nbsp;&bull;&nbsp; Guide Utilisateur Officiel</td>
        <td class="ph-right">v2.5 &nbsp;&bull;&nbsp; Juillet 2026</td>
    </tr></table>
</div>
<div class="page-footer">
    <table><tr>
        <td>factpro.ibigsoft.com &nbsp;&bull;&nbsp; support@ibigsoft.com</td>
        <td class="pf-right">Page <span class="pn"></span> / <span class="pnt"></span></td>
    </tr></table>
</div>

{{-- ═══════════════════ COUVERTURE ═══════════════════ --}}
<div class="cover">
    <div class="cover-top">
        <span class="cover-eyebrow">Documentation officielle &nbsp;&bull;&nbsp; IBIG Soft</span>
        <span class="cover-brand">IBIG FactPro</span>
        <span class="cover-brand-sub">Logiciel de facturation &amp; gestion commerciale</span>
        <hr class="cover-rule"/>
        <span class="cover-title">Guide Utilisateur<br/>Complet</span>
        <span class="cover-desc">
            Maîtrisez chaque fonctionnalité de A à Z :<br/>
            facturation, clients, stock, caisse POS, rapports, équipe &amp; abonnement.
        </span>
    </div>
    <div class="cover-bottom">
        <table class="cover-stats">
            <tr>
                <td>
                    <span class="stat-num">13</span>
                    <span class="stat-label">Modules<br/>complets</span>
                </td>
                <td>
                    <span class="stat-num">498</span>
                    <span class="stat-label">Modèles de<br/>documents</span>
                </td>
                <td>
                    <span class="stat-num">94</span>
                    <span class="stat-label">Styles<br/>visuels PDF</span>
                </td>
            </tr>
        </table>
        <div class="cover-tags">
            @foreach(['Démarrage rapide','Facturation OHADA','Caisse POS','Rapports &amp; FEC','Équipe &amp; Rôles','Mobile Money','API REST','White-label','Multi-sociétés','Conformité fiscale'] as $t)
            <span class="cover-tag">{!! $t !!}</span>
            @endforeach
        </div>
        <div class="cover-colophon">
            <strong>Version 2.5</strong> &nbsp;&bull;&nbsp; Juillet 2026 &nbsp;&bull;&nbsp;
            &copy; IBIG Soft. Tous droits réservés.<br/>
            Ce guide est mis à jour à chaque nouvelle version de FactPro.
        </div>
    </div>
</div>

{{-- ═══════════════════ SOMMAIRE ═══════════════════ --}}
<div class="toc-page">
    <span class="section-eyebrow">Navigation</span>
    <span class="page-title">Table des matières</span>
    <span class="page-subtitle">13 modules &nbsp;&bull;&nbsp; Procédures étape par étape &nbsp;&bull;&nbsp; IBIG FactPro v2.5</span>

    @php $toc = [
        ['🚀','Démarrage &amp; Configuration','Compte, société, équipe, templates, modes de paiement'],
        ['📄','Facturation','Devis, factures, avoirs, récurrences, envoi PDF et email'],
        ['👥','Clients &amp; CRM','Gestion, imports CSV, historique, relances, pipeline commercial'],
        ['📦','Produits &amp; Stock','Catalogue, alertes, codes-barres, inventaire, imports'],
        ['💰','Paiements','Encaissements, Mobile Money, reçus, rapprochement bancaire'],
        ['🖥','Caisse POS','Point de vente, sessions, clôture journalière, rapport X'],
        ['📊','Rapports &amp; Analytiques','Dashboard KPIs, export FEC/Excel, TVA, forecasting CA'],
        ['👤','Utilisateurs &amp; Rôles','Permissions, invitations, 2FA, réinitialisation mot de passe'],
        ['⚙','Paramètres','Numérotation, mentions légales, TVA, webhooks, intégrations'],
        ['💳','Abonnement &amp; Licence','Forfaits, limites, changement de plan, facturation prorata'],
        ['📋','Catalogue de modèles','498 modèles sectoriels, aperçu interactif, création 1 clic'],
        ['🎨','Styles visuels PDF','Recommandation auto, galerie, personnalisation couleurs'],
        ['📑','Types de documents','Workflow Devis→Facture, BL, acomptes, solde, contrats'],
    ]; @endphp

    @foreach($toc as $i => $m)
    <div class="toc-item">
        <table><tr>
            <td class="toc-num-cell"><span class="toc-num-badge">{{ $i+1 }}</span></td>
            <td class="toc-icon-cell">{!! $m[0] !!}</td>
            <td class="toc-content">
                <span class="toc-mod-title">{!! $m[1] !!}</span>
                <span class="toc-mod-desc">{!! $m[2] !!}</span>
            </td>
            <td class="toc-pg">{{ $i + 3 }}</td>
        </tr></table>
    </div>
    @endforeach
</div>

{{-- ═══════════════════ MODULE 1 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">🚀</td>
            <td>
                <span class="mod-num">Module 01</span>
                <span class="mod-title">Démarrage &amp; Configuration</span>
                <span class="mod-subtitle">Première mise en route de votre compte IBIG FactPro</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">1.1 &nbsp; Créer son compte</span>
        @foreach([
            'Rendez-vous sur <strong>factpro.ibigsoft.com</strong> et cliquez <em>Essai gratuit 14 jours</em>.',
            'Renseignez votre email professionnel et choisissez un mot de passe sécurisé.',
            'Cliquez <em>Créer mon compte</em> — aucune carte bancaire requise pour l\'essai.',
            'Confirmez votre adresse email via le lien reçu dans les 5 minutes.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">1.2 &nbsp; Configurer les informations société</span>
        @foreach([
            'Allez dans <strong>Paramètres &gt; Société</strong>.',
            'Renseignez : nom commercial, adresse complète, pays, numéro RCCM, NIF/NCC.',
            'Uploadez votre logo (PNG fond transparent recommandé, 800×200 px minimum).',
            'Configurez la devise par défaut et le fuseau horaire.',
            'Cliquez <em>Enregistrer</em> — ces informations apparaissent sur tous vos documents PDF.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro</span>
            Utilisez un logo PNG fond transparent haute résolution — il s'adapte automatiquement aux 94 styles de PDF, y compris les templates à fond sombre.</td>
        </tr></table></div>
    </div>

    <div class="section-block">
        <span class="section-title">1.3 &nbsp; Inviter les collaborateurs</span>
        @foreach([
            'Allez dans <strong>Paramètres &gt; Équipe &gt; Inviter un membre</strong>.',
            'Saisissez l\'email et sélectionnez le rôle : Admin / Comptable / Commercial / Caissier / Lecture seule.',
            'Un email avec lien d\'activation (valable 48h) est envoyé automatiquement.',
            'Le collaborateur accepte, crée son mot de passe et accède à l\'application immédiatement.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">1.4 &nbsp; Activer les modes de paiement</span>
        @foreach([
            'Allez dans <strong>Paramètres &gt; Paiements</strong>.',
            'Activez chaque opérateur : Orange Money, Wave, MTN MoMo, Moov Money, CinetPay, FedaPay, Espèces, Virement, Carte.',
            'Pour chaque Mobile Money, renseignez le numéro et le nom du titulaire.',
            'Ces informations s\'affichent sur tous vos liens de paiement client.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">1.5 &nbsp; Choisir le style visuel de vos documents</span>
        @foreach([
            'Allez dans <strong>Paramètres &gt; Templates</strong>.',
            'Parcourez les styles selon votre forfait (5 à 94 designs disponibles).',
            'Définissez la couleur primaire (en-tête) et la couleur secondaire (accents).',
            'Activez si nécessaire : QR code anti-falsification, signature électronique.',
            'Cliquez <em>Prévisualiser</em> puis <em>Enregistrer</em>.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro</span>
            Activez le QR code anti-falsification — il permet à vos clients de vérifier instantanément l'authenticité de chaque document en le scannant avec leur téléphone.</td>
        </tr></table></div>
    </div>
</div>

{{-- ═══════════════════ MODULE 2 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">📄</td>
            <td>
                <span class="mod-num">Module 02</span>
                <span class="mod-title">Facturation</span>
                <span class="mod-subtitle">Devis, factures, avoirs, récurrences et envoi automatique</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">2.1 &nbsp; Créer un devis</span>
        @foreach([
            'Allez dans <strong>Documents &gt; Nouveau devis</strong>.',
            'Dans le champ <em>Client</em>, tapez le nom pour le sélectionner — ou créez-le à la volée sans quitter le formulaire.',
            'Cliquez <em>Ajouter une ligne</em>, cherchez le produit/service, ajustez quantité et prix.',
            'Appliquez une remise globale (%) ou ligne par ligne.',
            'Vérifiez le total HT, la TVA et le total TTC calculés en temps réel.',
            'Cliquez <em>Enregistrer</em> (brouillon) ou <em>Finaliser</em> pour verrouiller et numéroter.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">2.2 &nbsp; Convertir un devis en facture</span>
        @foreach([
            'Ouvrez le devis accepté par le client.',
            'Cliquez <em>Convertir en facture</em> dans le menu <em>Actions</em>.',
            'Toutes les lignes, remises et conditions sont reprises automatiquement.',
            'Cliquez <em>Finaliser la facture</em> — un numéro séquentiel est attribué immédiatement.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="info-box"><table><tr>
            <td class="info-icon">ℹ</td>
            <td class="info-content"><span class="info-label">Bon à savoir</span>
            Depuis un devis accepté, vous pouvez aussi générer en 1 clic : Bon de commande, Bon de livraison ou Facture d'acompte — via le menu <em>Actions</em>.</td>
        </tr></table></div>
    </div>

    <div class="section-block">
        <span class="section-title">2.3 &nbsp; Envoyer par email avec PDF joint</span>
        @foreach([
            'Sur la page de la facture, cliquez <em>Envoyer par email</em>.',
            'L\'adresse du client et l\'objet sont pré-remplis. Le PDF est joint automatiquement.',
            'Personnalisez le message si besoin, puis cliquez <em>Envoyer</em>.',
            'Le suivi de lecture est activé — vous êtes notifié dès l\'ouverture par le client.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">2.4 &nbsp; Émettre un avoir</span>
        @foreach([
            'Ouvrez la facture à annuler.',
            'Cliquez <em>Créer un avoir</em> dans le menu <em>Actions</em>.',
            'Avoir total : conservez toutes les lignes. Avoir partiel : ajustez les quantités.',
            'Finalisez — l\'avoir est lié à la facture d\'origine et réduit le solde client automatiquement.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">2.5 &nbsp; Factures récurrentes</span>
        @foreach([
            'Allez dans <strong>Documents &gt; Récurrences &gt; Nouvelle récurrence</strong>.',
            'Créez le modèle : client, lignes produits, montants.',
            'Définissez la fréquence : hebdomadaire, mensuelle, trimestrielle ou annuelle.',
            'Indiquez la date de début et le nombre d\'occurrences (ou <em>Sans limite</em>).',
            'Activez l\'envoi automatique — les factures sont générées et envoyées sans intervention.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro</span>
            Idéal pour les SaaS, contrats de maintenance, loyers mensuels. Une fois configurée, la récurrence fonctionne de façon totalement autonome.</td>
        </tr></table></div>
    </div>
</div>

{{-- ═══════════════════ MODULE 3 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">👥</td>
            <td>
                <span class="mod-num">Module 03</span>
                <span class="mod-title">Clients &amp; CRM</span>
                <span class="mod-subtitle">Gestion des clients, imports, relances automatiques et pipeline commercial</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">3.1 &nbsp; Ajouter un client</span>
        @foreach([
            'Allez dans <strong>Clients &gt; Nouveau client</strong>.',
            'Renseignez : nom, email, téléphone, adresse, pays et type (Particulier / Entreprise).',
            'Pour les entreprises : renseignez les champs OHADA obligatoires <strong>RCCM</strong> et <strong>NIF</strong>.',
            'Enregistrez — le client est immédiatement disponible dans vos documents.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">3.2 &nbsp; Importer depuis CSV/Excel</span>
        @foreach([
            'Allez dans <strong>Clients &gt; Importer</strong> et téléchargez le modèle CSV.',
            'Colonnes obligatoires : <em>nom, email, pays</em>. Les autres colonnes sont facultatives.',
            'Importez le fichier — les doublons (même email) sont détectés et mis à jour.',
            'Un rapport d\'import indique les lignes créées, mises à jour et les erreurs.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro</span>
            Import jusqu'à 5 000 clients en une seule opération. Pour les très grands volumes, découpez en fichiers de 1 000 lignes pour un traitement plus rapide.</td>
        </tr></table></div>
    </div>

    <div class="section-block">
        <span class="section-title">3.3 &nbsp; Relances automatiques impayés</span>
        @foreach([
            'Allez dans <strong>Paramètres &gt; Relances</strong>.',
            'Créez une séquence : ex. J+15, J+30, J+45 après l\'échéance.',
            'Rédigez le message pour chaque étape (cordial → ferme → mise en demeure).',
            'Activez le canal : email, WhatsApp, ou les deux simultanément.',
            'FactPro envoie les relances automatiquement aux clients concernés.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">3.4 &nbsp; Pipeline commercial (CRM)</span>
        @foreach([
            'Allez dans <strong>Commercial &gt; Pipeline</strong>.',
            'Cliquez <em>Nouvelle opportunité</em> et liez-la à un client existant ou nouveau.',
            'Définissez : montant estimé, date de closing, probabilité de succès (%).',
            'Glissez la carte entre les colonnes : Prospection → Qualification → Proposition → Gagné / Perdu.',
            'Depuis une opportunité gagnée, convertissez directement en devis ou en facture.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════ MODULE 4 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">📦</td>
            <td>
                <span class="mod-num">Module 04</span>
                <span class="mod-title">Produits &amp; Stock</span>
                <span class="mod-subtitle">Catalogue, alertes, codes-barres et inventaire</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">4.1 &nbsp; Créer un produit ou un service</span>
        @foreach([
            'Allez dans <strong>Produits &gt; Nouveau produit</strong>.',
            'Renseignez : code SKU, désignation, catégorie, unité de mesure (pièce, kg, heure…).',
            'Entrez le <strong>prix de vente HT</strong> et sélectionnez le taux de TVA applicable.',
            'Pour un produit physique, activez <em>Suivi de stock</em> et entrez la quantité initiale.',
            'Définissez le seuil d\'alerte de stock faible (ex : 5 unités).',
            'Enregistrez — le produit est disponible immédiatement dans tous vos documents.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro</span>
            Avec un scanner de code-barres USB ou Bluetooth, scannez directement les produits lors des ventes POS et des inventaires — fini la saisie manuelle des codes SKU.</td>
        </tr></table></div>
    </div>

    <div class="section-block">
        <span class="section-title">4.2 &nbsp; Réaliser un inventaire</span>
        @foreach([
            'Allez dans <strong>Stocks &gt; Inventaire &gt; Nouvel inventaire</strong>.',
            'Scannez ou saisissez les quantités réelles pour chaque article.',
            'FactPro calcule automatiquement les écarts (stock théorique vs stock réel).',
            'Validez l\'inventaire — les stocks sont ajustés immédiatement.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">4.3 &nbsp; Générer des étiquettes codes-barres</span>
        @foreach([
            'Dans la liste produits, cochez les articles concernés.',
            'Cliquez <em>Actions &gt; Générer étiquettes</em>.',
            'Choisissez le format (A4, 3 colonnes, étiquettes Avery…) et imprimez.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════ MODULE 5 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">💰</td>
            <td>
                <span class="mod-num">Module 05</span>
                <span class="mod-title">Paiements</span>
                <span class="mod-subtitle">Encaissements, Mobile Money, reçus et rapprochement bancaire</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">5.1 &nbsp; Enregistrer un paiement</span>
        @foreach([
            'Ouvrez la facture concernée et cliquez <em>Enregistrer un paiement</em>.',
            'Indiquez le montant, le mode de paiement, la date et une référence optionnelle.',
            'Validez — la facture passe au statut <em>Payée</em> ou <em>Partiellement payée</em>.',
            'Un reçu de paiement est généré automatiquement et peut être envoyé au client.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">5.2 &nbsp; Modes de paiement disponibles</span>
        <table class="data-table">
            <thead><tr>
                <th>Mode de paiement</th>
                <th>Zone géographique</th>
                <th class="td-c">Lien client</th>
                <th class="td-c">Reçu auto</th>
            </tr></thead>
            <tbody>
                <tr><td><strong>Orange Money</strong></td><td>Afrique de l'Ouest</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td></tr>
                <tr><td><strong>Wave</strong></td><td>Sénégal, Côte d'Ivoire</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td></tr>
                <tr><td><strong>MTN MoMo</strong></td><td>Afrique subsaharienne</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td></tr>
                <tr><td><strong>Moov Money</strong></td><td>Afrique de l'Ouest</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td></tr>
                <tr><td><strong>CinetPay / FedaPay</strong></td><td>Afrique de l'Ouest</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td></tr>
                <tr><td><strong>Espèces</strong></td><td>Tous pays</td><td class="td-c no">—</td><td class="td-c ok">✓</td></tr>
                <tr><td><strong>Virement bancaire</strong></td><td>Tous pays</td><td class="td-c no">—</td><td class="td-c ok">✓</td></tr>
                <tr><td><strong>Carte bancaire</strong></td><td>International</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td></tr>
            </tbody>
        </table>
    </div>

    <div class="section-block">
        <span class="section-title">5.3 &nbsp; Rapprochement bancaire mensuel</span>
        @foreach([
            'Allez dans <strong>Rapports &gt; Rapprochement bancaire</strong>.',
            'Importez votre relevé bancaire au format CSV (depuis votre banque).',
            'FactPro rapproche automatiquement les transactions avec les paiements enregistrés.',
            'Traitez les écarts manuellement et validez le rapprochement.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════ MODULE 6 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">🖥</td>
            <td>
                <span class="mod-num">Module 06</span>
                <span class="mod-title">Caisse POS</span>
                <span class="mod-subtitle">Point de vente, sessions de caisse, clôture et rapport X</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">6.1 &nbsp; Ouvrir une session de caisse</span>
        @foreach([
            'Allez dans <strong>Caisse POS &gt; Ouvrir la session</strong>.',
            'Saisissez le <strong>fonds de caisse de départ</strong> (montant en espèces disponible).',
            'Cliquez <em>Démarrer la session</em> — l\'interface de vente s\'ouvre en plein écran.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro</span>
            Définissez un fonds de caisse fixe (ex : 50 000 FCFA). Il est automatiquement déduit lors du calcul d'écart à la clôture et simplifie la comptabilité journalière.</td>
        </tr></table></div>
    </div>

    <div class="section-block">
        <span class="section-title">6.2 &nbsp; Encaisser une vente</span>
        @foreach([
            'Scannez le code-barres ou recherchez les produits dans la barre de recherche POS.',
            'Ajustez les quantités avec les boutons + / −.',
            'Sélectionnez le mode de paiement (Espèces, Mobile Money, Carte, ou paiement partagé).',
            'Entrez le montant reçu — la monnaie à rendre est calculée automatiquement.',
            'Cliquez <em>Valider la vente</em> — le ticket est généré et envoyé à l\'imprimante thermique.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>

    <div class="section-block">
        <span class="section-title">6.3 &nbsp; Clôturer la caisse — Rapport X</span>
        @foreach([
            'En fin de journée : <strong>Caisse POS &gt; Session en cours &gt; Clôturer</strong>.',
            'Comptez physiquement les espèces et saisissez le montant réel en caisse.',
            'Le <strong>Rapport X</strong> est généré : total ventes, ventilation par mode de paiement, écart.',
            'Imprimez ou exportez le rapport X pour la comptabilité.',
            'Confirmez la clôture — la session est archivée et consultable à tout moment.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro — Mode Restauration</span>
            Activez les <em>Tables</em> dans <strong>Paramètres &gt; POS</strong> pour gérer les commandes par table et fusionner des additions — idéal pour la restauration avec service à table.</td>
        </tr></table></div>
    </div>
</div>

{{-- ═══════════════════ MODULE 7 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">📊</td>
            <td>
                <span class="mod-num">Module 07</span>
                <span class="mod-title">Rapports &amp; Analytiques</span>
                <span class="mod-subtitle">Dashboard KPIs, exports comptables, TVA et forecasting</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">7.1 &nbsp; Dashboard — KPIs en temps réel</span>
        <table class="two-col"><tr>
            <td>
                <span class="col-label">Indicateurs financiers</span>
                @foreach(['CA du mois vs mois précédent (variation %)','Factures en attente : nombre et montant total','Taux de recouvrement mensuel','Panier moyen par client'] as $s)
                <div class="bullet-row"><table><tr>
                    <td class="bullet-dot">›</td><td class="bullet-txt">{{ $s }}</td>
                </tr></table></div>
                @endforeach
            </td>
            <td>
                <span class="col-label">Indicateurs commerciaux</span>
                @foreach(['Top 5 produits les plus vendus','Top 5 clients par chiffre d\'affaires','Courbe CA sur 12 mois glissants','Objectifs commerciaux vs réalisé'] as $s)
                <div class="bullet-row"><table><tr>
                    <td class="bullet-dot">›</td><td class="bullet-txt">{{ $s }}</td>
                </tr></table></div>
                @endforeach
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">7.2 &nbsp; Export comptable (FEC / Excel OHADA / CSV)</span>
        @foreach([
            'Allez dans <strong>Rapports &gt; Export comptable</strong>.',
            'Sélectionnez la période et le format : <em>FEC</em> (France DGFiP 2026), <em>Excel OHADA</em>, ou <em>CSV</em>.',
            'Téléchargez et importez directement dans Sage, QuickBooks ou votre logiciel comptable.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="info-box"><table><tr>
            <td class="info-icon">ℹ</td>
            <td class="info-content"><span class="info-label">FEC obligatoire en France</span>
            Le Fichier des Écritures Comptables (FEC) est exigé lors des contrôles fiscaux. FactPro le génère automatiquement selon les spécifications DGFiP 2026.</td>
        </tr></table></div>
    </div>

    <div class="section-block">
        <span class="section-title">7.3 &nbsp; Déclaration de TVA</span>
        @foreach([
            'Allez dans <strong>Rapports &gt; TVA</strong> et sélectionnez la période.',
            'FactPro calcule : TVA collectée, TVA déductible, TVA nette à reverser.',
            'Exportez en PDF ou Excel pour votre déclaration fiscale.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════ MODULE 8 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">👤</td>
            <td>
                <span class="mod-num">Module 08</span>
                <span class="mod-title">Utilisateurs &amp; Rôles</span>
                <span class="mod-subtitle">Permissions, invitations, 2FA et gestion d'équipe</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">8.1 &nbsp; Matrice des permissions par rôle</span>
        <table class="data-table">
            <thead><tr>
                <th>Fonctionnalité</th>
                <th class="td-c">Admin</th>
                <th class="td-c">Comptable</th>
                <th class="td-c">Commercial</th>
                <th class="td-c">Caissier</th>
                <th class="td-c">Lecture</th>
            </tr></thead>
            <tbody>
                <tr><td>Créer / modifier des documents</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td><td class="td-c no">—</td><td class="td-c no">—</td></tr>
                <tr><td>Valider des paiements</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td><td class="td-c no">—</td><td class="td-c ok">✓</td><td class="td-c no">—</td></tr>
                <tr><td>Accéder aux rapports financiers</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td><td class="td-c no">—</td><td class="td-c no">—</td><td class="td-c ok">✓</td></tr>
                <tr><td>Gérer produits et stock</td><td class="td-c ok">✓</td><td class="td-c no">—</td><td class="td-c ok">✓</td><td class="td-c no">—</td><td class="td-c no">—</td></tr>
                <tr><td>Accéder à la caisse POS</td><td class="td-c ok">✓</td><td class="td-c no">—</td><td class="td-c no">—</td><td class="td-c ok">✓</td><td class="td-c no">—</td></tr>
                <tr><td>Gérer utilisateurs / équipe</td><td class="td-c ok">✓</td><td class="td-c no">—</td><td class="td-c no">—</td><td class="td-c no">—</td><td class="td-c no">—</td></tr>
                <tr><td>Modifier paramètres société</td><td class="td-c ok">✓</td><td class="td-c no">—</td><td class="td-c no">—</td><td class="td-c no">—</td><td class="td-c no">—</td></tr>
            </tbody>
        </table>
    </div>

    <div class="section-block">
        <span class="section-title">8.2 &nbsp; Activer la double authentification (2FA)</span>
        @foreach([
            'Allez dans <strong>Mon profil &gt; Sécurité &gt; Authentification à 2 facteurs</strong>.',
            'Cliquez <em>Activer</em> et scannez le QR code avec Google Authenticator ou Authy.',
            'Entrez le code à 6 chiffres pour confirmer l\'activation.',
            'Sauvegardez les codes de récupération dans un endroit sécurisé.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="warn-box"><table><tr>
            <td class="warn-icon">⚠</td>
            <td class="warn-content"><span class="warn-label">Important</span>
            Les codes de récupération sont à usage unique et constituent le seul moyen de récupérer votre compte en cas de perte de votre téléphone. Ne les perdez pas.</td>
        </tr></table></div>
    </div>
</div>

{{-- ═══════════════════ MODULE 9 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">⚙</td>
            <td>
                <span class="mod-num">Module 09</span>
                <span class="mod-title">Paramètres</span>
                <span class="mod-subtitle">Numérotation, mentions légales, TVA, webhooks et intégrations</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">9.1 &nbsp; Numérotation automatique des documents</span>
        @foreach([
            'Allez dans <strong>Paramètres &gt; Numérotation</strong>.',
            'Pour chaque type : définissez préfixe, inclusion de l\'année/mois, longueur de séquence.',
            'Exemples : <code>FACT-2026-0001</code>, <code>DEV-2026-0001</code>, <code>BL-0001</code>.',
            'Chaque type de document a son propre compteur indépendant.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro</span>
            Incluez l'année dans le préfixe pour un classement fiscal facile. La numérotation peut être configurée pour repartir de 1 chaque année automatiquement.</td>
        </tr></table></div>
    </div>

    <div class="section-block">
        <span class="section-title">9.2 &nbsp; Mentions légales et pied de page</span>
        @foreach([
            'Allez dans <strong>Paramètres &gt; Société &gt; Mentions légales</strong>.',
            'Saisissez vos mentions : RIB, conditions de paiement, pénalités de retard.',
            'Ces mentions apparaissent automatiquement en bas de tous vos documents PDF.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="info-box"><table><tr>
            <td class="info-icon">ℹ</td>
            <td class="info-content"><span class="info-label">Conformité OHADA</span>
            Les mentions obligatoires OHADA (RCCM, NIF, capital social, forme juridique) sont insérées automatiquement si renseignées dans la fiche société.</td>
        </tr></table></div>
    </div>

    <div class="section-block">
        <span class="section-title">9.3 &nbsp; Intégrations Zapier / Make</span>
        @foreach([
            'Allez dans <strong>Paramètres &gt; Intégrations &gt; Webhooks</strong>.',
            'Copiez l\'URL webhook dans votre scénario Zapier ou Make.',
            'Choisissez les événements déclencheurs : nouvelle facture, paiement reçu, stock faible…',
            'Connectez FactPro à des milliers d\'outils : Slack, Google Sheets, Notion, HubSpot…',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════ MODULE 10 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">💳</td>
            <td>
                <span class="mod-num">Module 10</span>
                <span class="mod-title">Abonnement &amp; Licence</span>
                <span class="mod-subtitle">Forfaits, limites par plan, changement et facturation prorata</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">10.1 &nbsp; Comparatif des forfaits</span>
        <table class="data-table">
            <thead><tr>
                <th>Fonctionnalité</th>
                <th class="td-c">Starter</th>
                <th class="td-c">Pro</th>
                <th class="td-c">Business</th>
                <th class="td-c">Enterprise</th>
            </tr></thead>
            <tbody>
                <tr><td><strong>Utilisateurs</strong></td><td class="td-c">1</td><td class="td-c">5</td><td class="td-c">20</td><td class="td-c">Illimité</td></tr>
                <tr><td><strong>Documents / mois</strong></td><td class="td-c">50</td><td class="td-c">500</td><td class="td-c">5 000</td><td class="td-c">Illimité</td></tr>
                <tr><td><strong>Styles PDF</strong></td><td class="td-c">5</td><td class="td-c">30</td><td class="td-c">94</td><td class="td-c">94+</td></tr>
                <tr><td><strong>Sociétés</strong></td><td class="td-c">1</td><td class="td-c">3</td><td class="td-c">10</td><td class="td-c">Illimité</td></tr>
                <tr><td><strong>API REST</strong></td><td class="td-c no">—</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td></tr>
                <tr><td><strong>White-label</strong></td><td class="td-c no">—</td><td class="td-c no">—</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td></tr>
                <tr><td><strong>Support prioritaire</strong></td><td class="td-c no">—</td><td class="td-c no">—</td><td class="td-c ok">✓</td><td class="td-c ok">✓</td></tr>
            </tbody>
        </table>
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro</span>
            Une alerte automatique est envoyée à 80% d'utilisation mensuelle — vous avez le temps de passer au forfait supérieur avant d'atteindre la limite.</td>
        </tr></table></div>
    </div>

    <div class="section-block">
        <span class="section-title">10.2 &nbsp; Changer de forfait</span>
        @foreach([
            'Allez dans <strong>Paramètres &gt; Abonnement</strong> et cliquez <em>Changer de forfait</em>.',
            'Les nouvelles fonctionnalités sont activées immédiatement.',
            'La facturation est calculée au prorata du mois en cours.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
    </div>
</div>

{{-- ═══════════════════ MODULE 11 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">📋</td>
            <td>
                <span class="mod-num">Module 11</span>
                <span class="mod-title">Catalogue de modèles</span>
                <span class="mod-subtitle">498 modèles sectoriels, aperçu interactif et création en 1 clic</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">11.1 &nbsp; Présentation</span>
        <p style="font-size:9pt; color:#334155; line-height:1.6;">
            FactPro propose <strong>498 modèles de documents</strong> prêts à l'emploi, organisés en <strong>24 catégories sectorielles</strong>.
            Chaque modèle intègre la structure et les mentions adaptées à son secteur d'activité.
        </p>
        <table class="two-col" style="margin-top:4mm"><tr>
            <td>
                <span class="col-label">Secteurs disponibles</span>
                @foreach(['BTP &amp; Travaux','Transport &amp; Logistique','Commerce &amp; Retail','Juridique &amp; Conseil','Santé &amp; Médical','Agriculture &amp; Élevage'] as $s)
                <div class="bullet-row"><table><tr>
                    <td class="bullet-dot">›</td><td class="bullet-txt">{!! $s !!}</td>
                </tr></table></div>
                @endforeach
            </td>
            <td>
                <span class="col-label">Et aussi</span>
                @foreach(['Restauration &amp; Hôtellerie','Informatique &amp; Tech','Finance &amp; Assurance','Immobilier','Éducation &amp; Formation','Énergie &amp; Industrie'] as $s)
                <div class="bullet-row"><table><tr>
                    <td class="bullet-dot">›</td><td class="bullet-txt">{!! $s !!}</td>
                </tr></table></div>
                @endforeach
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">11.2 &nbsp; Créer un document depuis le catalogue</span>
        @foreach([
            'Allez dans <strong>Documents &gt; Nouveau document &gt; Onglet Catalogue</strong>.',
            'Parcourez les 24 catégories ou utilisez la recherche pour trouver le bon modèle.',
            'Cliquez sur un modèle pour afficher son <strong>aperçu interactif</strong> avec données représentatives.',
            'L\'aperçu indique le style visuel PDF pré-sélectionné pour ce type de document.',
            'Cliquez <em>Créer ce document</em> — FactPro lance la création avec le bon type et le bon style.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro</span>
            L'aperçu affiche des données fictives — le document réel utilisera votre logo, vos informations société et les données réelles de votre client.</td>
        </tr></table></div>
    </div>
</div>

{{-- ═══════════════════ MODULE 12 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">🎨</td>
            <td>
                <span class="mod-num">Module 12</span>
                <span class="mod-title">Styles visuels PDF</span>
                <span class="mod-subtitle">Recommandation automatique, galerie et personnalisation</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">12.1 &nbsp; Style recommandé par type de document</span>
        <table class="data-table">
            <thead><tr><th>Type de document</th><th>Style recommandé par défaut</th></tr></thead>
            <tbody>
                <tr><td><strong>Bon de livraison</strong></td><td>Template Transport / Logistique</td></tr>
                <tr><td><strong>Contrat / Convention</strong></td><td>Template Juridique</td></tr>
                <tr><td><strong>Bon de commande fournisseur</strong></td><td>Template Corporate</td></tr>
                <tr><td><strong>Facture BTP / chantier</strong></td><td>Template BTP &amp; Travaux</td></tr>
                <tr><td><strong>Devis standard</strong></td><td>Template selon votre secteur paramétré</td></tr>
                <tr><td><strong>Facture commerciale</strong></td><td>Template Commerce</td></tr>
            </tbody>
        </table>
    </div>

    <div class="section-block">
        <span class="section-title">12.2 &nbsp; Modifier le style visuel</span>
        @foreach([
            'Lors de la création, seul le style recommandé est affiché (galerie réduite par défaut).',
            'Cliquez <em>Modifier</em> pour ouvrir la galerie complète (5, 30 ou 94 styles selon forfait).',
            'Cliquez sur un style pour voir l\'aperçu en temps réel.',
            'Sélectionnez le style — la galerie se referme et votre choix est appliqué.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="info-box"><table><tr>
            <td class="info-icon">ℹ</td>
            <td class="info-content"><span class="info-label">Contenu préservé</span>
            Changer de style visuel ne modifie jamais le contenu du document (lignes, montants, client). Vous pouvez changer de style à tout moment, même après finalisation.</td>
        </tr></table></div>
    </div>
</div>

{{-- ═══════════════════ MODULE 13 ═══════════════════ --}}
<div class="module-page">
    <div class="module-banner">
        <table><tr>
            <td class="mod-icon-cell">📑</td>
            <td>
                <span class="mod-num">Module 13</span>
                <span class="mod-title">Types de documents</span>
                <span class="mod-subtitle">Workflow commercial, Bon de livraison, acomptes et solde</span>
            </td>
        </tr></table>
    </div>

    <div class="section-block">
        <span class="section-title">13.1 &nbsp; Workflow commercial recommandé</span>
        <div class="workflow">
            <table><tr>
                <td><span class="wf-badge">Devis</span></td>
                <td><span class="wf-arr">→</span></td>
                <td><span class="wf-badge">Bon de commande</span></td>
                <td><span class="wf-arr">→</span></td>
                <td><span class="wf-badge">Bon de livraison</span></td>
                <td><span class="wf-arr">→</span></td>
                <td><span class="wf-badge">Facture</span></td>
            </tr></table>
        </div>
        <p style="font-size:8.5pt; color:#64748b; margin-top:3mm; margin-bottom:0;">
            Chaque étape se génère en 1 clic depuis le document précédent. Workflow recommandé, non obligatoire.
        </p>
    </div>

    <div class="section-block">
        <span class="section-title">13.2 &nbsp; Pourquoi le Bon de livraison n'a-t-il pas de prix ?</span>
        <p style="font-size:9pt; color:#334155; line-height:1.6; margin-bottom:0;">
            C'est un comportement <strong>normal et conforme à la norme OHADA</strong>. Le BL est un document de transport et de réception, pas un document financier. Il sert à confirmer les quantités livrées et à obtenir une signature de réception. Les prix figurent sur la facture associée.
        </p>
    </div>

    <div class="section-block">
        <span class="section-title">13.3 &nbsp; Acomptes et facture de solde</span>
        @foreach([
            'La <strong>Facture d\'acompte</strong> facture un % du total avant livraison (ex : 30% à la commande).',
            'Vous pouvez émettre plusieurs acomptes successifs pour le même projet.',
            'La <strong>Facture de solde</strong> déduit automatiquement tous les acomptes encaissés.',
            'Depuis le devis ou BC : cliquez <em>Générer la facture de solde</em>.',
        ] as $k => $s)
        <div class="step-row"><table><tr>
            <td style="width:7mm"><span class="step-badge">{{ $k+1 }}</span></td>
            <td class="step-txt">{!! $s !!}</td>
        </tr></table></div>
        @endforeach
        <div class="tip-box"><table><tr>
            <td class="tip-icon">💡</td>
            <td class="tip-content"><span class="tip-label">Conseil Pro — Grands projets</span>
            Pour les projets BTP ou IT, utilisez la séquence 30% + 40% + 30%. FactPro calcule automatiquement chaque montant et suit les encaissements.</td>
        </tr></table></div>
    </div>

    <div class="section-block">
        <span class="section-title">13.4 &nbsp; Tous les types de documents disponibles</span>
        <table class="data-table">
            <thead><tr><th>Document</th><th>Usage principal</th></tr></thead>
            <tbody>
                <tr><td><strong>Devis</strong></td><td>Offre commerciale envoyée avant acceptation client</td></tr>
                <tr><td><strong>Facture</strong></td><td>Document de facturation légal avec numérotation séquentielle</td></tr>
                <tr><td><strong>Avoir</strong></td><td>Annulation partielle ou totale d'une facture émise</td></tr>
                <tr><td><strong>Facture Proforma</strong></td><td>Offre indicative (douane, importation, financement)</td></tr>
                <tr><td><strong>Bon de commande</strong></td><td>Confirmation de commande signée par le client</td></tr>
                <tr><td><strong>Bon de livraison</strong></td><td>Document de transport et preuve de livraison (sans prix)</td></tr>
                <tr><td><strong>Facture d'acompte</strong></td><td>Encaissement partiel avant livraison complète</td></tr>
                <tr><td><strong>Facture de solde</strong></td><td>Solde final après déduction de tous les acomptes</td></tr>
                <tr><td><strong>Reçu de paiement</strong></td><td>Preuve d'encaissement émise au client</td></tr>
                <tr><td><strong>Contrat / Convention</strong></td><td>Accord commercial ou prestation de services signable</td></tr>
                <tr><td><strong>Quittance de loyer</strong></td><td>Attestation de paiement mensuel du loyer</td></tr>
                <tr><td><strong>Rapport d'intervention</strong></td><td>Compte-rendu technique de maintenance ou dépannage</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════ PAGE FINALE ═══════════════════ --}}
<div class="final-page">
    <span class="final-icon">🤖</span>
    <span class="final-title">Besoin d'aide ? Parlez à SARA</span>
    <span class="final-sub">Notre assistante IA répond à vos questions 24h/24, 7j/7.<br/>
    Accessible depuis le bouton <strong>?</strong> en bas à droite de chaque page de l'application.</span>

    <div class="contact-box">
        <table>
            <tr><td class="contact-key">Email support</td><td class="contact-val">support@ibigsoft.com</td></tr>
            <tr><td class="contact-key">Application</td><td class="contact-val">factpro.ibigsoft.com</td></tr>
            <tr><td class="contact-key">Documentation</td><td class="contact-val">factpro.ibigsoft.com/help</td></tr>
        </table>
    </div>

    <div class="final-footer">
        <strong>IBIG Soft</strong> &nbsp;&bull;&nbsp; Éditeur de IBIG FactPro<br/>
        Guide Utilisateur Officiel &nbsp;&bull;&nbsp; Version 2.5 &nbsp;&bull;&nbsp; Juillet 2026<br/>
        &copy; 2024–2026 IBIG Soft. Tous droits réservés. Reproduction interdite sans autorisation écrite.
    </div>
</div>

</body>
</html>
