<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Conditions d'utilisation de l'API" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Objet</h2>
    <p>
      Les présentes conditions régissent l'accès et l'utilisation de l'<strong>API REST IBIG FactPro</strong>
      mise à disposition par IBIG Soft SARL. L'API permet aux développeurs et intégrateurs d'automatiser
      des opérations (création de factures, gestion de clients, récupération de données, etc.)
      et d'intégrer IBIG FactPro à d'autres systèmes d'information.
      L'accès à l'API implique l'acceptation pleine et entière des présentes conditions.
    </p>

    <h2>2. Accès et clés API</h2>
    <ul>
      <li>L'accès à l'API nécessite un abonnement actif à IBIG FactPro (forfait Pro ou Enterprise).</li>
      <li>Des clés API personnelles sont générées depuis l'espace client (<strong>Paramètres → API</strong>).</li>
      <li>Chaque clé API est liée à un compte utilisateur et hérite de ses permissions.</li>
      <li>Les clés API doivent être traitées comme des mots de passe : ne jamais les exposer dans du code public (dépôts Git, forums, etc.).</li>
      <li>En cas de compromission suspectée, la clé doit être révoquée immédiatement depuis l'espace client.</li>
    </ul>

    <h2>3. Limites de débit (Rate Limiting)</h2>
    <ul>
      <li><strong>Forfait Pro :</strong> 1 000 requêtes par heure par clé API.</li>
      <li><strong>Forfait Enterprise :</strong> 10 000 requêtes par heure par clé API (limites personnalisables sur demande).</li>
      <li>Les limites sont retournées dans les en-têtes HTTP (<code>X-RateLimit-Limit</code>, <code>X-RateLimit-Remaining</code>, <code>X-RateLimit-Reset</code>).</li>
      <li>En cas de dépassement, un code HTTP <strong>429 Too Many Requests</strong> est retourné.</li>
    </ul>

    <h2>4. Usages autorisés</h2>
    <ul>
      <li>Automatisation des processus métiers liés à la facturation, gestion des clients et des stocks.</li>
      <li>Intégration avec des ERP, CRM, outils e-commerce ou plateformes de paiement.</li>
      <li>Développement d'applications tierces destinées aux propres clients de l'abonné.</li>
      <li>Tests et développement dans un environnement sandbox dédié.</li>
    </ul>

    <h2>5. Usages interdits</h2>
    <ul>
      <li>Revente ou redistribution de l'accès API à des tiers sans accord écrit préalable d'IBIG Soft SARL.</li>
      <li>Extraction massive de données (scraping) à des fins non liées à l'activité de l'abonné.</li>
      <li>Tentatives de contournement des limites de débit ou des mécanismes de sécurité.</li>
      <li>Utilisation de l'API pour accéder aux données d'autres clients de la plateforme.</li>
      <li>Tout usage contraire à la <a href="/legal/utilisation-acceptable">Politique d'utilisation acceptable</a>.</li>
    </ul>

    <h2>6. Disponibilité et versionnage</h2>
    <p>
      L'API est versionnée. La version courante est <strong>v1</strong> (endpoint : <code>/api/v1/</code>).
      IBIG Soft SARL s'engage à maintenir chaque version majeure pendant au moins <strong>12 mois</strong>
      après l'annonce d'une nouvelle version, afin de permettre une migration sereine.
      Les changements incompatibles (breaking changes) sont notifiés 30 jours à l'avance par email.
    </p>

    <h2>7. Responsabilité du développeur</h2>
    <p>
      L'intégrateur est seul responsable de la qualité, de la sécurité et de la conformité
      des applications qu'il développe à partir de l'API IBIG FactPro. IBIG Soft SARL ne saurait
      être tenu responsable des dysfonctionnements résultant d'une intégration incorrecte ou
      d'une utilisation non conforme aux présentes conditions.
    </p>

    <h2>8. Suspension et résiliation</h2>
    <p>
      IBIG Soft SARL se réserve le droit de suspendre ou révoquer l'accès API en cas de :
    </p>
    <ul>
      <li>Non-respect des présentes conditions.</li>
      <li>Activité anormale susceptible de perturber la plateforme.</li>
      <li>Résiliation de l'abonnement principal.</li>
    </ul>

    <h2>9. Documentation et support</h2>
    <p>
      La documentation complète de l'API est disponible à l'adresse
      <strong>docs.ibigsoft.com/api</strong>.
      Pour toute question technique : <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a>.
    </p>

  </LegalLayout>
</template>
