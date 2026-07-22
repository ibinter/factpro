<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Politique des cookies" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Qu'est-ce qu'un cookie ?</h2>
    <p>
      Un cookie est un petit fichier texte déposé sur votre appareil (ordinateur, smartphone, tablette)
      lors de votre visite sur IBIG FactPro. Il permet de mémoriser des informations
      sur votre navigation afin d'améliorer votre expérience.
    </p>

    <h2>2. Cookies utilisés</h2>

    <h3>Cookies nécessaires — toujours actifs</h3>
    <ul>
      <li><strong>Session Laravel</strong> — maintien de votre connexion sécurisée entre les pages.</li>
      <li><strong>Token CSRF</strong> — protection contre les attaques de falsification de requêtes.</li>
      <li><strong>Préférences cookies</strong> — mémorisation de votre choix de consentement.</li>
    </ul>

    <h3>Cookies analytiques — avec consentement</h3>
    <ul>
      <li>Mesure de l'audience et analyse du comportement de navigation.</li>
      <li>Identification des pages les plus visitées pour améliorer le service.</li>
      <li>Ces données sont anonymisées et ne permettent pas de vous identifier personnellement.</li>
    </ul>

    <h3>Cookies marketing — avec consentement</h3>
    <ul>
      <li>Affichage de publicités pertinentes basées sur vos centres d'intérêt.</li>
      <li>Mesure de l'efficacité de nos campagnes publicitaires.</li>
      <li>Partage limité avec des partenaires publicitaires sélectionnés.</li>
    </ul>

    <h2>3. Durée de conservation</h2>
    <ul>
      <li><strong>Cookies de session</strong> — expirent à la fermeture du navigateur.</li>
      <li><strong>Cookies de préférences</strong> — conservés <strong>12 mois</strong>.</li>
      <li><strong>Cookies analytiques</strong> — selon les politiques des prestataires (max. 13 mois).</li>
      <li><strong>Cookies marketing</strong> — selon les politiques des partenaires.</li>
    </ul>

    <h2>4. Gestion de vos préférences</h2>
    <p>
      Vous pouvez à tout moment modifier vos préférences de cookies via la bannière
      disponible en bas de page sur le site public. Vous pouvez également désactiver
      les cookies dans les paramètres de votre navigateur.
    </p>
    <p>
      <strong>Attention :</strong> désactiver certains cookies nécessaires peut affecter
      le bon fonctionnement du service (connexion, sécurité).
    </p>

    <h2>5. Contact</h2>
    <p>
      Pour toute question relative aux cookies :
      <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a>
    </p>

  </LegalLayout>
</template>
