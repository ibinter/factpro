<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>
<template>
  <LegalLayout title="Politique des cookies" :can-login="canLogin" :can-register="canRegister">
    <h2>1. Qu'est-ce qu'un cookie ?</h2>
    <p>Un cookie est un petit fichier texte déposé sur votre appareil lors de votre visite sur IBIG FactPro. Il permet de mémoriser vos préférences et d'améliorer votre expérience.</p>

    <h2>2. Cookies utilisés</h2>
    <h3>Cookies nécessaires (toujours actifs)</h3>
    <ul>
      <li><strong>Session Laravel :</strong> maintien de votre connexion sécurisée.</li>
      <li><strong>CSRF :</strong> protection contre les attaques de falsification de requêtes.</li>
      <li><strong>Préférences de cookies :</strong> mémorisation de votre choix de consentement.</li>
    </ul>

    <h3>Cookies analytiques (avec consentement)</h3>
    <ul>
      <li>Mesure de l'audience et du comportement de navigation pour améliorer le service.</li>
      <li>Ces données sont anonymisées et ne permettent pas de vous identifier personnellement.</li>
    </ul>

    <h3>Cookies marketing (avec consentement)</h3>
    <ul>
      <li>Publicités pertinentes basées sur vos centres d'intérêt.</li>
      <li>Mesure de l'efficacité de nos campagnes publicitaires.</li>
    </ul>

    <h2>3. Gestion de vos préférences</h2>
    <p>Vous pouvez à tout moment modifier vos préférences de cookies via la bannière disponible en bas de page. Vous pouvez également désactiver les cookies dans les paramètres de votre navigateur, ce qui peut affecter le fonctionnement du service.</p>

    <h2>4. Durée de conservation</h2>
    <p>Les cookies de session expirent à la fermeture du navigateur. Les cookies de préférences sont conservés 12 mois. Les cookies analytiques et marketing sont conservés selon les politiques des prestataires concernés.</p>

    <h2>5. Contact</h2>
    <p>Pour toute question : <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a></p>
  </LegalLayout>
</template>
