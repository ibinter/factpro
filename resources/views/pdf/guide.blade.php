<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Guide Utilisateur IBIG FactPro</title>
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9.5pt; color: #1f2937; line-height: 1.55; background: #fff; }

  /* ── COUVERTURE ── */
  .cover { page-break-after: always; min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; background: linear-gradient(160deg, #0062CC 0%, #1a56db 60%, #7c3aed 100%); padding: 60px 40px; text-align: center; }
  .cover-badge { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 50px; padding: 6px 20px; font-size: 9pt; color: rgba(255,255,255,0.9); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 32px; display: inline-block; }
  .cover-title { font-size: 32pt; font-weight: 900; color: #fff; letter-spacing: -0.5px; line-height: 1.15; margin-bottom: 8px; }
  .cover-subtitle { font-size: 14pt; color: rgba(255,255,255,0.85); margin-bottom: 48px; }
  .cover-divider { width: 60px; height: 3px; background: rgba(255,255,255,0.5); margin: 0 auto 48px; border-radius: 2px; }
  .cover-meta { color: rgba(255,255,255,0.7); font-size: 9pt; line-height: 1.8; }
  .cover-meta strong { color: #fff; }
  .cover-modules { margin-top: 48px; display: flex; flex-wrap: wrap; justify-content: center; gap: 8px; }
  .cover-module-chip { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 20px; padding: 4px 12px; font-size: 8pt; color: rgba(255,255,255,0.85); }
  .cover-footer { position: absolute; bottom: 30px; left: 0; right: 0; text-align: center; font-size: 8pt; color: rgba(255,255,255,0.5); }

  /* ── SOMMAIRE ── */
  .toc-page { page-break-after: always; padding: 40px 50px; }
  .toc-header { margin-bottom: 28px; padding-bottom: 14px; border-bottom: 2px solid #0062CC; }
  .toc-header h1 { font-size: 18pt; font-weight: 800; color: #0062CC; }
  .toc-header p { font-size: 9pt; color: #6b7280; margin-top: 3px; }
  .toc-item { display: flex; align-items: center; padding: 7px 0; border-bottom: 1px dotted #e5e7eb; }
  .toc-icon { width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 12pt; margin-right: 10px; flex-shrink: 0; }
  .toc-text { flex: 1; font-size: 10pt; font-weight: 600; color: #1f2937; }
  .toc-desc { font-size: 8pt; color: #6b7280; display: block; font-weight: 400; margin-top: 1px; }
  .toc-num { font-size: 8pt; color: #9ca3af; }

  /* ── MODULES ── */
  .module { page-break-before: always; padding: 36px 50px 40px; }
  .module:first-of-type { page-break-before: auto; }
  .module-header { display: flex; align-items: center; gap: 14px; padding-bottom: 14px; margin-bottom: 24px; }
  .module-header-bar { width: 5px; height: 50px; border-radius: 3px; flex-shrink: 0; }
  .module-icon { font-size: 26pt; line-height: 1; }
  .module-title { font-size: 17pt; font-weight: 800; color: #111827; line-height: 1.2; }
  .module-subtitle { font-size: 9pt; color: #6b7280; margin-top: 2px; }

  /* ── SECTIONS ── */
  .section { margin-bottom: 22px; }
  .section-header { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
  .section-num { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 7.5pt; font-weight: 800; flex-shrink: 0; }
  .section-title { font-size: 11pt; font-weight: 700; color: #1f2937; }
  .section-body { font-size: 9pt; color: #374151; line-height: 1.6; }

  /* ── LISTES ── */
  ol.steps { list-style: none; padding: 0; margin: 0; }
  ol.steps li { display: flex; gap: 7px; padding: 3px 0; font-size: 9pt; color: #374151; line-height: 1.5; }
  ol.steps li .n { font-weight: 700; flex-shrink: 0; min-width: 16px; }
  ul.bullets { list-style: none; padding: 0; margin: 0; }
  ul.bullets li { display: flex; gap: 7px; padding: 2px 0; font-size: 9pt; color: #374151; line-height: 1.5; }
  ul.bullets li .dot { flex-shrink: 0; }

  /* ── CALLOUT CONSEIL PRO ── */
  .tip { background: #fffbeb; border: 1px solid #fbbf24; border-left: 4px solid #f59e0b; border-radius: 6px; padding: 8px 12px; margin-top: 10px; margin-bottom: 4px; font-size: 8.5pt; color: #92400e; display: flex; gap: 8px; }
  .tip-label { font-weight: 700; flex-shrink: 0; }

  /* ── CALLOUT ATTENTION ── */
  .warning { background: #fef2f2; border: 1px solid #fca5a5; border-left: 4px solid #ef4444; border-radius: 6px; padding: 8px 12px; margin-top: 10px; font-size: 8.5pt; color: #7f1d1d; display: flex; gap: 8px; }
  .warning-label { font-weight: 700; flex-shrink: 0; }

  /* ── TABLEAU ── */
  table.data { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-top: 8px; }
  table.data th { background: #f3f4f6; color: #374151; font-weight: 700; text-align: left; padding: 6px 10px; border: 1px solid #e5e7eb; }
  table.data td { padding: 5px 10px; border: 1px solid #e5e7eb; color: #374151; }
  table.data tr:nth-child(even) td { background: #f9fafb; }
  .check { color: #059669; font-weight: 700; }
  .dash  { color: #9ca3af; }

  /* ── WORKFLOW BADGE ── */
  .workflow { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; margin: 8px 0; font-size: 8.5pt; }
  .wf-step { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 5px; padding: 3px 9px; font-weight: 600; color: #1d4ed8; }
  .wf-arrow { color: #9ca3af; font-weight: 700; }

  /* ── FOOTER / HEADER ── */
  @page { margin: 15mm 15mm 20mm 15mm; }

  /* ── COULEURS PAR MODULE ── */
  .c-blue   { background: #0062CC; }
  .c-purple { background: #7c3aed; }
  .c-red    { background: #dc2626; }
  .c-cyan   { background: #0891b2; }
  .c-amber  { background: #d97706; }
  .c-green  { background: #059669; }
  .c-teal   { background: #0d9488; }
  .c-rose   { background: #be185d; }
  .c-indigo { background: #4338ca; }
  .c-orange { background: #ea580c; }
  .c-violet { background: #6d28d9; }
  .c-emeral { background: #10b981; }
  .c-sky    { background: #0284c7; }

  .n-blue   { color: #0062CC; }
  .n-purple { color: #7c3aed; }
  .n-red    { color: #dc2626; }
  .n-cyan   { color: #0891b2; }
  .n-amber  { color: #d97706; }
  .n-green  { color: #059669; }
  .n-teal   { color: #0d9488; }
  .n-rose   { color: #be185d; }
  .n-indigo { color: #4338ca; }
  .n-orange { color: #ea580c; }

  .bg-blue   { background: #dbeafe; color: #1e40af; }
  .bg-purple { background: #ede9fe; color: #5b21b6; }
  .bg-red    { background: #fee2e2; color: #991b1b; }
  .bg-cyan   { background: #cffafe; color: #164e63; }
  .bg-amber  { background: #fef3c7; color: #78350f; }
  .bg-green  { background: #dcfce7; color: #14532d; }

  code { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 3px; padding: 1px 4px; font-size: 8pt; font-family: monospace; color: #1f2937; }
  strong { font-weight: 700; }
  em { font-style: italic; }

  .page-num { text-align: center; font-size: 8pt; color: #9ca3af; margin-top: 20px; }
  .separator { border: none; border-top: 1px solid #e5e7eb; margin: 16px 0; }
</style>
</head>
<body>

<!-- ═══════════════════════════════════════════════════════
     PAGE DE COUVERTURE
════════════════════════════════════════════════════════════ -->
<div class="cover">
  <div class="cover-badge">Documentation officielle</div>
  <div class="cover-title">IBIG FactPro</div>
  <div class="cover-subtitle">Guide utilisateur complet</div>
  <div class="cover-divider"></div>
  <div class="cover-meta">
    <strong>Version 2.5</strong> &nbsp;·&nbsp; Juillet 2026<br>
    13 modules &nbsp;·&nbsp; Procédures étape par étape<br>
    498 modèles de documents &nbsp;·&nbsp; Conforme OHADA
  </div>
  <div class="cover-modules">
    <span class="cover-module-chip">Démarrage</span>
    <span class="cover-module-chip">Facturation</span>
    <span class="cover-module-chip">Clients</span>
    <span class="cover-module-chip">Stock</span>
    <span class="cover-module-chip">Paiements</span>
    <span class="cover-module-chip">Caisse POS</span>
    <span class="cover-module-chip">Rapports</span>
    <span class="cover-module-chip">Équipe & Rôles</span>
    <span class="cover-module-chip">Paramètres</span>
    <span class="cover-module-chip">Abonnement</span>
    <span class="cover-module-chip">Catalogue</span>
    <span class="cover-module-chip">Styles PDF</span>
    <span class="cover-module-chip">Types de documents</span>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     SOMMAIRE
════════════════════════════════════════════════════════════ -->
<div class="toc-page">
  <div class="toc-header">
    <h1>Table des matières</h1>
    <p>13 modules · Guide complet · IBIG FactPro v2.5</p>
  </div>

  @php
  $modules = [
    ['icon'=>'🚀','color'=>'#0062CC','bg'=>'#dbeafe','title'=>'Module 1 — Démarrage','desc'=>'Première configuration, logo, collaborateurs, modes de paiement'],
    ['icon'=>'📄','color'=>'#7c3aed','bg'=>'#ede9fe','title'=>'Module 2 — Facturation','desc'=>'Devis, factures, avoirs, récurrences et envoi automatique'],
    ['icon'=>'👥','color'=>'#dc2626','bg'=>'#fee2e2','title'=>'Module 3 — Clients','desc'=>'Gestion, imports CSV, historique, relances et pipeline CRM'],
    ['icon'=>'📦','color'=>'#0891b2','bg'=>'#cffafe','title'=>'Module 4 — Produits & Stock','desc'=>'Catalogue, alertes stock, codes-barres, inventaire et imports'],
    ['icon'=>'💰','color'=>'#d97706','bg'=>'#fef3c7','title'=>'Module 5 — Paiements','desc'=>'Encaissements, Mobile Money, soldes, reçus et rapprochement'],
    ['icon'=>'🖥️','color'=>'#059669','bg'=>'#dcfce7','title'=>'Module 6 — Caisse POS','desc'=>'Point de vente, sessions, rapport de clôture et multi-caissier'],
    ['icon'=>'📊','color'=>'#0d9488','bg'=>'#ccfbf1','title'=>'Module 7 — Rapports','desc'=>'Analytics, exports FEC/Excel, TVA et forecasting CA'],
    ['icon'=>'👤','color'=>'#be185d','bg'=>'#fce7f3','title'=>'Module 8 — Utilisateurs & Rôles','desc'=>'Permissions, invitations, réinitialisation et audit'],
    ['icon'=>'⚙️','color'=>'#4338ca','bg'=>'#e0e7ff','title'=>'Module 9 — Paramètres','desc'=>'Numérotation, mentions légales, TVA, intégrations et RGPD'],
    ['icon'=>'💳','color'=>'#ea580c','bg'=>'#ffedd5','title'=>'Module 10 — Abonnement & Licence','desc'=>'Forfaits, limites, changement de plan et facturation prorata'],
    ['icon'=>'📋','color'=>'#0062CC','bg'=>'#dbeafe','title'=>'Module 11 — Catalogue de modèles','desc'=>'498 modèles sectoriels, aperçu interactif et création en 1 clic'],
    ['icon'=>'🎨','color'=>'#7c3aed','bg'=>'#ede9fe','title'=>'Module 12 — Styles visuels PDF','desc'=>'Recommandation auto, galerie et personnalisation des templates'],
    ['icon'=>'📑','color'=>'#059669','bg'=>'#dcfce7','title'=>'Module 13 — Types de documents','desc'=>'Workflow Devis→Facture, BL sans prix, acomptes et solde'],
  ];
  @endphp

  @foreach($modules as $i => $m)
  <div class="toc-item">
    <div class="toc-icon" style="background:{{ $m['bg'] }}">{{ $m['icon'] }}</div>
    <div class="toc-text">
      {{ $m['title'] }}
      <span class="toc-desc">{{ $m['desc'] }}</span>
    </div>
    <div class="toc-num">Module {{ $i+1 }}</div>
  </div>
  @endforeach
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 1 — DÉMARRAGE
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-blue"></div>
    <div class="module-icon">🚀</div>
    <div>
      <div class="module-title">Module 1 — Démarrage</div>
      <div class="module-subtitle">Première configuration de votre compte</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-blue">1</div>
      <div class="section-title">Créer son compte et configurer la société</div>
    </div>
    <ol class="steps">
      <li><span class="n n-blue">1.</span><span>Rendez-vous sur <strong>factpro.ibigsoft.com</strong> et cliquez sur <em>Essai gratuit</em>.</span></li>
      <li><span class="n n-blue">2.</span><span>Renseignez votre adresse email et choisissez un mot de passe sécurisé (8 car. min, 1 majuscule, 1 chiffre).</span></li>
      <li><span class="n n-blue">3.</span><span>Cliquez sur <em>Créer mon compte</em> — aucune carte bancaire requise.</span></li>
      <li><span class="n n-blue">4.</span><span>Allez dans <strong>Paramètres &gt; Société</strong>.</span></li>
      <li><span class="n n-blue">5.</span><span>Renseignez : nom commercial, adresse complète, numéro RCCM, numéro d'impôt (NIF/NCC).</span></li>
      <li><span class="n n-blue">6.</span><span>Uploadez votre logo (PNG ou SVG recommandé, fond transparent).</span></li>
      <li><span class="n n-blue">7.</span><span>Enregistrez — le logo et les informations apparaissent désormais sur tous vos documents.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Utilisez un logo en PNG fond transparent (800×200 px minimum) pour un rendu net sur tous les templates PDF, y compris les modèles sombres.</span></div>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-blue">2</div>
      <div class="section-title">Inviter les premiers collaborateurs</div>
    </div>
    <ol class="steps">
      <li><span class="n n-blue">1.</span><span>Allez dans <strong>Paramètres &gt; Équipe</strong> et cliquez <em>Inviter un membre</em>.</span></li>
      <li><span class="n n-blue">2.</span><span>Saisissez l'adresse email du collaborateur.</span></li>
      <li><span class="n n-blue">3.</span><span>Sélectionnez son rôle : <em>Admin, Comptable, Commercial, Caissier</em> ou <em>Lecture seule</em>.</span></li>
      <li><span class="n n-blue">4.</span><span>Cliquez <em>Envoyer l'invitation</em> — un email est envoyé avec un lien d'activation valable 48h.</span></li>
      <li><span class="n n-blue">5.</span><span>Le collaborateur accepte et crée son mot de passe.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-blue">3</div>
      <div class="section-title">Paramétrer les modes de paiement acceptés</div>
    </div>
    <ol class="steps">
      <li><span class="n n-blue">1.</span><span>Allez dans <strong>Paramètres &gt; Paiements</strong>.</span></li>
      <li><span class="n n-blue">2.</span><span>Activez chaque mode : Orange Money, Wave, MTN MoMo, Moov Money, Espèces, Virement, Carte.</span></li>
      <li><span class="n n-blue">3.</span><span>Pour chaque Mobile Money, renseignez le numéro du compte et le nom du titulaire.</span></li>
      <li><span class="n n-blue">4.</span><span>Ces modes apparaissent sur les liens de paiement envoyés aux clients.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-blue">4</div>
      <div class="section-title">Personnaliser les modèles de documents</div>
    </div>
    <ol class="steps">
      <li><span class="n n-blue">1.</span><span>Allez dans <strong>Paramètres &gt; Templates</strong>.</span></li>
      <li><span class="n n-blue">2.</span><span>Choisissez parmi les modèles disponibles selon votre forfait.</span></li>
      <li><span class="n n-blue">3.</span><span>Définissez la couleur primaire (en-tête) et la couleur secondaire (accents).</span></li>
      <li><span class="n n-blue">4.</span><span>Activez ou désactivez : signature, QR code anti-falsification et mentions légales.</span></li>
      <li><span class="n n-blue">5.</span><span>Prévisualisez le rendu PDF avant de sauvegarder.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Le QR code anti-falsification intégré sur chaque document permet à votre client de vérifier l'authenticité du document en le scannant — activez-le pour renforcer la confiance.</span></div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 2 — FACTURATION
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-purple"></div>
    <div class="module-icon">📄</div>
    <div>
      <div class="module-title">Module 2 — Facturation</div>
      <div class="module-subtitle">Devis, factures, avoirs et récurrences</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-purple">1</div>
      <div class="section-title">Créer un devis</div>
    </div>
    <ol class="steps">
      <li><span class="n n-purple">1.</span><span>Allez dans <strong>Documents &gt; Nouveau devis</strong>.</span></li>
      <li><span class="n n-purple">2.</span><span>Dans le champ <em>Client</em>, tapez le nom pour le sélectionner ou créez-le à la volée.</span></li>
      <li><span class="n n-purple">3.</span><span>Cliquez <em>Ajouter une ligne</em>, cherchez le produit/service, ajustez quantité et prix unitaire.</span></li>
      <li><span class="n n-purple">4.</span><span>Appliquez une remise globale (%) ou par ligne si nécessaire.</span></li>
      <li><span class="n n-purple">5.</span><span>Vérifiez le total HT, la TVA et le total TTC.</span></li>
      <li><span class="n n-purple">6.</span><span>Cliquez <em>Enregistrer</em> (brouillon) ou <em>Finaliser</em> pour le verrouiller.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-purple">2</div>
      <div class="section-title">Convertir un devis en facture en 1 clic</div>
    </div>
    <ol class="steps">
      <li><span class="n n-purple">1.</span><span>Ouvrez le devis accepté par le client.</span></li>
      <li><span class="n n-purple">2.</span><span>Cliquez le bouton <em>Convertir en facture</em>.</span></li>
      <li><span class="n n-purple">3.</span><span>Vérifiez les informations reprises automatiquement (lignes, remises, conditions).</span></li>
      <li><span class="n n-purple">4.</span><span>Cliquez <em>Finaliser la facture</em> — un numéro est attribué automatiquement.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Depuis un devis accepté, vous pouvez aussi générer directement un Bon de commande ou un Bon de livraison en un clic depuis le menu <em>Actions</em>.</span></div>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-purple">3</div>
      <div class="section-title">Envoyer par email avec PDF en pièce jointe</div>
    </div>
    <ol class="steps">
      <li><span class="n n-purple">1.</span><span>Sur la page de la facture, cliquez <em>Envoyer par email</em>.</span></li>
      <li><span class="n n-purple">2.</span><span>L'adresse du client et l'objet sont pré-remplis. Le PDF est joint automatiquement.</span></li>
      <li><span class="n n-purple">3.</span><span>Personnalisez le message si besoin, puis cliquez <em>Envoyer</em>.</span></li>
      <li><span class="n n-purple">4.</span><span>Le suivi de lecture est activé — vous serez notifié quand le client ouvre l'email.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-purple">4</div>
      <div class="section-title">Créer un avoir (annulation partielle ou totale)</div>
    </div>
    <ol class="steps">
      <li><span class="n n-purple">1.</span><span>Ouvrez la facture à annuler.</span></li>
      <li><span class="n n-purple">2.</span><span>Cliquez <em>Créer un avoir</em> dans le menu Actions.</span></li>
      <li><span class="n n-purple">3.</span><span>Pour un avoir total, conservez toutes les lignes. Pour un avoir partiel, ajustez les quantités.</span></li>
      <li><span class="n n-purple">4.</span><span>Finalisez l'avoir — il est lié à la facture d'origine et réduit le solde dû.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-purple">5</div>
      <div class="section-title">Factures récurrentes</div>
    </div>
    <ol class="steps">
      <li><span class="n n-purple">1.</span><span>Allez dans <strong>Documents &gt; Récurrences &gt; Nouvelle récurrence</strong>.</span></li>
      <li><span class="n n-purple">2.</span><span>Créez le modèle de facture (client, lignes, montants).</span></li>
      <li><span class="n n-purple">3.</span><span>Définissez la fréquence : hebdomadaire, mensuelle, trimestrielle ou annuelle.</span></li>
      <li><span class="n n-purple">4.</span><span>Indiquez la date de début et le nombre d'occurrences (ou <em>Sans limite</em>).</span></li>
      <li><span class="n n-purple">5.</span><span>Activez la récurrence — les factures seront générées et envoyées automatiquement.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Idéal pour les abonnements SaaS, loyers mensuels ou contrats de maintenance. Activez l'envoi automatique par email pour ne plus avoir à y penser.</span></div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 3 — CLIENTS
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-red"></div>
    <div class="module-icon">👥</div>
    <div>
      <div class="module-title">Module 3 — Clients</div>
      <div class="module-subtitle">Gestion des clients, imports et relances</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-red">1</div>
      <div class="section-title">Ajouter un client</div>
    </div>
    <ol class="steps">
      <li><span class="n n-red">1.</span><span>Allez dans <strong>Clients &gt; Nouveau client</strong>.</span></li>
      <li><span class="n n-red">2.</span><span>Renseignez : nom, email, téléphone, adresse, pays et type (Particulier / Entreprise).</span></li>
      <li><span class="n n-red">3.</span><span>Pour les entreprises, renseignez les champs OHADA : <strong>RCCM</strong> et <strong>NIF</strong> (obligatoires sur les factures légales).</span></li>
      <li><span class="n n-red">4.</span><span>Enregistrez — le client est immédiatement disponible pour vos documents.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-red">2</div>
      <div class="section-title">Importer depuis un fichier CSV/Excel</div>
    </div>
    <ol class="steps">
      <li><span class="n n-red">1.</span><span>Allez dans <strong>Paramètres &gt; Import &gt; Clients</strong>.</span></li>
      <li><span class="n n-red">2.</span><span>Téléchargez le modèle CSV fourni par FactPro.</span></li>
      <li><span class="n n-red">3.</span><span>Remplissez les colonnes obligatoires : nom, email, pays. Les autres colonnes sont facultatives.</span></li>
      <li><span class="n n-red">4.</span><span>Importez le fichier — les doublons sont détectés par email.</span></li>
      <li><span class="n n-red">5.</span><span>Un rapport d'import indique les lignes créées, mises à jour et les erreurs.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Vous pouvez importer jusqu'à 5 000 clients en une seule opération. Pour les gros volumes, découpez en fichiers de 1 000 lignes pour un traitement plus rapide.</span></div>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-red">3</div>
      <div class="section-title">Consulter l'historique complet d'un client</div>
    </div>
    <ol class="steps">
      <li><span class="n n-red">1.</span><span>Cliquez sur le nom du client dans la liste.</span></li>
      <li><span class="n n-red">2.</span><span>Onglet <em>Documents</em> : tous les devis, factures et avoirs liés.</span></li>
      <li><span class="n n-red">3.</span><span>Onglet <em>Paiements</em> : historique de tous les encaissements.</span></li>
      <li><span class="n n-red">4.</span><span>Onglet <em>Statistiques</em> : CA total, panier moyen, taux de paiement.</span></li>
      <li><span class="n n-red">5.</span><span>Le <strong>solde dû</strong> est affiché en rouge si des factures sont impayées.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-red">4</div>
      <div class="section-title">Configurer les relances automatiques</div>
    </div>
    <ol class="steps">
      <li><span class="n n-red">1.</span><span>Allez dans <strong>Paramètres &gt; Relances</strong>.</span></li>
      <li><span class="n n-red">2.</span><span>Créez une séquence : ex. J+30, J+60, J+90 après l'échéance.</span></li>
      <li><span class="n n-red">3.</span><span>Rédigez ou personnalisez le message de relance pour chaque étape.</span></li>
      <li><span class="n n-red">4.</span><span>Activez le canal : email, WhatsApp, ou les deux.</span></li>
      <li><span class="n n-red">5.</span><span>FactPro envoie les relances automatiquement aux clients avec des factures échues.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>La séquence "3 relances" (J+15, J+30, J+45 après échéance) avec escalade de ton (cordial → ferme → mise en demeure) augmente significativement le taux de recouvrement.</span></div>
  </div>

  <div class="section">
    <div class="section-header">
      <div class="section-num bg-red">5</div>
      <div class="section-title">Pipeline commercial (CRM)</div>
    </div>
    <ol class="steps">
      <li><span class="n n-red">1.</span><span>Allez dans <strong>Commercial &gt; Pipeline</strong>.</span></li>
      <li><span class="n n-red">2.</span><span>Cliquez <em>Nouvelle opportunité</em> et liez-la à un client existant ou nouveau.</span></li>
      <li><span class="n n-red">3.</span><span>Définissez : montant estimé, date de closing, probabilité de succès.</span></li>
      <li><span class="n n-red">4.</span><span>Glissez la carte entre les colonnes (Prospection → Qualification → Proposition → Gagné/Perdu).</span></li>
      <li><span class="n n-red">5.</span><span>Depuis une opportunité Gagnée, convertissez directement en devis ou facture.</span></li>
    </ol>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 4 — PRODUITS & STOCK
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-cyan"></div>
    <div class="module-icon">📦</div>
    <div>
      <div class="module-title">Module 4 — Produits &amp; Stock</div>
      <div class="module-subtitle">Catalogue, alertes, codes-barres et inventaire</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-cyan">1</div><div class="section-title">Créer un produit ou service</div></div>
    <ol class="steps">
      <li><span class="n n-cyan">1.</span><span>Allez dans <strong>Produits &gt; Nouveau produit</strong>.</span></li>
      <li><span class="n n-cyan">2.</span><span>Renseignez : code SKU, désignation, catégorie, unité de mesure.</span></li>
      <li><span class="n n-cyan">3.</span><span>Entrez le <strong>prix HT</strong> et sélectionnez le taux de TVA applicable.</span></li>
      <li><span class="n n-cyan">4.</span><span>Pour un produit physique, activez <em>Suivi de stock</em> et entrez la quantité initiale.</span></li>
      <li><span class="n n-cyan">5.</span><span>Enregistrez.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-cyan">2</div><div class="section-title">Alertes de stock faible</div></div>
    <ol class="steps">
      <li><span class="n n-cyan">1.</span><span>Dans la fiche produit, renseignez le champ <strong>Seuil d'alerte</strong> (ex : 5 unités).</span></li>
      <li><span class="n n-cyan">2.</span><span>Quand le stock descend sous ce seuil, une alerte apparaît sur le tableau de bord.</span></li>
      <li><span class="n n-cyan">3.</span><span>Vous recevez également un email de notification automatique.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-cyan">3</div><div class="section-title">Importer un catalogue depuis CSV</div></div>
    <ol class="steps">
      <li><span class="n n-cyan">1.</span><span>Allez dans <strong>Produits &gt; Importer</strong>.</span></li>
      <li><span class="n n-cyan">2.</span><span>Téléchargez le modèle CSV (colonnes : SKU, nom, catégorie, prix HT, TVA, stock).</span></li>
      <li><span class="n n-cyan">3.</span><span>Remplissez et uploadez. Les produits existants (même SKU) sont mis à jour.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Utilisez le code SKU comme identifiant unique. Avec un scanner de code-barres, vous pouvez scanner les produits lors d'une vente POS ou d'un inventaire sans saisir manuellement.</span></div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-cyan">4</div><div class="section-title">Générer des étiquettes codes-barres</div></div>
    <ol class="steps">
      <li><span class="n n-cyan">1.</span><span>Dans la liste produits, cochez les articles concernés.</span></li>
      <li><span class="n n-cyan">2.</span><span>Cliquez <em>Actions &gt; Générer étiquettes</em>.</span></li>
      <li><span class="n n-cyan">3.</span><span>Choisissez le format d'étiquette (A4, 3 colonnes…) et cliquez <em>Imprimer</em>.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-cyan">5</div><div class="section-title">Réaliser un inventaire</div></div>
    <ol class="steps">
      <li><span class="n n-cyan">1.</span><span>Allez dans <strong>Stocks &gt; Inventaire &gt; Nouvel inventaire</strong>.</span></li>
      <li><span class="n n-cyan">2.</span><span>Scannez ou saisissez les quantités réelles pour chaque article.</span></li>
      <li><span class="n n-cyan">3.</span><span>FactPro calcule les écarts (théorique vs réel).</span></li>
      <li><span class="n n-cyan">4.</span><span>Validez l'inventaire pour appliquer les ajustements de stock.</span></li>
    </ol>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 5 — PAIEMENTS
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-amber"></div>
    <div class="module-icon">💰</div>
    <div>
      <div class="module-title">Module 5 — Paiements</div>
      <div class="module-subtitle">Encaissements, Mobile Money et rapprochement</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-amber">1</div><div class="section-title">Enregistrer un paiement</div></div>
    <ol class="steps">
      <li><span class="n n-amber">1.</span><span>Ouvrez la facture concernée.</span></li>
      <li><span class="n n-amber">2.</span><span>Cliquez <em>Enregistrer un paiement</em>.</span></li>
      <li><span class="n n-amber">3.</span><span>Indiquez le montant, le mode (espèces, virement, Mobile Money), la date et une référence.</span></li>
      <li><span class="n n-amber">4.</span><span>Validez — la facture passe au statut <em>Payée</em> ou <em>Partiellement payée</em>.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-amber">2</div><div class="section-title">Configurer Orange Money / Wave / MTN MoMo</div></div>
    <ol class="steps">
      <li><span class="n n-amber">1.</span><span>Allez dans <strong>Paramètres &gt; Paiements</strong>.</span></li>
      <li><span class="n n-amber">2.</span><span>Activez chaque opérateur souhaité.</span></li>
      <li><span class="n n-amber">3.</span><span>Renseignez le numéro de compte et le nom du titulaire.</span></li>
      <li><span class="n n-amber">4.</span><span>Ces informations s'affichent sur les liens de paiement envoyés aux clients.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Activez la passerelle CinetPay ou FedaPay pour permettre à vos clients de payer directement en ligne via un lien sécurisé, sans intervention de votre part.</span></div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-amber">3</div><div class="section-title">Générer un reçu de paiement</div></div>
    <ol class="steps">
      <li><span class="n n-amber">1.</span><span>Après validation d'un paiement, cliquez <em>Voir le reçu</em>.</span></li>
      <li><span class="n n-amber">2.</span><span>Le reçu PDF est généré avec les détails du paiement.</span></li>
      <li><span class="n n-amber">3.</span><span>Cliquez <em>Envoyer au client</em> par email ou WhatsApp.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-amber">4</div><div class="section-title">Rapprochement bancaire mensuel</div></div>
    <ol class="steps">
      <li><span class="n n-amber">1.</span><span>Allez dans <strong>Rapports &gt; Rapprochement bancaire</strong>.</span></li>
      <li><span class="n n-amber">2.</span><span>Importez votre relevé bancaire (CSV depuis votre banque).</span></li>
      <li><span class="n n-amber">3.</span><span>FactPro rapproche automatiquement les transactions avec les paiements enregistrés.</span></li>
      <li><span class="n n-amber">4.</span><span>Traitez les écarts manuellement et validez le rapprochement.</span></li>
    </ol>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 6 — CAISSE POS
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-green"></div>
    <div class="module-icon">🖥️</div>
    <div>
      <div class="module-title">Module 6 — Caisse POS</div>
      <div class="module-subtitle">Point de vente, sessions, clôture et multi-caissier</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-green">1</div><div class="section-title">Ouvrir une session de caisse</div></div>
    <ol class="steps">
      <li><span class="n n-green">1.</span><span>Allez dans <strong>Caisse POS &gt; Ouvrir la session</strong>.</span></li>
      <li><span class="n n-green">2.</span><span>Saisissez le fonds de caisse de départ (montant en espèces en caisse).</span></li>
      <li><span class="n n-green">3.</span><span>Cliquez <em>Démarrer la session</em> — l'interface de vente s'ouvre.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Définissez un fonds de caisse de départ réaliste — il sera déduit lors du calcul de l'écart en clôture. Un fonds de caisse constant (ex: 50 000 FCFA) facilite la comptabilité journalière.</span></div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-green">2</div><div class="section-title">Encaisser une vente</div></div>
    <ol class="steps">
      <li><span class="n n-green">1.</span><span>Scannez ou cherchez les produits dans la barre de recherche.</span></li>
      <li><span class="n n-green">2.</span><span>Ajustez les quantités en cliquant sur + / −.</span></li>
      <li><span class="n n-green">3.</span><span>Sélectionnez le mode de paiement (Espèces, Mobile Money, Carte…).</span></li>
      <li><span class="n n-green">4.</span><span>Entrez le montant reçu — la monnaie à rendre est calculée automatiquement.</span></li>
      <li><span class="n n-green">5.</span><span>Cliquez <em>Valider la vente</em> — un ticket est généré et imprimé automatiquement.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-green">3</div><div class="section-title">Clôture de caisse et Rapport X</div></div>
    <ol class="steps">
      <li><span class="n n-green">1.</span><span>En fin de journée, allez dans <strong>Caisse &gt; Session en cours &gt; Clôturer</strong>.</span></li>
      <li><span class="n n-green">2.</span><span>Comptez les espèces et saisissez le montant réel en caisse.</span></li>
      <li><span class="n n-green">3.</span><span>Le <strong>Rapport X</strong> est généré : total des ventes, ventilation par mode de paiement, écart caisse.</span></li>
      <li><span class="n n-green">4.</span><span>Imprimez ou exportez le rapport pour la comptabilité.</span></li>
      <li><span class="n n-green">5.</span><span>Confirmez la clôture — la session est archivée.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>En mode restauration, activez les <em>Tables</em> dans <strong>Paramètres &gt; POS</strong> pour gérer les commandes par table et fusionner plusieurs additions — idéal pour les restaurants avec service à table.</span></div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-green">4</div><div class="section-title">Impression thermique (58mm / 80mm)</div></div>
    <ol class="steps">
      <li><span class="n n-green">1.</span><span>Allez dans <strong>Paramètres &gt; Caisse &gt; Imprimante</strong>.</span></li>
      <li><span class="n n-green">2.</span><span>Choisissez le format : 58mm (petites imprimantes) ou 80mm (standard).</span></li>
      <li><span class="n n-green">3.</span><span>Activez l'impression automatique après chaque vente.</span></li>
    </ol>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 7 — RAPPORTS
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-teal"></div>
    <div class="module-icon">📊</div>
    <div>
      <div class="module-title">Module 7 — Rapports</div>
      <div class="module-subtitle">Analytics, exports comptables, TVA et forecasting</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num" style="background:#ccfbf1;color:#0d9488;">1</div><div class="section-title">Dashboard KPIs</div></div>
    <ul class="bullets">
      <li><span class="dot" style="color:#0d9488;">•</span><span><strong>CA du mois</strong> vs mois précédent avec variation %</span></li>
      <li><span class="dot" style="color:#0d9488;">•</span><span><strong>Factures en attente</strong> : nombre et montant total à encaisser</span></li>
      <li><span class="dot" style="color:#0d9488;">•</span><span><strong>Top produits</strong> : les 5 articles les plus vendus du mois</span></li>
      <li><span class="dot" style="color:#0d9488;">•</span><span><strong>Top clients</strong> : les meilleurs clients par CA</span></li>
      <li><span class="dot" style="color:#0d9488;">•</span><span><strong>Courbe CA</strong> : évolution sur 12 mois</span></li>
    </ul>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num" style="background:#ccfbf1;color:#0d9488;">2</div><div class="section-title">Exporter la comptabilité (FEC / Excel)</div></div>
    <ol class="steps">
      <li><span class="n" style="color:#0d9488;">1.</span><span>Allez dans <strong>Rapports &gt; Export comptable</strong>.</span></li>
      <li><span class="n" style="color:#0d9488;">2.</span><span>Sélectionnez la période et le format : <em>FEC</em> (France 2026), <em>Excel OHADA</em> ou <em>CSV</em>.</span></li>
      <li><span class="n" style="color:#0d9488;">3.</span><span>Le fichier est téléchargé — importez-le directement dans votre logiciel comptable (Sage, Cegid…).</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Le format FEC (Fichier des Écritures Comptables) est obligatoire en France pour les contrôles fiscaux. FactPro le génère automatiquement conformément aux spécifications DGFiP.</span></div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num" style="background:#ccfbf1;color:#0d9488;">3</div><div class="section-title">Déclaration de TVA</div></div>
    <ol class="steps">
      <li><span class="n" style="color:#0d9488;">1.</span><span>Allez dans <strong>Rapports &gt; TVA</strong>.</span></li>
      <li><span class="n" style="color:#0d9488;">2.</span><span>Sélectionnez la période (mensuelle ou trimestrielle).</span></li>
      <li><span class="n" style="color:#0d9488;">3.</span><span>FactPro calcule : TVA collectée, TVA déductible, TVA à reverser.</span></li>
      <li><span class="n" style="color:#0d9488;">4.</span><span>Exportez en PDF ou Excel pour votre déclaration fiscale.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num" style="background:#ccfbf1;color:#0d9488;">4</div><div class="section-title">Forecasting et objectifs commerciaux</div></div>
    <ol class="steps">
      <li><span class="n" style="color:#0d9488;">1.</span><span>Allez dans <strong>Rapports &gt; Forecasting</strong>.</span></li>
      <li><span class="n" style="color:#0d9488;">2.</span><span>Définissez un objectif de CA mensuel ou annuel.</span></li>
      <li><span class="n" style="color:#0d9488;">3.</span><span>FactPro affiche votre progression en temps réel et projette la fin de mois selon la tendance.</span></li>
    </ol>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 8 — UTILISATEURS & RÔLES
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-rose"></div>
    <div class="module-icon">👤</div>
    <div>
      <div class="module-title">Module 8 — Utilisateurs &amp; Rôles</div>
      <div class="module-subtitle">Permissions, invitations et gestion d'équipe</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-red">1</div><div class="section-title">Permissions par rôle</div></div>
    <table class="data">
      <thead>
        <tr>
          <th>Fonctionnalité</th>
          <th style="text-align:center">Admin</th>
          <th style="text-align:center">Comptable</th>
          <th style="text-align:center">Commercial</th>
          <th style="text-align:center">Caissier</th>
          <th style="text-align:center">Lecture</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>Créer / modifier des documents</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="dash">—</td></tr>
        <tr><td>Valider des paiements</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="dash">—</td></tr>
        <tr><td>Accéder aux rapports</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="check">✓</td></tr>
        <tr><td>Gérer les produits</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="dash">—</td></tr>
        <tr><td>Gérer les utilisateurs</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="dash">—</td></tr>
        <tr><td>Accéder à la caisse POS</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="dash">—</td></tr>
        <tr><td>Modifier les paramètres</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="dash">—</td></tr>
      </tbody>
    </table>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-red">2</div><div class="section-title">Inviter un collaborateur</div></div>
    <ol class="steps">
      <li><span class="n n-red">1.</span><span>Allez dans <strong>Paramètres &gt; Équipe &gt; Inviter un membre</strong>.</span></li>
      <li><span class="n n-red">2.</span><span>Saisissez l'email et sélectionnez le rôle.</span></li>
      <li><span class="n n-red">3.</span><span>Un email d'invitation avec lien d'activation (valable 48h) est envoyé.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-red">3</div><div class="section-title">Réinitialiser le mot de passe d'un membre</div></div>
    <ol class="steps">
      <li><span class="n n-red">1.</span><span>Allez dans <strong>Paramètres &gt; Équipe</strong>.</span></li>
      <li><span class="n n-red">2.</span><span>Cliquez sur les 3 points (⋯) à côté du membre.</span></li>
      <li><span class="n n-red">3.</span><span>Sélectionnez <em>Réinitialiser le mot de passe</em>.</span></li>
      <li><span class="n n-red">4.</span><span>Un email est envoyé automatiquement au membre avec un lien de réinitialisation.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-red">4</div><div class="section-title">Activer la double authentification (2FA)</div></div>
    <ol class="steps">
      <li><span class="n n-red">1.</span><span>Allez dans <strong>Mon profil &gt; Sécurité &gt; Authentification à 2 facteurs</strong>.</span></li>
      <li><span class="n n-red">2.</span><span>Scannez le QR code avec Google Authenticator ou Authy.</span></li>
      <li><span class="n n-red">3.</span><span>Entrez le code à 6 chiffres pour valider l'activation.</span></li>
    </ol>
    <div class="warning"><span class="warning-label">⚠️ Important</span><span>Sauvegardez les codes de récupération affichés lors de l'activation du 2FA — ils sont la seule façon de récupérer votre compte si vous perdez accès à votre téléphone.</span></div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 9 — PARAMÈTRES
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-indigo"></div>
    <div class="module-icon">⚙️</div>
    <div>
      <div class="module-title">Module 9 — Paramètres</div>
      <div class="module-subtitle">Numérotation, mentions légales, TVA et intégrations</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-purple">1</div><div class="section-title">Configurer la numérotation des documents</div></div>
    <ol class="steps">
      <li><span class="n n-purple">1.</span><span>Allez dans <strong>Paramètres &gt; Numérotation</strong>.</span></li>
      <li><span class="n n-purple">2.</span><span>Pour chaque type (Facture, Devis, BL…), définissez : préfixe, année/mois, longueur de séquence.</span></li>
      <li><span class="n n-purple">3.</span><span>Exemples : <code>FACT-2026-0001</code>, <code>BL-0001</code>, <code>DEV-2026-0001</code>.</span></li>
      <li><span class="n n-purple">4.</span><span>Enregistrez — la numérotation est indépendante par type de document.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Incluez l'année dans le préfixe (FACT-2026-) pour faciliter le classement et la recherche de vos archives fiscales.</span></div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-purple">2</div><div class="section-title">Mentions légales et pied de page</div></div>
    <ol class="steps">
      <li><span class="n n-purple">1.</span><span>Allez dans <strong>Paramètres &gt; Société &gt; Pied de page</strong>.</span></li>
      <li><span class="n n-purple">2.</span><span>Saisissez vos mentions légales : RIB, conditions de paiement, pénalités de retard.</span></li>
      <li><span class="n n-purple">3.</span><span>Ces mentions apparaissent automatiquement sur tous vos documents PDF.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Les mentions OHADA obligatoires (RCCM, NIF, capital social) sont insérées automatiquement si renseignées dans la fiche société — inutile de les répéter dans le pied de page.</span></div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-purple">3</div><div class="section-title">Taux de TVA par pays</div></div>
    <ol class="steps">
      <li><span class="n n-purple">1.</span><span>Allez dans <strong>Paramètres &gt; Fiscalité</strong>.</span></li>
      <li><span class="n n-purple">2.</span><span>Sélectionnez votre pays : Côte d'Ivoire (18%), Sénégal (18%), Maroc (20%), France (20%), etc.</span></li>
      <li><span class="n n-purple">3.</span><span>FactPro applique automatiquement les bons taux sur vos documents.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-purple">4</div><div class="section-title">Connecteurs Zapier / Make</div></div>
    <ol class="steps">
      <li><span class="n n-purple">1.</span><span>Allez dans <strong>Paramètres &gt; Intégrations &gt; Webhooks</strong>.</span></li>
      <li><span class="n n-purple">2.</span><span>Copiez l'URL de webhook FactPro dans votre scénario Zapier ou Make.</span></li>
      <li><span class="n n-purple">3.</span><span>Choisissez les événements déclencheurs : nouvelle facture, paiement reçu, nouveau client…</span></li>
    </ol>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 10 — ABONNEMENT & LICENCE
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-orange"></div>
    <div class="module-icon">💳</div>
    <div>
      <div class="module-title">Module 10 — Abonnement &amp; Licence</div>
      <div class="module-subtitle">Forfaits, limites, changement de plan et facturation prorata</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-amber">1</div><div class="section-title">Limites par forfait</div></div>
    <table class="data">
      <thead>
        <tr>
          <th>Fonctionnalité</th>
          <th style="text-align:center">Starter</th>
          <th style="text-align:center">Pro</th>
          <th style="text-align:center">Business</th>
          <th style="text-align:center">Enterprise</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>Utilisateurs</td><td style="text-align:center">1</td><td style="text-align:center">5</td><td style="text-align:center">20</td><td style="text-align:center">Illimité</td></tr>
        <tr><td>Documents / mois</td><td style="text-align:center">50</td><td style="text-align:center">500</td><td style="text-align:center">5 000</td><td style="text-align:center">Illimité</td></tr>
        <tr><td>Templates PDF</td><td style="text-align:center">5</td><td style="text-align:center">30</td><td style="text-align:center">94</td><td style="text-align:center">94+</td></tr>
        <tr><td>Sociétés</td><td style="text-align:center">1</td><td style="text-align:center">3</td><td style="text-align:center">10</td><td style="text-align:center">Illimité</td></tr>
        <tr><td>API REST</td><td style="text-align:center" class="dash">—</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="check">✓</td><td style="text-align:center" class="check">✓</td></tr>
      </tbody>
    </table>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>FactPro vous envoie une notification à 80% d'utilisation mensuelle pour vous laisser le temps de passer au forfait supérieur avant d'atteindre la limite.</span></div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-amber">2</div><div class="section-title">Changer de forfait</div></div>
    <ol class="steps">
      <li><span class="n n-amber">1.</span><span>Allez dans <strong>Paramètres &gt; Abonnement</strong>.</span></li>
      <li><span class="n n-amber">2.</span><span>Cliquez sur <em>Changer de forfait</em>.</span></li>
      <li><span class="n n-amber">3.</span><span>Le nouveau forfait est activé immédiatement — les fonctionnalités supplémentaires sont disponibles instantanément.</span></li>
      <li><span class="n n-amber">4.</span><span>La facturation est calculée au prorata du mois en cours.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-amber">3</div><div class="section-title">Modes de paiement de l'abonnement</div></div>
    <ul class="bullets">
      <li><span class="dot n-amber">•</span><span><strong>Mobile Money</strong> : Orange Money, Wave, MTN MoMo — renouvellement automatique mensuel</span></li>
      <li><span class="dot n-amber">•</span><span><strong>Virement bancaire</strong> : pour les abonnements annuels</span></li>
      <li><span class="dot n-amber">•</span><span><strong>Carte bancaire</strong> : Visa / Mastercard via CinetPay</span></li>
    </ul>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 11 — CATALOGUE DE MODÈLES
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-blue"></div>
    <div class="module-icon">📋</div>
    <div>
      <div class="module-title">Module 11 — Catalogue de modèles</div>
      <div class="module-subtitle">498 modèles sectoriels, aperçu interactif et création en 1 clic</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-blue">1</div><div class="section-title">Qu'est-ce que le catalogue ?</div></div>
    <p class="section-body">FactPro propose <strong>498 modèles de documents</strong> prêts à l'emploi, organisés en <strong>24 catégories sectorielles</strong> (BTP, Transport, Commerce, Juridique, Santé, Agriculture, Restauration, IT, Finance, Immobilier, etc.). Chaque modèle est conçu pour un type d'activité précis et intègre les mentions obligatoires et la structure adaptée au secteur.</p>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-blue">2</div><div class="section-title">Accéder au catalogue</div></div>
    <ol class="steps">
      <li><span class="n n-blue">1.</span><span>Allez dans <strong>Documents</strong> et cliquez sur le bouton <em>Nouveau document</em>.</span></li>
      <li><span class="n n-blue">2.</span><span>Dans la fenêtre de création, cliquez sur l'onglet <strong>Catalogue</strong>.</span></li>
      <li><span class="n n-blue">3.</span><span>Parcourez les 24 catégories ou utilisez la recherche pour trouver le modèle adapté.</span></li>
    </ol>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-blue">3</div><div class="section-title">Utiliser l'aperçu interactif</div></div>
    <ol class="steps">
      <li><span class="n n-blue">1.</span><span>Cliquez sur un modèle pour afficher son <strong>aperçu interactif</strong> avec des données fictives représentatives.</span></li>
      <li><span class="n n-blue">2.</span><span>L'aperçu indique le <strong>style visuel PDF</strong> qui sera pré-sélectionné lors de la création.</span></li>
      <li><span class="n n-blue">3.</span><span>Les données fictives sont <em>indicatives</em> — le document réel sera personnalisé avec vos informations.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Le bandeau "Aperçu indicatif" en haut de la prévisualisation rappelle que les données affichées sont fictives. Le document réel utilisera votre logo, vos informations société et les données de votre client.</span></div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-blue">4</div><div class="section-title">Créer un document depuis le catalogue</div></div>
    <ol class="steps">
      <li><span class="n n-blue">1.</span><span>Après avoir consulté l'aperçu du modèle, cliquez le bouton <em>Créer ce document</em>.</span></li>
      <li><span class="n n-blue">2.</span><span>FactPro lance automatiquement la création avec le <strong>bon type de document</strong> ET le <strong>bon style visuel</strong> pré-sélectionné.</span></li>
      <li><span class="n n-blue">3.</span><span>Complétez les champs du document (client, lignes, montants) comme d'habitude.</span></li>
    </ol>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 12 — STYLES VISUELS PDF
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-purple"></div>
    <div class="module-icon">🎨</div>
    <div>
      <div class="module-title">Module 12 — Styles visuels PDF</div>
      <div class="module-subtitle">Recommandation automatique, galerie et personnalisation</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-purple">1</div><div class="section-title">Style recommandé automatiquement</div></div>
    <p class="section-body" style="margin-bottom:8px">Lors de la création d'un document, FactPro recommande automatiquement un style visuel PDF adapté au type de document :</p>
    <table class="data">
      <thead><tr><th>Type de document</th><th>Style recommandé</th></tr></thead>
      <tbody>
        <tr><td>Bon de livraison</td><td><strong>Template Transport</strong></td></tr>
        <tr><td>Contrat</td><td><strong>Template Juridique</strong></td></tr>
        <tr><td>Bon de commande fournisseur</td><td><strong>Template Corporate</strong></td></tr>
        <tr><td>Facture standard</td><td><strong>Template Commerce</strong></td></tr>
        <tr><td>Devis / Proforma</td><td><strong>Template selon votre secteur</strong></td></tr>
      </tbody>
    </table>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-purple">2</div><div class="section-title">Modifier le style visuel</div></div>
    <ol class="steps">
      <li><span class="n n-purple">1.</span><span>Lors de la création, la galerie est <strong>réduite par défaut</strong> — seul le style recommandé est affiché.</span></li>
      <li><span class="n n-purple">2.</span><span>Cliquez <em>Modifier</em> pour ouvrir la galerie complète des styles disponibles selon votre forfait.</span></li>
      <li><span class="n n-purple">3.</span><span>Cliquez sur un style pour voir un aperçu en temps réel.</span></li>
      <li><span class="n n-purple">4.</span><span>Sélectionnez le style souhaité — la galerie se referme automatiquement.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Le changement de style n'affecte jamais le contenu du document (lignes, montants, client) — uniquement la mise en page visuelle du PDF généré. Vous pouvez changer de style à tout moment, même après la création.</span></div>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODULE 13 — TYPES DE DOCUMENTS
════════════════════════════════════════════════════════════ -->
<div class="module">
  <div class="module-header">
    <div class="module-header-bar c-green"></div>
    <div class="module-icon">📑</div>
    <div>
      <div class="module-title">Module 13 — Types de documents</div>
      <div class="module-subtitle">Workflow recommandé et rôle de chaque document</div>
    </div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-green">1</div><div class="section-title">Workflow commercial recommandé</div></div>
    <p class="section-body" style="margin-bottom:10px">Pour une gestion commerciale complète et tracée, FactPro recommande ce cycle documentaire :</p>
    <div class="workflow">
      <span class="wf-step">Devis</span>
      <span class="wf-arrow">→</span>
      <span class="wf-step">Bon de commande</span>
      <span class="wf-arrow">→</span>
      <span class="wf-step">Bon de livraison</span>
      <span class="wf-arrow">→</span>
      <span class="wf-step">Facture</span>
    </div>
    <p class="section-body" style="margin-top:8px;font-size:8.5pt;color:#6b7280">Chaque étape peut être générée en un clic depuis le document précédent. Ce workflow est recommandé mais non obligatoire.</p>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-green">2</div><div class="section-title">Bon de livraison — pourquoi sans prix ?</div></div>
    <p class="section-body">Le Bon de livraison (BL) <strong>n'affiche pas de prix</strong> — c'est un comportement normal et conforme à la norme OHADA. Le BL est un <strong>document de transport et de réception</strong>, pas un document commercial. Il sert à :</p>
    <ul class="bullets" style="margin-top:6px">
      <li><span class="dot n-green">•</span><span>Confirmer les quantités et références livrées</span></li>
      <li><span class="dot n-green">•</span><span>Obtenir la signature de réception du client ou du livreur</span></li>
      <li><span class="dot n-green">•</span><span>Servir de preuve de livraison en cas de litige</span></li>
    </ul>
    <p class="section-body" style="margin-top:6px;font-size:8.5pt;color:#6b7280">Les prix figurent sur la facture associée, pas sur le bon de livraison.</p>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-green">3</div><div class="section-title">Facture d'acompte et facture de solde</div></div>
    <ol class="steps">
      <li><span class="n n-green">1.</span><span>La <strong>Facture d'acompte</strong> permet de facturer un pourcentage du total <strong>avant la livraison</strong> (ex: 30% à la commande). Elle est liée au devis ou bon de commande d'origine.</span></li>
      <li><span class="n n-green">2.</span><span>Vous pouvez émettre <strong>plusieurs factures d'acompte</strong> successives pour le même projet.</span></li>
      <li><span class="n n-green">3.</span><span>La <strong>Facture de solde</strong> clôture le cycle commercial : elle déduit automatiquement tous les acomptes déjà perçus et affiche uniquement le montant restant dû.</span></li>
      <li><span class="n n-green">4.</span><span>Pour créer une facture de solde, ouvrez le devis ou BC et cliquez <em>Générer la facture de solde</em>.</span></li>
    </ol>
    <div class="tip"><span class="tip-label">💡 Conseil Pro</span><span>Pour les grands projets BTP ou IT, utilisez la séquence : 30% à la commande (acompte 1) + 40% à mi-livraison (acompte 2) + 30% à la réception (solde). FactPro calcule automatiquement chaque montant.</span></div>
  </div>

  <div class="section">
    <div class="section-header"><div class="section-num bg-green">4</div><div class="section-title">Autres types de documents disponibles</div></div>
    <table class="data">
      <thead><tr><th>Document</th><th>Usage principal</th></tr></thead>
      <tbody>
        <tr><td><strong>Facture Proforma</strong></td><td>Offre de prix avant acceptation, douane, importation</td></tr>
        <tr><td><strong>Reçu de paiement</strong></td><td>Preuve d'encaissement pour le client</td></tr>
        <tr><td><strong>Quittance de loyer</strong></td><td>Immobilier : preuve de paiement mensuel du loyer</td></tr>
        <tr><td><strong>Avis de loyer</strong></td><td>Immobilier : demande de paiement mensuel au locataire</td></tr>
        <tr><td><strong>Contrat</strong></td><td>Prestation de services, accord commercial</td></tr>
        <tr><td><strong>Bon de commande client</strong></td><td>Confirmation de commande signée par le client</td></tr>
        <tr><td><strong>Rapport d'intervention</strong></td><td>Maintenance, dépannage technique</td></tr>
        <tr><td><strong>Bon de transfert</strong></td><td>Mouvement de stock entre entrepôts</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     PAGE FINALE — CONTACT & SUPPORT
════════════════════════════════════════════════════════════ -->
<div class="module" style="text-align:center; padding: 60px 50px;">
  <div style="font-size: 40pt; margin-bottom: 20px;">🤖</div>
  <div style="font-size: 18pt; font-weight: 800; color: #1f2937; margin-bottom: 8px;">Une question ? Parlez à SARA</div>
  <div style="font-size: 10pt; color: #6b7280; margin-bottom: 32px; max-width: 400px; margin-left: auto; margin-right: auto; line-height: 1.6;">
    Notre assistante IA répond 24h/24 à toutes vos questions sur FactPro.<br>
    Accessible depuis n'importe quelle page de l'application.
  </div>
  <div style="border-top: 1px solid #e5e7eb; padding-top: 24px; margin-top: 24px;">
    <div style="font-size: 9pt; color: #9ca3af; line-height: 1.8;">
      <strong style="color:#1f2937">IBIG Soft</strong> — Éditeur de FactPro<br>
      support@ibigsoft.com &nbsp;·&nbsp; factpro.ibigsoft.com<br>
      Guide v2.5 — Juillet 2026 &nbsp;·&nbsp; © IBIG Soft. Tous droits réservés.
    </div>
  </div>
</div>

</body>
</html>
