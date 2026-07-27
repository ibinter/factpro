<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<title>Guide Utilisateur IBIG FactPro</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9.5pt;
    color: #1f2937;
    line-height: 1.55;
    background: #ffffff;
}

/* ─── PAGE BREAKS ─── */
.page-break { page-break-after: always; }

/* ─── COVER ─── */
.cover {
    background-color: #0062CC;
    padding: 70px 60px 60px 60px;
    text-align: center;
    page-break-after: always;
}
.cover-badge {
    display: inline-block;
    border: 1px solid #ffffff;
    border-radius: 20px;
    padding: 4px 18px;
    font-size: 8pt;
    color: #ffffff;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 30px;
}
.cover-logo {
    font-size: 40pt;
    font-weight: 900;
    color: #ffffff;
    margin-bottom: 6px;
    letter-spacing: -1px;
}
.cover-product {
    font-size: 16pt;
    color: #bfdbfe;
    margin-bottom: 40px;
}
.cover-divider {
    border: none;
    border-top: 2px solid #93c5fd;
    width: 80px;
    margin: 0 auto 40px auto;
}
.cover-meta {
    font-size: 10pt;
    color: #dbeafe;
    line-height: 2;
}
.cover-meta strong { color: #ffffff; }
.cover-chips {
    margin-top: 48px;
    text-align: center;
}
.cover-chip {
    display: inline-block;
    border: 1px solid #93c5fd;
    border-radius: 12px;
    padding: 3px 10px;
    font-size: 7.5pt;
    color: #dbeafe;
    margin: 3px 2px;
}
.cover-footer {
    margin-top: 50px;
    font-size: 8pt;
    color: #93c5fd;
}

/* ─── SOMMAIRE ─── */
.toc {
    padding: 50px 55px;
    page-break-after: always;
}
.toc-title {
    font-size: 20pt;
    font-weight: 700;
    color: #0062CC;
    border-bottom: 3px solid #0062CC;
    padding-bottom: 10px;
    margin-bottom: 6px;
}
.toc-subtitle {
    font-size: 8.5pt;
    color: #6b7280;
    margin-bottom: 28px;
}
.toc-row {
    border-bottom: 1px dotted #d1d5db;
    padding: 8px 0;
}
.toc-row table { width: 100%; border-collapse: collapse; }
.toc-row td { vertical-align: middle; }
.toc-icon-cell { width: 36px; }
.toc-icon-box {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    text-align: center;
    font-size: 14pt;
    line-height: 28px;
}
.toc-name { font-size: 10pt; font-weight: 700; color: #111827; padding-left: 10px; }
.toc-desc { font-size: 8pt; color: #6b7280; font-weight: 400; display: block; margin-top: 2px; }
.toc-num { font-size: 8.5pt; color: #9ca3af; text-align: right; width: 60px; }

/* ─── MODULE ─── */
.module {
    padding: 40px 55px 44px 55px;
    page-break-before: always;
}
.module-header {
    border-left: 5px solid #0062CC;
    padding: 8px 0 8px 16px;
    margin-bottom: 26px;
}
.module-icon { font-size: 22pt; line-height: 1; display: block; margin-bottom: 4px; }
.module-title { font-size: 17pt; font-weight: 800; color: #111827; line-height: 1.2; }
.module-subtitle { font-size: 9pt; color: #6b7280; margin-top: 2px; }

/* ─── SECTION ─── */
.section { margin-bottom: 22px; }
.section-header { margin-bottom: 7px; }
.section-header table { width: 100%; border-collapse: collapse; }
.section-num {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    text-align: center;
    font-size: 8pt;
    font-weight: 800;
    line-height: 22px;
    display: inline-block;
}
.section-title { font-size: 10.5pt; font-weight: 700; color: #1f2937; padding-left: 8px; vertical-align: middle; }

/* ─── ÉTAPES ─── */
.steps { list-style: none; margin: 0; padding: 0; }
.step-row { padding: 2.5px 0; font-size: 9pt; color: #374151; line-height: 1.5; }
.step-row table { border-collapse: collapse; width: 100%; }
.step-n { font-weight: 700; width: 20px; vertical-align: top; }
.step-text { vertical-align: top; }
.bullets { list-style: none; margin: 0; padding: 0; }
.bullet-row { padding: 2px 0; font-size: 9pt; color: #374151; }
.bullet-row table { border-collapse: collapse; width: 100%; }
.bullet-dot { width: 14px; vertical-align: top; }

/* ─── CONSEIL PRO ─── */
.tip {
    background-color: #fffbeb;
    border: 1px solid #fbbf24;
    border-left: 4px solid #f59e0b;
    border-radius: 5px;
    padding: 8px 12px;
    margin-top: 10px;
    font-size: 8.5pt;
    color: #78350f;
}
.tip table { border-collapse: collapse; width: 100%; }
.tip-label { font-weight: 700; width: 90px; vertical-align: top; padding-right: 6px; }
.tip-text { vertical-align: top; }

/* ─── ATTENTION ─── */
.warning {
    background-color: #fff1f2;
    border: 1px solid #fca5a5;
    border-left: 4px solid #ef4444;
    border-radius: 5px;
    padding: 8px 12px;
    margin-top: 10px;
    font-size: 8.5pt;
    color: #7f1d1d;
}
.warning table { border-collapse: collapse; width: 100%; }
.warning-label { font-weight: 700; width: 90px; vertical-align: top; }

/* ─── TABLEAUX ─── */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5pt;
    margin-top: 10px;
}
.data-table th {
    background-color: #f3f4f6;
    color: #374151;
    font-weight: 700;
    text-align: left;
    padding: 6px 10px;
    border: 1px solid #e5e7eb;
}
.data-table td {
    padding: 5px 10px;
    border: 1px solid #e5e7eb;
    color: #374151;
}
.data-table tr:nth-child(even) td { background-color: #f9fafb; }
.td-center { text-align: center; }
.check { color: #059669; font-weight: 700; }
.dash  { color: #9ca3af; }

/* ─── WORKFLOW ─── */
.workflow-bar { margin: 10px 0; font-size: 9pt; }
.workflow-bar table { border-collapse: collapse; }
.wf-step {
    background-color: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 5px;
    padding: 4px 10px;
    font-weight: 700;
    color: #1d4ed8;
}
.wf-arrow { color: #9ca3af; font-weight: 700; padding: 0 6px; }

/* ─── PAGE FINALE ─── */
.final-page {
    padding: 80px 55px;
    text-align: center;
    page-break-before: always;
}
.final-icon { font-size: 36pt; margin-bottom: 16px; display: block; }
.final-title { font-size: 18pt; font-weight: 800; color: #111827; margin-bottom: 8px; }
.final-body { font-size: 10pt; color: #6b7280; line-height: 1.7; margin-bottom: 30px; }
.final-footer { font-size: 8.5pt; color: #9ca3af; line-height: 2; border-top: 1px solid #e5e7eb; padding-top: 20px; margin-top: 30px; }

code {
    background-color: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 3px;
    padding: 1px 4px;
    font-size: 8pt;
    font-family: DejaVu Sans Mono, monospace;
}
strong { font-weight: 700; }
em { font-style: italic; }
.text-muted { color: #6b7280; font-size: 8.5pt; }

/* ─── COULEURS PAR MODULE ─── */
.bg-blue   { background-color: #dbeafe; color: #1d4ed8; }
.bg-purple { background-color: #ede9fe; color: #5b21b6; }
.bg-red    { background-color: #fee2e2; color: #991b1b; }
.bg-cyan   { background-color: #cffafe; color: #155e75; }
.bg-amber  { background-color: #fef3c7; color: #78350f; }
.bg-green  { background-color: #dcfce7; color: #14532d; }
.bg-teal   { background-color: #ccfbf1; color: #134e4a; }
.bg-rose   { background-color: #ffe4e6; color: #881337; }

.c-blue   { color: #0062CC; }
.c-purple { color: #7c3aed; }
.c-red    { color: #dc2626; }
.c-cyan   { color: #0891b2; }
.c-amber  { color: #d97706; }
.c-green  { color: #059669; }
.c-teal   { color: #0d9488; }

.border-blue   { border-left-color: #0062CC !important; }
.border-purple { border-left-color: #7c3aed !important; }
.border-red    { border-left-color: #dc2626 !important; }
.border-cyan   { border-left-color: #0891b2 !important; }
.border-amber  { border-left-color: #d97706 !important; }
.border-green  { border-left-color: #059669 !important; }
.border-teal   { border-left-color: #0d9488 !important; }
.border-rose   { border-left-color: #be185d !important; }
.border-indigo { border-left-color: #4338ca !important; }
.border-orange { border-left-color: #ea580c !important; }
</style>
</head>
<body>

{{-- ══════════════════════════════════════════════════
     COUVERTURE
══════════════════════════════════════════════════ --}}
<div class="cover">
  <div class="cover-badge">Documentation officielle</div><br/><br/>
  <div class="cover-logo">IBIG FactPro</div>
  <div class="cover-product">Guide utilisateur complet</div>
  <hr class="cover-divider"/>
  <div class="cover-meta">
    <strong>Version 2.5</strong> &nbsp;&middot;&nbsp; Juillet 2026<br/>
    13 modules &nbsp;&middot;&nbsp; Procédures étape par étape<br/>
    498 modèles de documents &nbsp;&middot;&nbsp; Conforme OHADA / SYSCOHADA
  </div>
  <div class="cover-chips">
    <span class="cover-chip">Démarrage</span>
    <span class="cover-chip">Facturation</span>
    <span class="cover-chip">Clients</span>
    <span class="cover-chip">Stock</span>
    <span class="cover-chip">Paiements</span>
    <span class="cover-chip">Caisse POS</span>
    <span class="cover-chip">Rapports</span>
    <span class="cover-chip">Équipe &amp; Rôles</span>
    <span class="cover-chip">Paramètres</span>
    <span class="cover-chip">Abonnement</span>
    <span class="cover-chip">Catalogue</span>
    <span class="cover-chip">Styles PDF</span>
    <span class="cover-chip">Types de documents</span>
  </div>
  <div class="cover-footer">
    IBIG Soft &nbsp;&middot;&nbsp; factpro.ibigsoft.com &nbsp;&middot;&nbsp; support@ibigsoft.com
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     SOMMAIRE
══════════════════════════════════════════════════ --}}
<div class="toc">
  <div class="toc-title">Table des matières</div>
  <div class="toc-subtitle">13 modules &nbsp;&middot;&nbsp; Guide complet &nbsp;&middot;&nbsp; IBIG FactPro v2.5</div>

  @php
  $modules = [
    ['icon'=>'🚀','bg'=>'#dbeafe','title'=>'Module 1 — Démarrage','desc'=>'Création de compte, société, équipe, templates'],
    ['icon'=>'📄','bg'=>'#ede9fe','title'=>'Module 2 — Facturation','desc'=>'Devis, factures, avoirs, récurrences, envoi PDF'],
    ['icon'=>'👥','bg'=>'#fee2e2','title'=>'Module 3 — Clients','desc'=>'Gestion, imports CSV, historique, relances, CRM'],
    ['icon'=>'📦','bg'=>'#cffafe','title'=>'Module 4 — Produits & Stock','desc'=>'Catalogue, alertes, codes-barres, inventaire'],
    ['icon'=>'💰','bg'=>'#fef3c7','title'=>'Module 5 — Paiements','desc'=>'Encaissements, Mobile Money, reçus, rapprochement'],
    ['icon'=>'🖥️','bg'=>'#dcfce7','title'=>'Module 6 — Caisse POS','desc'=>'Point de vente, sessions, clôture, rapport X'],
    ['icon'=>'📊','bg'=>'#ccfbf1','title'=>'Module 7 — Rapports','desc'=>'Analytics, FEC, TVA, exports, forecasting CA'],
    ['icon'=>'👤','bg'=>'#ffe4e6','title'=>'Module 8 — Utilisateurs & Rôles','desc'=>'Permissions, invitations, 2FA, réinitialisation'],
    ['icon'=>'⚙️','bg'=>'#e0e7ff','title'=>'Module 9 — Paramètres','desc'=>'Numérotation, mentions légales, TVA, webhooks'],
    ['icon'=>'💳','bg'=>'#ffedd5','title'=>'Module 10 — Abonnement & Licence','desc'=>'Forfaits, limites, changement de plan, prorata'],
    ['icon'=>'📋','bg'=>'#dbeafe','title'=>'Module 11 — Catalogue de modèles','desc'=>'498 modèles sectoriels, aperçu, création 1 clic'],
    ['icon'=>'🎨','bg'=>'#ede9fe','title'=>'Module 12 — Styles visuels PDF','desc'=>'Recommandation auto, galerie, personnalisation'],
    ['icon'=>'📑','bg'=>'#dcfce7','title'=>'Module 13 — Types de documents','desc'=>'Workflow Devis→Facture, BL, acomptes, solde'],
  ];
  @endphp

  @foreach($modules as $i => $m)
  <div class="toc-row">
    <table><tr>
      <td class="toc-icon-cell">
        <div class="toc-icon-box" style="background-color:{{ $m['bg'] }}">{{ $m['icon'] }}</div>
      </td>
      <td>
        <span class="toc-name">{{ $m['title'] }}<span class="toc-desc">{{ $m['desc'] }}</span></span>
      </td>
      <td class="toc-num">Module {{ $i+1 }}</td>
    </tr></table>
  </div>
  @endforeach
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 1 — DÉMARRAGE
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-blue">
    <span class="module-icon">🚀</span>
    <span class="module-title">Module 1 — Démarrage</span><br/>
    <span class="module-subtitle">Première configuration de votre compte FactPro</span>
  </div>

  <div class="section">
    <div class="section-header">
      <table><tr>
        <td style="width:26px"><span class="section-num bg-blue">1</span></td>
        <td class="section-title">Créer son compte et configurer la société</td>
      </tr></table>
    </div>
    @foreach([
      ['Rendez-vous sur <strong>factpro.ibigsoft.com</strong> et cliquez sur <em>Essai gratuit</em>.','blue'],
      ['Renseignez votre adresse email et choisissez un mot de passe sécurisé (8 car. min, 1 majuscule, 1 chiffre).','blue'],
      ['Cliquez sur <em>Créer mon compte</em> — aucune carte bancaire requise pour l\'essai.','blue'],
      ['Allez dans <strong>Paramètres &gt; Société</strong>.','blue'],
      ['Renseignez : nom commercial, adresse complète, numéro RCCM, numéro d\'impôt (NIF/NCC).','blue'],
      ['Uploadez votre logo (PNG fond transparent recommandé, 800×200 px minimum).','blue'],
      ['Enregistrez — le logo et les informations apparaissent sur tous vos documents.','blue'],
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-blue">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s[0] !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Utilisez un logo PNG fond transparent de bonne résolution — il apparaît sur tous les 94 styles de PDF disponibles, y compris les templates sombres et premium.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-blue">2</span></td>
      <td class="section-title">Inviter les premiers collaborateurs</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Équipe</strong> et cliquez <em>Inviter un membre</em>.',
      'Saisissez l\'adresse email du collaborateur.',
      'Sélectionnez son rôle : <em>Admin, Comptable, Commercial, Caissier</em> ou <em>Lecture seule</em>.',
      'Cliquez <em>Envoyer l\'invitation</em> — un email avec lien d\'activation (valable 48h) est envoyé.',
      'Le collaborateur accepte et crée son mot de passe.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-blue">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-blue">3</span></td>
      <td class="section-title">Paramétrer les modes de paiement acceptés</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Paiements</strong>.',
      'Activez chaque mode : Orange Money, Wave, MTN MoMo, Moov Money, Espèces, Virement, Carte.',
      'Pour chaque Mobile Money, renseignez le numéro de compte et le nom du titulaire.',
      'Ces modes apparaissent sur les liens de paiement envoyés aux clients.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-blue">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-blue">4</span></td>
      <td class="section-title">Personnaliser les modèles de documents</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Templates</strong>.',
      'Choisissez parmi les modèles disponibles selon votre forfait (5 à 94 styles).',
      'Définissez la couleur primaire (en-tête) et la couleur secondaire (accents).',
      'Activez ou désactivez : signature électronique, QR code anti-falsification, mentions légales.',
      'Prévisualisez le rendu PDF avant de sauvegarder.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-blue">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Le QR code anti-falsification intégré permet à votre client de vérifier l'authenticité du document en le scannant — activez-le pour renforcer la confiance et la crédibilité.</td>
    </tr></table></div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 2 — FACTURATION
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-purple">
    <span class="module-icon">📄</span>
    <span class="module-title">Module 2 — Facturation</span><br/>
    <span class="module-subtitle">Devis, factures, avoirs et récurrences</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-purple">1</span></td>
      <td class="section-title">Créer un devis</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Documents &gt; Nouveau devis</strong>.',
      'Dans le champ <em>Client</em>, tapez le nom pour le sélectionner ou créez-le à la volée.',
      'Cliquez <em>Ajouter une ligne</em>, cherchez le produit/service, ajustez quantité et prix unitaire.',
      'Appliquez une remise globale (%) ou par ligne si nécessaire.',
      'Vérifiez le total HT, la TVA et le total TTC.',
      'Cliquez <em>Enregistrer</em> (brouillon) ou <em>Finaliser</em> pour verrouiller et numéroter.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-purple">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-purple">2</span></td>
      <td class="section-title">Convertir un devis en facture en 1 clic</td>
    </tr></table></div>
    @foreach([
      'Ouvrez le devis accepté par le client.',
      'Cliquez le bouton <em>Convertir en facture</em> dans le menu Actions.',
      'Vérifiez les informations reprises automatiquement (lignes, remises, conditions).',
      'Cliquez <em>Finaliser la facture</em> — un numéro séquentiel est attribué automatiquement.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-purple">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Depuis un devis accepté, vous pouvez aussi générer directement un Bon de commande ou un Bon de livraison en un clic depuis le menu <em>Actions</em>.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-purple">3</span></td>
      <td class="section-title">Envoyer par email avec PDF en pièce jointe</td>
    </tr></table></div>
    @foreach([
      'Sur la page de la facture, cliquez <em>Envoyer par email</em>.',
      'L\'adresse du client et l\'objet sont pré-remplis. Le PDF est joint automatiquement.',
      'Personnalisez le message si besoin, puis cliquez <em>Envoyer</em>.',
      'Le suivi de lecture est activé — vous serez notifié quand le client ouvre l\'email.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-purple">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-purple">4</span></td>
      <td class="section-title">Créer un avoir (annulation partielle ou totale)</td>
    </tr></table></div>
    @foreach([
      'Ouvrez la facture à annuler.',
      'Cliquez <em>Créer un avoir</em> dans le menu Actions.',
      'Pour un avoir total, conservez toutes les lignes. Pour un avoir partiel, ajustez les quantités.',
      'Finalisez l\'avoir — il est lié à la facture d\'origine et réduit le solde dû.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-purple">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-purple">5</span></td>
      <td class="section-title">Factures récurrentes</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Documents &gt; Récurrences &gt; Nouvelle récurrence</strong>.',
      'Créez le modèle de facture (client, lignes, montants).',
      'Définissez la fréquence : hebdomadaire, mensuelle, trimestrielle ou annuelle.',
      'Indiquez la date de début et le nombre d\'occurrences (ou <em>Sans limite</em>).',
      'Activez la récurrence — les factures sont générées et envoyées automatiquement.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-purple">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Idéal pour les abonnements SaaS, loyers mensuels ou contrats de maintenance. Activez l'envoi automatique par email pour ne plus avoir à y penser.</td>
    </tr></table></div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 3 — CLIENTS
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-red">
    <span class="module-icon">👥</span>
    <span class="module-title">Module 3 — Clients</span><br/>
    <span class="module-subtitle">Gestion des clients, imports et relances</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-red">1</span></td>
      <td class="section-title">Ajouter un client</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Clients &gt; Nouveau client</strong>.',
      'Renseignez : nom, email, téléphone, adresse, pays et type (Particulier / Entreprise).',
      'Pour les entreprises, renseignez les champs OHADA : <strong>RCCM</strong> et <strong>NIF</strong> (obligatoires sur les factures légales).',
      'Enregistrez — le client est immédiatement disponible pour vos documents.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-red">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-red">2</span></td>
      <td class="section-title">Importer depuis un fichier CSV/Excel</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Import &gt; Clients</strong>.',
      'Téléchargez le modèle CSV fourni par FactPro.',
      'Remplissez les colonnes obligatoires : nom, email, pays. Les autres colonnes sont facultatives.',
      'Importez le fichier — les doublons sont détectés par email et mis à jour.',
      'Un rapport d\'import indique les lignes créées, mises à jour et les erreurs.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-red">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Vous pouvez importer jusqu'à 5 000 clients en une seule opération. Pour les gros volumes, découpez en fichiers de 1 000 lignes pour un traitement plus rapide.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-red">3</span></td>
      <td class="section-title">Consulter l'historique complet d'un client</td>
    </tr></table></div>
    @foreach([
      'Cliquez sur le nom du client dans la liste.',
      'Onglet <em>Documents</em> : tous les devis, factures et avoirs liés.',
      'Onglet <em>Paiements</em> : historique de tous les encaissements.',
      'Onglet <em>Statistiques</em> : CA total, panier moyen, taux de paiement.',
      'Le <strong>solde dû</strong> est affiché en rouge en haut si des factures sont impayées.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-red">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-red">4</span></td>
      <td class="section-title">Configurer les relances automatiques</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Relances</strong>.',
      'Créez une séquence : ex. J+15, J+30, J+45 après l\'échéance de la facture.',
      'Rédigez ou personnalisez le message de relance pour chaque étape (cordial → ferme).',
      'Activez le canal : email, WhatsApp, ou les deux.',
      'FactPro envoie les relances automatiquement aux clients avec des factures échues.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-red">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">La séquence "3 relances" avec escalade de ton (cordial J+15 → ferme J+30 → mise en demeure J+45) augmente significativement le taux de recouvrement.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-red">5</span></td>
      <td class="section-title">Pipeline commercial (CRM)</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Commercial &gt; Pipeline</strong>.',
      'Cliquez <em>Nouvelle opportunité</em> et liez-la à un client existant ou nouveau.',
      'Définissez : montant estimé, date de closing, probabilité de succès (%).',
      'Glissez la carte entre les colonnes : Prospection → Qualification → Proposition → Gagné/Perdu.',
      'Depuis une opportunité Gagnée, convertissez directement en devis ou facture.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-red">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 4 — PRODUITS & STOCK
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-cyan">
    <span class="module-icon">📦</span>
    <span class="module-title">Module 4 — Produits &amp; Stock</span><br/>
    <span class="module-subtitle">Catalogue, alertes, codes-barres et inventaire</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-cyan">1</span></td>
      <td class="section-title">Créer un produit ou service</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Produits &gt; Nouveau produit</strong>.',
      'Renseignez : code SKU, désignation, catégorie, unité de mesure.',
      'Entrez le <strong>prix HT</strong> et sélectionnez le taux de TVA applicable.',
      'Pour un produit physique, activez <em>Suivi de stock</em> et entrez la quantité initiale.',
      'Enregistrez — le produit est immédiatement disponible lors de la création de documents.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-cyan">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-cyan">2</span></td>
      <td class="section-title">Alertes de stock faible</td>
    </tr></table></div>
    @foreach([
      'Dans la fiche produit, renseignez le champ <strong>Seuil d\'alerte</strong> (ex : 5 unités).',
      'Quand le stock descend sous ce seuil, une alerte apparaît sur le tableau de bord.',
      'Vous recevez également un email de notification automatique.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-cyan">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Avec un scanner de code-barres, vous pouvez scanner les produits lors d'une vente POS ou d'un inventaire — plus besoin de saisir manuellement les codes SKU.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-cyan">3</span></td>
      <td class="section-title">Importer un catalogue produits depuis CSV</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Produits &gt; Importer</strong>.',
      'Téléchargez le modèle CSV (colonnes : SKU, nom, catégorie, prix HT, TVA, stock initial).',
      'Remplissez et uploadez. Les produits existants (même SKU) sont mis à jour.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-cyan">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-cyan">4</span></td>
      <td class="section-title">Générer des étiquettes codes-barres</td>
    </tr></table></div>
    @foreach([
      'Dans la liste produits, cochez les articles concernés.',
      'Cliquez <em>Actions &gt; Générer étiquettes</em>.',
      'Choisissez le format d\'étiquette (A4, 3 colonnes…) et cliquez <em>Imprimer</em>.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-cyan">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-cyan">5</span></td>
      <td class="section-title">Réaliser un inventaire</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Stocks &gt; Inventaire &gt; Nouvel inventaire</strong>.',
      'Scannez ou saisissez les quantités réelles pour chaque article.',
      'FactPro calcule les écarts (stock théorique vs stock réel).',
      'Validez l\'inventaire pour appliquer les ajustements de stock.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-cyan">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 5 — PAIEMENTS
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-amber">
    <span class="module-icon">💰</span>
    <span class="module-title">Module 5 — Paiements</span><br/>
    <span class="module-subtitle">Encaissements, Mobile Money et rapprochement bancaire</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-amber">1</span></td>
      <td class="section-title">Enregistrer un paiement</td>
    </tr></table></div>
    @foreach([
      'Ouvrez la facture concernée.',
      'Cliquez <em>Enregistrer un paiement</em>.',
      'Indiquez le montant, le mode (Espèces, Virement, Mobile Money…), la date et une référence optionnelle.',
      'Validez — la facture passe au statut <em>Payée</em> ou <em>Partiellement payée</em>.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-amber">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-amber">2</span></td>
      <td class="section-title">Configurer Orange Money / Wave / MTN MoMo</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Paiements</strong>.',
      'Activez chaque opérateur souhaité et renseignez le numéro + nom du titulaire.',
      'Ces informations s\'affichent sur les liens de paiement envoyés aux clients.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-amber">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Activez la passerelle CinetPay ou FedaPay pour permettre à vos clients de payer directement en ligne via un lien sécurisé, sans aucune intervention de votre part.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-amber">3</span></td>
      <td class="section-title">Générer un reçu de paiement</td>
    </tr></table></div>
    @foreach([
      'Après validation d\'un paiement, cliquez <em>Voir le reçu</em>.',
      'Le reçu PDF est généré avec les détails complets du paiement.',
      'Cliquez <em>Envoyer au client</em> pour l\'envoyer par email ou WhatsApp.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-amber">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-amber">4</span></td>
      <td class="section-title">Rapprochement bancaire mensuel</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Rapports &gt; Rapprochement bancaire</strong>.',
      'Importez votre relevé bancaire (CSV depuis votre banque).',
      'FactPro rapproche automatiquement les transactions avec les paiements enregistrés.',
      'Traitez les écarts manuellement et validez le rapprochement.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-amber">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 6 — CAISSE POS
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-green">
    <span class="module-icon">🖥️</span>
    <span class="module-title">Module 6 — Caisse POS</span><br/>
    <span class="module-subtitle">Point de vente, sessions, clôture et rapport X</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-green">1</span></td>
      <td class="section-title">Ouvrir une session de caisse</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Caisse POS &gt; Ouvrir la session</strong>.',
      'Saisissez le fonds de caisse de départ (montant en espèces disponible).',
      'Cliquez <em>Démarrer la session</em> — l\'interface de vente s\'ouvre.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-green">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Définissez un fonds de caisse constant (ex: 50 000 FCFA) — il sera déduit lors du calcul de l'écart en clôture et facilite la comptabilité journalière.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-green">2</span></td>
      <td class="section-title">Encaisser une vente</td>
    </tr></table></div>
    @foreach([
      'Scannez ou cherchez les produits dans la barre de recherche.',
      'Ajustez les quantités en cliquant sur + / −.',
      'Sélectionnez le mode de paiement (Espèces, Mobile Money, Carte…).',
      'Entrez le montant reçu — la monnaie à rendre est calculée automatiquement.',
      'Cliquez <em>Valider la vente</em> — un ticket est généré et imprimé automatiquement.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-green">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-green">3</span></td>
      <td class="section-title">Clôture de caisse et Rapport X</td>
    </tr></table></div>
    @foreach([
      'En fin de journée, allez dans <strong>Caisse &gt; Session en cours &gt; Clôturer</strong>.',
      'Comptez les espèces et saisissez le montant réel en caisse.',
      'Le <strong>Rapport X</strong> est généré : total ventes, ventilation par mode de paiement, écart caisse.',
      'Imprimez ou exportez le rapport pour la comptabilité.',
      'Confirmez la clôture — la session est archivée.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-green">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">En mode restauration, activez les <em>Tables</em> dans <strong>Paramètres &gt; POS</strong> pour gérer les commandes par table et fusionner plusieurs additions — idéal pour les restaurants avec service à table.</td>
    </tr></table></div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 7 — RAPPORTS
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-teal">
    <span class="module-icon">📊</span>
    <span class="module-title">Module 7 — Rapports</span><br/>
    <span class="module-subtitle">Analytics, exports comptables, TVA et forecasting</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-teal">1</span></td>
      <td class="section-title">Dashboard KPIs</td>
    </tr></table></div>
    @foreach([
      '<strong>CA du mois</strong> vs mois précédent avec variation en %.',
      '<strong>Factures en attente</strong> : nombre et montant total à encaisser.',
      '<strong>Top produits</strong> : les 5 articles les plus vendus du mois.',
      '<strong>Top clients</strong> : les meilleurs clients par chiffre d\'affaires.',
      '<strong>Courbe CA</strong> : évolution mensuelle sur 12 mois.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-teal">&bull;</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-teal">2</span></td>
      <td class="section-title">Exporter la comptabilité (FEC / Excel / OHADA)</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Rapports &gt; Export comptable</strong>.',
      'Sélectionnez la période et le format : <em>FEC</em> (France 2026), <em>Excel OHADA</em> ou <em>CSV</em>.',
      'Le fichier est téléchargé — importez-le directement dans votre logiciel comptable.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-teal">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Le format FEC (Fichier des Écritures Comptables) est obligatoire en France pour les contrôles fiscaux. FactPro le génère automatiquement selon les spécifications DGFiP 2026.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-teal">3</span></td>
      <td class="section-title">Déclaration de TVA</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Rapports &gt; TVA</strong>.',
      'Sélectionnez la période (mensuelle ou trimestrielle).',
      'FactPro calcule : TVA collectée, TVA déductible, TVA nette à reverser.',
      'Exportez en PDF ou Excel pour votre déclaration fiscale.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-teal">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-teal">4</span></td>
      <td class="section-title">Forecasting et objectifs commerciaux</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Rapports &gt; Forecasting</strong>.',
      'Définissez un objectif de CA mensuel ou annuel.',
      'FactPro affiche votre progression en temps réel et projette la fin de mois selon la tendance.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-teal">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 8 — UTILISATEURS & RÔLES
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-rose">
    <span class="module-icon">👤</span>
    <span class="module-title">Module 8 — Utilisateurs &amp; Rôles</span><br/>
    <span class="module-subtitle">Permissions, invitations et gestion d'équipe</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-rose">1</span></td>
      <td class="section-title">Permissions par rôle</td>
    </tr></table></div>
    <table class="data-table">
      <thead><tr>
        <th>Fonctionnalité</th>
        <th class="td-center">Admin</th>
        <th class="td-center">Comptable</th>
        <th class="td-center">Commercial</th>
        <th class="td-center">Caissier</th>
        <th class="td-center">Lecture</th>
      </tr></thead>
      <tbody>
        <tr><td>Créer / modifier documents</td><td class="td-center check">✓</td><td class="td-center check">✓</td><td class="td-center check">✓</td><td class="td-center dash">—</td><td class="td-center dash">—</td></tr>
        <tr><td>Valider des paiements</td><td class="td-center check">✓</td><td class="td-center check">✓</td><td class="td-center dash">—</td><td class="td-center check">✓</td><td class="td-center dash">—</td></tr>
        <tr><td>Accéder aux rapports</td><td class="td-center check">✓</td><td class="td-center check">✓</td><td class="td-center dash">—</td><td class="td-center dash">—</td><td class="td-center check">✓</td></tr>
        <tr><td>Gérer les produits</td><td class="td-center check">✓</td><td class="td-center dash">—</td><td class="td-center check">✓</td><td class="td-center dash">—</td><td class="td-center dash">—</td></tr>
        <tr><td>Gérer les utilisateurs</td><td class="td-center check">✓</td><td class="td-center dash">—</td><td class="td-center dash">—</td><td class="td-center dash">—</td><td class="td-center dash">—</td></tr>
        <tr><td>Accéder à la caisse POS</td><td class="td-center check">✓</td><td class="td-center dash">—</td><td class="td-center dash">—</td><td class="td-center check">✓</td><td class="td-center dash">—</td></tr>
        <tr><td>Modifier les paramètres</td><td class="td-center check">✓</td><td class="td-center dash">—</td><td class="td-center dash">—</td><td class="td-center dash">—</td><td class="td-center dash">—</td></tr>
      </tbody>
    </table>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-rose">2</span></td>
      <td class="section-title">Inviter un collaborateur</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Équipe &gt; Inviter un membre</strong>.',
      'Saisissez l\'email et sélectionnez le rôle.',
      'Un email d\'invitation avec lien d\'activation (valable 48h) est envoyé automatiquement.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n" style="color:#be185d">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-rose">3</span></td>
      <td class="section-title">Réinitialiser le mot de passe d'un membre</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Équipe</strong>.',
      'Cliquez sur les 3 points (⋯) à côté du membre concerné.',
      'Sélectionnez <em>Réinitialiser le mot de passe</em>.',
      'Un email est envoyé au membre avec un lien de réinitialisation sécurisé.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n" style="color:#be185d">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-rose">4</span></td>
      <td class="section-title">Activer la double authentification (2FA)</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Mon profil &gt; Sécurité &gt; Authentification à 2 facteurs</strong>.',
      'Scannez le QR code avec Google Authenticator ou Authy.',
      'Entrez le code à 6 chiffres pour valider l\'activation.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n" style="color:#be185d">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="warning"><table><tr>
      <td class="warning-label">⚠️ Important</td>
      <td>Sauvegardez les codes de récupération affichés lors de l'activation — ils sont la seule façon de récupérer votre compte si vous perdez accès à votre téléphone.</td>
    </tr></table></div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 9 — PARAMÈTRES
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-indigo">
    <span class="module-icon">⚙️</span>
    <span class="module-title">Module 9 — Paramètres</span><br/>
    <span class="module-subtitle">Numérotation, mentions légales, TVA et intégrations</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-purple">1</span></td>
      <td class="section-title">Configurer la numérotation des documents</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Numérotation</strong>.',
      'Pour chaque type (Facture, Devis, BL…), définissez : préfixe, inclusion de l\'année/mois, longueur de séquence.',
      'Exemples : <code>FACT-2026-0001</code>, <code>BL-0001</code>, <code>DEV-2026-0001</code>.',
      'Enregistrez — la numérotation est indépendante par type de document.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-purple">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Incluez l'année dans le préfixe (FACT-2026-) pour faciliter le classement et la recherche de vos archives fiscales. Chaque type de document a son propre compteur.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-purple">2</span></td>
      <td class="section-title">Mentions légales et pied de page</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Société &gt; Pied de page</strong>.',
      'Saisissez vos mentions légales : RIB, conditions de paiement, pénalités de retard.',
      'Ces mentions apparaissent automatiquement sur tous vos documents PDF.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-purple">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Les mentions OHADA obligatoires (RCCM, NIF, capital social) sont insérées automatiquement si renseignées dans la fiche société.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-purple">3</span></td>
      <td class="section-title">Connecteurs Zapier / Make (webhooks)</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Intégrations &gt; Webhooks</strong>.',
      'Copiez l\'URL de webhook FactPro dans votre scénario Zapier ou Make.',
      'Choisissez les événements déclencheurs : nouvelle facture, paiement reçu, nouveau client…',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-purple">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 10 — ABONNEMENT & LICENCE
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-orange">
    <span class="module-icon">💳</span>
    <span class="module-title">Module 10 — Abonnement &amp; Licence</span><br/>
    <span class="module-subtitle">Forfaits, limites, changement de plan et facturation prorata</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-amber">1</span></td>
      <td class="section-title">Limites par forfait</td>
    </tr></table></div>
    <table class="data-table">
      <thead><tr>
        <th>Fonctionnalité</th>
        <th class="td-center">Starter</th>
        <th class="td-center">Pro</th>
        <th class="td-center">Business</th>
        <th class="td-center">Enterprise</th>
      </tr></thead>
      <tbody>
        <tr><td>Utilisateurs</td><td class="td-center">1</td><td class="td-center">5</td><td class="td-center">20</td><td class="td-center">Illimité</td></tr>
        <tr><td>Documents / mois</td><td class="td-center">50</td><td class="td-center">500</td><td class="td-center">5 000</td><td class="td-center">Illimité</td></tr>
        <tr><td>Styles PDF</td><td class="td-center">5</td><td class="td-center">30</td><td class="td-center">94</td><td class="td-center">94+</td></tr>
        <tr><td>Sociétés</td><td class="td-center">1</td><td class="td-center">3</td><td class="td-center">10</td><td class="td-center">Illimité</td></tr>
        <tr><td>API REST</td><td class="td-center dash">—</td><td class="td-center check">✓</td><td class="td-center check">✓</td><td class="td-center check">✓</td></tr>
        <tr><td>White-label</td><td class="td-center dash">—</td><td class="td-center dash">—</td><td class="td-center check">✓</td><td class="td-center check">✓</td></tr>
      </tbody>
    </table>
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">FactPro vous envoie une notification à 80% d'utilisation mensuelle pour vous laisser le temps de passer au forfait supérieur avant d'atteindre la limite.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-amber">2</span></td>
      <td class="section-title">Changer de forfait</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Paramètres &gt; Abonnement</strong> et cliquez <em>Changer de forfait</em>.',
      'Le nouveau forfait est activé immédiatement — les fonctionnalités supplémentaires sont disponibles instantanément.',
      'La facturation est calculée au prorata du mois en cours.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-amber">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 11 — CATALOGUE DE MODÈLES
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-blue">
    <span class="module-icon">📋</span>
    <span class="module-title">Module 11 — Catalogue de modèles</span><br/>
    <span class="module-subtitle">498 modèles sectoriels, aperçu interactif et création en 1 clic</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-blue">1</span></td>
      <td class="section-title">Qu'est-ce que le catalogue ?</td>
    </tr></table></div>
    <p style="font-size:9pt; color:#374151; line-height:1.6; margin:4px 0 8px 0;">
      FactPro propose <strong>498 modèles de documents</strong> prêts à l'emploi, organisés en <strong>24 catégories sectorielles</strong> : BTP, Transport, Commerce, Juridique, Santé, Agriculture, Restauration, IT, Finance, Immobilier, et bien d'autres. Chaque modèle intègre la structure et les mentions adaptées à son secteur.
    </p>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-blue">2</span></td>
      <td class="section-title">Accéder au catalogue et créer un document</td>
    </tr></table></div>
    @foreach([
      'Allez dans <strong>Documents</strong> et cliquez sur le bouton <em>Nouveau document</em>.',
      'Dans la fenêtre de création, cliquez sur l\'onglet <strong>Catalogue</strong>.',
      'Parcourez les 24 catégories ou utilisez la recherche pour trouver le modèle adapté.',
      'Cliquez sur un modèle pour afficher son <strong>aperçu interactif</strong> avec données fictives représentatives.',
      'L\'aperçu indique le <strong>style visuel PDF</strong> qui sera pré-sélectionné à la création.',
      'Cliquez <em>Créer ce document</em> — FactPro lance la création avec le bon type ET le bon style pré-sélectionné.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-blue">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Le bandeau "Aperçu indicatif" rappelle que les données affichées sont fictives. Le document réel utilisera votre logo, vos informations société et les données de votre client réel.</td>
    </tr></table></div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 12 — STYLES VISUELS PDF
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-purple">
    <span class="module-icon">🎨</span>
    <span class="module-title">Module 12 — Styles visuels PDF</span><br/>
    <span class="module-subtitle">Recommandation automatique, galerie et personnalisation</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-purple">1</span></td>
      <td class="section-title">Style recommandé automatiquement</td>
    </tr></table></div>
    <p style="font-size:9pt; color:#374151; margin-bottom:8px;">Lors de la création d'un document, FactPro recommande automatiquement un style visuel adapté :</p>
    <table class="data-table">
      <thead><tr><th>Type de document</th><th>Style recommandé</th></tr></thead>
      <tbody>
        <tr><td>Bon de livraison</td><td><strong>Template Transport</strong></td></tr>
        <tr><td>Contrat</td><td><strong>Template Juridique</strong></td></tr>
        <tr><td>Bon de commande fournisseur</td><td><strong>Template Corporate</strong></td></tr>
        <tr><td>Facture BTP / chantier</td><td><strong>Template BTP</strong></td></tr>
        <tr><td>Devis / Proforma</td><td><strong>Template selon votre secteur</strong></td></tr>
        <tr><td>Facture standard</td><td><strong>Template Commerce</strong></td></tr>
      </tbody>
    </table>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-purple">2</span></td>
      <td class="section-title">Modifier le style visuel</td>
    </tr></table></div>
    @foreach([
      'Lors de la création, la galerie est <strong>réduite par défaut</strong> — seul le style recommandé est affiché.',
      'Cliquez <em>Modifier</em> pour ouvrir la galerie complète des styles disponibles selon votre forfait.',
      'Cliquez sur un style pour voir un aperçu en temps réel.',
      'Sélectionnez le style souhaité — la galerie se referme automatiquement.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-purple">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Le changement de style n'affecte jamais le contenu du document (lignes, montants, client) — uniquement la mise en page visuelle du PDF. Vous pouvez changer de style à tout moment.</td>
    </tr></table></div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODULE 13 — TYPES DE DOCUMENTS
══════════════════════════════════════════════════ --}}
<div class="module">
  <div class="module-header border-green">
    <span class="module-icon">📑</span>
    <span class="module-title">Module 13 — Types de documents</span><br/>
    <span class="module-subtitle">Workflow recommandé et rôle de chaque document</span>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-green">1</span></td>
      <td class="section-title">Workflow commercial recommandé</td>
    </tr></table></div>
    <p style="font-size:9pt; color:#374151; margin-bottom:10px;">Pour une gestion commerciale complète et tracée, FactPro recommande ce cycle :</p>
    <div class="workflow-bar">
      <table><tr>
        <td><span class="wf-step">Devis</span></td>
        <td><span class="wf-arrow">→</span></td>
        <td><span class="wf-step">Bon de commande</span></td>
        <td><span class="wf-arrow">→</span></td>
        <td><span class="wf-step">Bon de livraison</span></td>
        <td><span class="wf-arrow">→</span></td>
        <td><span class="wf-step">Facture</span></td>
      </tr></table>
    </div>
    <p style="font-size:8.5pt; color:#6b7280; margin-top:8px;">Chaque étape peut être générée en un clic depuis le document précédent. Ce workflow est recommandé mais non obligatoire.</p>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-green">2</span></td>
      <td class="section-title">Bon de livraison — pourquoi sans prix ?</td>
    </tr></table></div>
    <p style="font-size:9pt; color:#374151; line-height:1.6; margin-bottom:6px;">Le Bon de livraison (BL) <strong>n'affiche pas de prix</strong> — c'est un comportement normal et conforme à la norme OHADA. Le BL est un <strong>document de transport et de réception</strong>, pas un document commercial. Il sert à :</p>
    @foreach([
      'Confirmer les quantités et références livrées',
      'Obtenir la signature de réception du client ou du livreur',
      'Servir de preuve de livraison en cas de litige',
    ] as $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-green">&bull;</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <p style="font-size:8.5pt; color:#6b7280; margin-top:6px;">Les prix figurent sur la facture associée, pas sur le bon de livraison.</p>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-green">3</span></td>
      <td class="section-title">Facture d'acompte et facture de solde</td>
    </tr></table></div>
    @foreach([
      'La <strong>Facture d\'acompte</strong> permet de facturer un pourcentage du total <strong>avant la livraison</strong> (ex: 30% à la commande). Elle est liée au devis ou bon de commande d\'origine.',
      'Vous pouvez émettre <strong>plusieurs factures d\'acompte</strong> successives pour le même projet.',
      'La <strong>Facture de solde</strong> clôture le cycle : elle déduit automatiquement tous les acomptes déjà perçus et affiche uniquement le montant restant dû.',
      'Pour créer une facture de solde, ouvrez le devis ou BC et cliquez <em>Générer la facture de solde</em>.',
    ] as $idx => $s)
    <div class="step-row"><table><tr>
      <td class="step-n c-green">{{ $idx+1 }}.</td>
      <td class="step-text">{!! $s !!}</td>
    </tr></table></div>
    @endforeach
    <div class="tip"><table><tr>
      <td class="tip-label">💡 Conseil Pro</td>
      <td class="tip-text">Pour les grands projets BTP ou IT, utilisez la séquence 30% à la commande + 40% à mi-livraison + 30% à la réception. FactPro calcule automatiquement chaque montant.</td>
    </tr></table></div>
  </div>

  <div class="section">
    <div class="section-header"><table><tr>
      <td style="width:26px"><span class="section-num bg-green">4</span></td>
      <td class="section-title">Autres types de documents disponibles</td>
    </tr></table></div>
    <table class="data-table">
      <thead><tr><th>Document</th><th>Usage principal</th></tr></thead>
      <tbody>
        <tr><td><strong>Facture Proforma</strong></td><td>Offre de prix avant acceptation, douane, importation</td></tr>
        <tr><td><strong>Reçu de paiement</strong></td><td>Preuve d'encaissement pour le client</td></tr>
        <tr><td><strong>Quittance de loyer</strong></td><td>Immobilier : preuve de paiement mensuel du loyer</td></tr>
        <tr><td><strong>Avis de loyer</strong></td><td>Immobilier : demande de paiement mensuel au locataire</td></tr>
        <tr><td><strong>Contrat</strong></td><td>Prestation de services, accord commercial signable</td></tr>
        <tr><td><strong>Bon de commande client</strong></td><td>Confirmation de commande signée par le client</td></tr>
        <tr><td><strong>Rapport d'intervention</strong></td><td>Maintenance, dépannage, intervention technique</td></tr>
        <tr><td><strong>Bon de transfert stock</strong></td><td>Mouvement d'articles entre entrepôts ou sites</td></tr>
      </tbody>
    </table>
  </div>
</div>

{{-- ══════════════════════════════════════════════════
     PAGE FINALE
══════════════════════════════════════════════════ --}}
<div class="final-page">
  <span class="final-icon">🤖</span>
  <div class="final-title">Une question ? Parlez à SARA</div>
  <div class="final-body">
    Notre assistante IA répond 24h/24 à toutes vos questions sur FactPro.<br/>
    Accessible depuis n'importe quelle page de l'application.
  </div>
  <div class="final-footer">
    <strong>IBIG Soft</strong> — Éditeur de FactPro<br/>
    support@ibigsoft.com &nbsp;&middot;&nbsp; factpro.ibigsoft.com<br/>
    Guide utilisateur v2.5 &nbsp;&middot;&nbsp; Juillet 2026 &nbsp;&middot;&nbsp; &copy; IBIG Soft. Tous droits réservés.
  </div>
</div>

</body>
</html>
