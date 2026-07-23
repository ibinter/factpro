<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Politique anti-spam" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Engagement anti-spam d'IBIG Soft SARL</h2>
    <p>
      IBIG Soft SARL s'engage fermement à lutter contre le spam et les communications non sollicitées.
      La présente politique définit les règles que nous appliquons dans nos propres communications,
      ainsi que les obligations imposées aux utilisateurs d'<strong>IBIG FactPro</strong>
      qui utilisent les fonctionnalités d'envoi d'emails (factures, devis, relances clients).
    </p>

    <h2>2. Communications d'IBIG Soft SARL vers ses clients</h2>
    <p>IBIG Soft SARL s'engage à :</p>
    <ul>
      <li>N'envoyer des emails commerciaux qu'aux utilisateurs ayant expressément consenti à les recevoir lors de l'inscription ou via les préférences de notification.</li>
      <li>Inclure systématiquement un lien de désinscription fonctionnel dans chaque email commercial.</li>
      <li>Honorer immédiatement toute demande de désinscription (désabonnement effectif sous 48 heures).</li>
      <li>Ne jamais vendre, louer ou partager les adresses email des utilisateurs à des tiers à des fins de prospection commerciale.</li>
      <li>Identifier clairement l'expéditeur (<em>IBIG Soft SARL</em>) et l'objet de chaque communication.</li>
    </ul>

    <h2>3. Catégories d'emails envoyés par IBIG Soft SARL</h2>
    <ul>
      <li><strong>Emails transactionnels</strong> (confirmation d'inscription, factures, réinitialisation de mot de passe) : envoyés automatiquement et nécessaires à l'exécution du contrat — non soumis à opt-in mais limités à leur finalité.</li>
      <li><strong>Emails de service</strong> (alertes de disponibilité, notifications SLA, changements de politique) : envoyés à tous les abonnés actifs — indispensables à la bonne exécution du service.</li>
      <li><strong>Emails marketing</strong> (nouveautés produit, offres promotionnelles) : envoyés uniquement aux abonnés ayant consenti explicitement.</li>
    </ul>

    <h2>4. Obligations des utilisateurs d'IBIG FactPro</h2>
    <p>
      Les utilisateurs qui envoient des emails via IBIG FactPro (factures, devis, relances) s'engagent à :
    </p>
    <ul>
      <li>N'utiliser les fonctionnalités d'envoi que pour des communications commerciales légitimes liées à leur activité professionnelle.</li>
      <li>S'assurer que leurs destinataires ont consenti à recevoir des communications commerciales conformément aux lois applicables dans leurs pays respectifs.</li>
      <li>Ne pas utiliser IBIG FactPro pour envoyer des messages non sollicités, des offres frauduleuses ou tout contenu trompeur.</li>
      <li>Respecter les législations locales anti-spam (loi ivoirienne sur la cybercriminalité, RGPD pour les destinataires en Europe, CAN-SPAM pour les États-Unis, etc.).</li>
      <li>Ne pas usurper l'identité d'un tiers dans les champs d'expéditeur.</li>
    </ul>

    <h2>5. Mesures techniques anti-spam</h2>
    <p>IBIG Soft SARL met en œuvre les mesures techniques suivantes :</p>
    <ul>
      <li>Configuration SPF, DKIM et DMARC sur les domaines d'envoi pour authentifier les emails.</li>
      <li>Limitation de débit (rate limiting) sur les envois en masse pour prévenir les abus.</li>
      <li>Surveillance des listes noires (blacklists) d'IP et de domaines.</li>
      <li>Filtrage automatique des contenus suspects avant envoi.</li>
      <li>Désactivation immédiate des comptes utilisateurs identifiés comme sources de spam.</li>
    </ul>

    <h2>6. Signalement d'abus</h2>
    <p>
      Si vous recevez un email non sollicité provenant prétendument d'IBIG Soft SARL ou envoyé
      via IBIG FactPro, signalez-le immédiatement à :
      <a href="mailto:contact@ibigsoft.com">contact@ibigsoft.com</a> — objet <em>« Signalement spam »</em>.
    </p>
    <p>
      Toute violation confirmée par un utilisateur entraînera la suspension immédiate de son compte
      sans remboursement, conformément aux <a href="/legal/cgu">Conditions Générales d'Utilisation</a>.
    </p>

    <h2>7. Gestion des préférences de communication</h2>
    <p>
      Vous pouvez gérer vos préférences de communication depuis votre espace client,
      rubrique <strong>Paramètres → Notifications</strong>, ou en nous contactant à
      <a href="mailto:contact@ibigsoft.com">contact@ibigsoft.com</a>.
    </p>

  </LegalLayout>
</template>
