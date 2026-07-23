<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Politique d'utilisation acceptable" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Objet</h2>
    <p>
      La présente Politique d'Utilisation Acceptable (<strong>PUA</strong>) définit les règles de bonne
      conduite applicables à tous les utilisateurs d'<strong>IBIG FactPro</strong>. Elle vise à protéger
      l'intégrité de la plateforme, les données des autres utilisateurs et la réputation d'IBIG Soft SARL.
      L'utilisation de la plateforme implique l'acceptation sans réserve de la présente politique.
    </p>

    <h2>2. Usages légitimes</h2>
    <p>IBIG FactPro est conçu pour être utilisé exclusivement à des fins professionnelles légitimes :</p>
    <ul>
      <li>Création, gestion et envoi de factures, devis, bons de commande et avoirs.</li>
      <li>Gestion de la clientèle, des fournisseurs, des produits et des stocks.</li>
      <li>Suivi comptable et financier de l'activité de l'entreprise abonnée.</li>
      <li>Génération de rapports et tableaux de bord pour la prise de décision.</li>
      <li>Utilisation des fonctionnalités d'automatisation et d'intégration API dans le cadre de l'activité de l'abonné.</li>
    </ul>

    <h2>3. Comportements interdits</h2>
    <p>Il est strictement interdit d'utiliser IBIG FactPro pour :</p>
    <ul>
      <li>Créer ou envoyer des factures, devis ou documents frauduleux ou fictifs.</li>
      <li>Blanchir des capitaux, financer des activités illicites ou contourner des obligations fiscales.</li>
      <li>Stocker, traiter ou transmettre des données à caractère pornographique, haineux, discriminatoire ou illégal.</li>
      <li>Porter atteinte aux droits de propriété intellectuelle de tiers (marques, brevets, droits d'auteur).</li>
      <li>Tenter d'accéder aux données d'autres abonnés ou de compromettre la sécurité de la plateforme.</li>
      <li>Mener des attaques informatiques (injection, déni de service, fuzzing, exploitation de failles).</li>
      <li>Utiliser des robots ou scripts automatisés non autorisés pour extraire des données.</li>
      <li>Revendre, sous-louer ou partager l'accès à la plateforme avec des tiers non autorisés.</li>
      <li>Usurper l'identité d'IBIG Soft SARL, de ses employés ou d'autres utilisateurs.</li>
      <li>Toute activité violant les lois ivoiriennes, les dispositions OHADA ou toute autre loi applicable.</li>
    </ul>

    <h2>4. Contenu généré par les utilisateurs</h2>
    <p>
      Les utilisateurs sont seuls responsables des données qu'ils saisissent dans IBIG FactPro
      (informations clients, descriptions de produits, montants, etc.). IBIG Soft SARL ne vérifie pas
      le contenu saisi et décline toute responsabilité à cet égard. Tout contenu illicite constaté
      sera supprimé et pourra entraîner la suspension du compte concerné.
    </p>

    <h2>5. Obligations générales des utilisateurs</h2>
    <ul>
      <li>Maintenir la confidentialité des identifiants de connexion.</li>
      <li>Signaler immédiatement tout accès non autorisé ou comportement suspect.</li>
      <li>Utiliser la plateforme dans le respect des lois fiscales et commerciales applicables.</li>
      <li>Ne pas interférer avec le bon fonctionnement des services pour les autres utilisateurs.</li>
    </ul>

    <h2>6. Surveillance et détection des abus</h2>
    <p>
      IBIG Soft SARL se réserve le droit de surveiller l'utilisation de la plateforme
      afin de détecter les violations de la présente politique. Cette surveillance est effectuée
      dans le respect de la confidentialité des données et uniquement à des fins de sécurité
      et de conformité. Des alertes automatiques peuvent être déclenchées en cas d'activité anormale.
    </p>

    <h2>7. Conséquences en cas de violation</h2>
    <p>Tout manquement à la présente politique peut entraîner :</p>
    <ul>
      <li>Un avertissement formel par email.</li>
      <li>La suspension temporaire ou définitive du compte, sans remboursement.</li>
      <li>La transmission des informations aux autorités compétentes en cas d'infraction pénale.</li>
      <li>Une action en dommages et intérêts pour le préjudice causé à IBIG Soft SARL ou à des tiers.</li>
    </ul>

    <h2>8. Signalement d'abus</h2>
    <p>
      Pour signaler un abus ou une violation de la présente politique :
      <a href="mailto:contact@ibigsoft.com">contact@ibigsoft.com</a> — objet <em>« Signalement abus PUA »</em>.
    </p>

  </LegalLayout>
</template>
