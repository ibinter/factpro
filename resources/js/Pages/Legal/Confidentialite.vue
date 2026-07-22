<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Politique de confidentialité" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Responsable du traitement</h2>
    <p>
      <strong>IBIG SARL</strong> – Intermark Business International Group, éditeur du logiciel
      IBIG FactPro, est responsable du traitement de vos données personnelles.<br>
      Contact : <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a>
    </p>

    <h2>2. Données collectées</h2>

    <h3>Données de compte</h3>
    <ul>
      <li>Nom, prénom, adresse email, mot de passe (hashé et non lisible).</li>
      <li>Langue préférée, fuseau horaire, avatar.</li>
    </ul>

    <h3>Données de société</h3>
    <ul>
      <li>Raison sociale, adresse, pays, numéros d'identification fiscale (NIF, RCCM, etc.).</li>
      <li>Logo de la société, coordonnées bancaires (optionnelles).</li>
    </ul>

    <h3>Données métier</h3>
    <ul>
      <li>Clients, factures, devis, produits, paiements, stocks — saisies par l'utilisateur.</li>
      <li>Ces données vous appartiennent entièrement et ne sont jamais utilisées à d'autres fins.</li>
    </ul>

    <h3>Données techniques</h3>
    <ul>
      <li>Adresse IP, navigateur, type d'appareil, système d'exploitation.</li>
      <li>Logs de connexion, journal d'audit des actions dans l'application.</li>
    </ul>

    <h3>Données de paiement</h3>
    <ul>
      <li>Historique des abonnements et transactions.</li>
      <li><strong>Aucun numéro de carte bancaire</strong> n'est stocké sur nos serveurs.</li>
    </ul>

    <h2>3. Finalités du traitement</h2>
    <ul>
      <li>Fourniture, amélioration et sécurisation du service IBIG FactPro.</li>
      <li>Gestion des abonnements, facturation et recouvrement.</li>
      <li>Support technique et assistance aux utilisateurs.</li>
      <li>Prévention des fraudes et protection de la sécurité.</li>
      <li>Communications relatives au compte (expiration, alertes, nouveautés).</li>
      <li>Statistiques d'usage anonymisées pour améliorer le produit.</li>
    </ul>

    <h2>4. Base légale du traitement</h2>
    <ul>
      <li><strong>Exécution du contrat</strong> — fourniture du service souscrit.</li>
      <li><strong>Intérêt légitime</strong> — sécurité, prévention des fraudes.</li>
      <li><strong>Consentement</strong> — communications marketing, cookies non essentiels.</li>
    </ul>

    <h2>5. Durée de conservation</h2>
    <ul>
      <li>Données de compte : durée de l'abonnement + <strong>12 mois</strong> après résiliation.</li>
      <li>Documents commerciaux (factures, etc.) : jusqu'à <strong>10 ans</strong> selon les obligations légales comptables applicables.</li>
      <li>Logs techniques : <strong>12 mois</strong> glissants.</li>
    </ul>

    <h2>6. Partage des données</h2>
    <p>Vos données ne sont <strong>jamais vendues</strong>. Elles peuvent être partagées uniquement avec :</p>
    <ul>
      <li>Prestataires d'hébergement nécessaires au fonctionnement du service.</li>
      <li>Prestataires d'envoi d'email transactionnel (notifications, alertes).</li>
      <li>Autorités légales sur demande conforme aux lois en vigueur.</li>
    </ul>

    <h2>7. Vos droits</h2>
    <p>Vous disposez des droits suivants sur vos données personnelles :</p>
    <ul>
      <li><strong>Accès</strong> — obtenir une copie de vos données.</li>
      <li><strong>Rectification</strong> — corriger des données inexactes.</li>
      <li><strong>Effacement</strong> — demander la suppression de vos données.</li>
      <li><strong>Portabilité</strong> — recevoir vos données dans un format structuré.</li>
      <li><strong>Opposition</strong> — vous opposer à certains traitements.</li>
    </ul>
    <p>Pour exercer ces droits : <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a></p>

    <h2>8. Cookies</h2>
    <p>
      L'utilisation des cookies est détaillée dans notre
      <a href="/legal/cookies">Politique des cookies</a>.
    </p>

    <h2>9. Sécurité</h2>
    <p>IBIG SARL met en œuvre les mesures suivantes pour protéger vos données :</p>
    <ul>
      <li>Chiffrement SSL/TLS de toutes les communications.</li>
      <li>Mots de passe hachés (bcrypt) — jamais lisibles.</li>
      <li>Authentification à deux facteurs (2FA) disponible.</li>
      <li>Sauvegardes quotidiennes chiffrées hors site.</li>
      <li>Journal d'audit complet de toutes les actions sensibles.</li>
    </ul>

    <h2>10. Modifications de la politique</h2>
    <p>
      Cette politique peut être modifiée. Vous serez notifié par email en cas de changement
      substantiel. La version en vigueur est toujours celle publiée sur cette page.
    </p>

  </LegalLayout>
</template>
