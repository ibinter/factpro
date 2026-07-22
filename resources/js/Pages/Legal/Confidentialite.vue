<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>
<template>
  <LegalLayout title="Politique de confidentialité" :can-login="canLogin" :can-register="canRegister">
    <h2>1. Responsable du traitement</h2>
    <p>IBIG SARL – Intermark Business International Group, éditeur du logiciel IBIG FactPro, est responsable du traitement de vos données personnelles. Contact : <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a></p>

    <h2>2. Données collectées</h2>
    <p>Nous collectons les données suivantes :</p>
    <ul>
      <li><strong>Données de compte :</strong> nom, prénom, email, mot de passe (hashé), langue, fuseau horaire.</li>
      <li><strong>Données de société :</strong> raison sociale, adresse, pays, numéros d'identification fiscale.</li>
      <li><strong>Données métier :</strong> clients, factures, devis, produits, paiements, stocks — saisies par l'utilisateur.</li>
      <li><strong>Données techniques :</strong> adresse IP, navigateur, appareil, logs de connexion, actions dans le journal d'audit.</li>
      <li><strong>Données de paiement :</strong> historique des abonnements et paiements (jamais de numéro de carte bancaire).</li>
    </ul>

    <h2>3. Finalités du traitement</h2>
    <ul>
      <li>Fourniture et amélioration du service IBIG FactPro.</li>
      <li>Gestion des abonnements et des paiements.</li>
      <li>Support technique et assistance.</li>
      <li>Sécurité et prévention des fraudes.</li>
      <li>Communications relatives au compte (expiration, renouvellement, alertes de sécurité).</li>
      <li>Amélioration des fonctionnalités (statistiques anonymisées).</li>
    </ul>

    <h2>4. Base légale</h2>
    <p>Le traitement est fondé sur : l'exécution du contrat (fourniture du service), l'intérêt légitime (sécurité, prévention des fraudes), le consentement (communications marketing, cookies non essentiels).</p>

    <h2>5. Conservation des données</h2>
    <p>Vos données de compte sont conservées pendant la durée de votre abonnement, plus 12 mois après la résiliation (sauf obligation légale). Les données métier (factures, etc.) peuvent être soumises à une obligation de conservation de 10 ans selon la législation applicable.</p>

    <h2>6. Partage des données</h2>
    <p>Vos données ne sont pas vendues. Elles peuvent être partagées avec :</p>
    <ul>
      <li>Prestataires techniques nécessaires au fonctionnement du service (hébergement, emails, IA).</li>
      <li>Autorités légales sur demande conforme aux lois applicables.</li>
    </ul>

    <h2>7. Vos droits</h2>
    <p>Vous disposez des droits suivants : accès, rectification, effacement, limitation, portabilité, opposition. Pour exercer ces droits : <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a></p>

    <h2>8. Cookies</h2>
    <p>L'utilisation des cookies est détaillée dans notre <a href="/legal/cookies">Politique des cookies</a>.</p>

    <h2>9. Sécurité</h2>
    <p>IBIG Soft met en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données : chiffrement, contrôle d'accès, journalisation, sauvegardes chiffrées, authentification à deux facteurs disponible.</p>

    <h2>10. Modifications</h2>
    <p>Cette politique peut être modifiée. Vous serez notifié par email en cas de changement substantiel. La version en vigueur est celle publiée sur ce site.</p>
  </LegalLayout>
</template>
