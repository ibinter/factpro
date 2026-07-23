<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Déclaration d'accessibilité" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Engagement d'accessibilité</h2>
    <p>
      IBIG Soft SARL s'engage à rendre <strong>IBIG FactPro</strong> accessible au plus grand nombre,
      y compris aux personnes en situation de handicap. Cette déclaration s'applique à l'application
      web disponible à l'adresse <strong>app.factpro.ibigsoft.com</strong> ainsi qu'aux pages publiques
      du site <strong>ibigsoft.com</strong>.
    </p>

    <h2>2. Référentiel et niveau de conformité</h2>
    <p>
      IBIG FactPro vise la conformité aux règles d'accessibilité du contenu Web
      (<strong>WCAG 2.1 niveau AA</strong>) et aux bonnes pratiques du référentiel
      <strong>RGAA 4.1</strong> (Référentiel Général d'Amélioration de l'Accessibilité).
      Une évaluation interne a été conduite en juillet 2026.
    </p>
    <p><strong>État de conformité actuel :</strong> conformité partielle.</p>

    <h2>3. Mesures mises en œuvre</h2>
    <ul>
      <li>Contraste des couleurs respectant un ratio minimum de 4,5:1 pour le texte courant.</li>
      <li>Navigation entièrement réalisable au clavier (tabulation, raccourcis ARIA).</li>
      <li>Étiquettes descriptives sur tous les champs de formulaire.</li>
      <li>Messages d'erreur explicites et accessibles via les lecteurs d'écran.</li>
      <li>Compatibilité testée avec les lecteurs d'écran NVDA (Windows) et VoiceOver (macOS/iOS).</li>
      <li>Pas de contenu clignotant susceptible de provoquer des crises d'épilepsie.</li>
      <li>Structure sémantique HTML5 avec rôles ARIA appropriés.</li>
    </ul>

    <h2>4. Contenus non accessibles</h2>
    <p>
      Certains contenus ne sont pas encore entièrement conformes en raison de contraintes techniques
      liées à des composants tiers en cours de remplacement :
    </p>
    <ul>
      <li>Certains graphiques statistiques du tableau de bord (alternatives textuelles en cours d'ajout).</li>
      <li>Quelques PDF générés automatiquement (factures, devis) dont la structure ne permet pas encore une lecture par synthèse vocale.</li>
      <li>Certains messages d'info-bulle (tooltip) dont la durée d'affichage n'est pas contrôlable.</li>
    </ul>

    <h2>5. Technologies et outils d'assistance pris en charge</h2>
    <ul>
      <li>Lecteurs d'écran : NVDA + Firefox, JAWS + Chrome, VoiceOver + Safari.</li>
      <li>Zoom navigateur jusqu'à 200 % sans perte de contenu ni de fonctionnalité.</li>
      <li>Mode contraste élevé Windows et macOS.</li>
      <li>Navigation clavier complète (Tab, Shift+Tab, Entrée, Espace, flèches directionnelles).</li>
    </ul>

    <h2>6. Plan d'amélioration</h2>
    <p>
      IBIG Soft SARL s'engage à améliorer progressivement le niveau d'accessibilité de la plateforme.
      Les correctifs prioritaires sont planifiés dans la feuille de route produit Q3–Q4 2026 :
    </p>
    <ul>
      <li>Ajout d'alternatives textuelles pour tous les graphiques (Q3 2026).</li>
      <li>Amélioration de l'accessibilité des PDF générés (Q4 2026).</li>
      <li>Audit complet RGAA 4.1 par un prestataire tiers (Q4 2026).</li>
    </ul>

    <h2>7. Signalement et contact</h2>
    <p>
      Si vous rencontrez une difficulté d'accès à un contenu ou une fonctionnalité, ou si vous souhaitez
      nous signaler un problème d'accessibilité, contactez-nous :
    </p>
    <ul>
      <li>Email : <a href="mailto:contact@ibigsoft.com">contact@ibigsoft.com</a></li>
      <li>Objet : <em>« Accessibilité — IBIG FactPro »</em></li>
    </ul>
    <p>
      Nous nous engageons à vous répondre dans un délai de <strong>5 jours ouvrés</strong>
      et à traiter votre demande dans les meilleurs délais.
    </p>

    <h2>8. Voie de recours</h2>
    <p>
      Si vous n'obtenez pas de réponse satisfaisante, vous pouvez saisir les autorités compétentes
      en matière de protection des droits des personnes handicapées en République de Côte d'Ivoire,
      conformément à la législation nationale en vigueur.
    </p>

  </LegalLayout>
</template>
