<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Politique de sécurité" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Engagement de sécurité</h2>
    <p>
      IBIG Soft SARL place la sécurité des données de ses clients au cœur de ses priorités.
      La présente politique décrit les mesures techniques et organisationnelles mises en œuvre
      pour protéger la plateforme <strong>IBIG FactPro</strong> et les données qu'elle héberge,
      conformément aux meilleures pratiques internationales et aux exigences légales applicables
      en République de Côte d'Ivoire et dans la zone OHADA.
    </p>

    <h2>2. Infrastructure et hébergement sécurisés</h2>
    <ul>
      <li>Serveurs hébergés dans des datacenters certifiés ISO 27001, avec contrôle d'accès physique renforcé.</li>
      <li>Redondance géographique des données pour assurer la continuité en cas de sinistre.</li>
      <li>Pare-feu applicatif (WAF) actif sur l'ensemble des points d'entrée de l'application.</li>
      <li>Surveillance réseau 24h/24, 7j/7 avec alertes automatisées en cas d'anomalie.</li>
      <li>Séparation stricte des environnements de production, de staging et de développement.</li>
    </ul>

    <h2>3. Chiffrement des données</h2>
    <ul>
      <li>Toutes les communications entre le navigateur et nos serveurs sont chiffrées via <strong>TLS 1.2 minimum</strong> (TLS 1.3 recommandé).</li>
      <li>Les données au repos sont chiffrées avec <strong>AES-256</strong>.</li>
      <li>Les mots de passe sont hachés avec <strong>bcrypt</strong> (facteur de coût ≥ 12) — ils ne sont jamais stockés en clair.</li>
      <li>Les sauvegardes sont chiffrées avant transfert et stockage.</li>
    </ul>

    <h2>4. Contrôle d'accès et authentification</h2>
    <ul>
      <li>Authentification multi-facteurs (MFA) disponible et recommandée pour tous les comptes.</li>
      <li>Gestion des rôles et permissions granulaire par entreprise (administrateur, gestionnaire, comptable, lecture seule).</li>
      <li>Sessions sécurisées avec expiration automatique après 30 minutes d'inactivité.</li>
      <li>Journalisation de toutes les connexions et actions sensibles (audit log).</li>
      <li>Révocation immédiate des accès lors de la résiliation ou du changement de statut d'un utilisateur.</li>
    </ul>

    <h2>5. Développement sécurisé (DevSecOps)</h2>
    <ul>
      <li>Revues de code obligatoires intégrant des contrôles de sécurité avant tout déploiement.</li>
      <li>Analyse statique de code (SAST) automatisée à chaque pull request.</li>
      <li>Dépendances tierces auditées régulièrement ; mises à jour de sécurité appliquées sous 72 heures.</li>
      <li>Tests de pénétration (pentest) réalisés au minimum une fois par an par un prestataire tiers indépendant.</li>
      <li>Protection contre les vulnérabilités OWASP Top 10 (injection SQL, XSS, CSRF, etc.).</li>
    </ul>

    <h2>6. Gestion des incidents de sécurité</h2>
    <p>
      En cas d'incident de sécurité avéré affectant des données personnelles, IBIG Soft SARL s'engage à :
    </p>
    <ul>
      <li>Notifier les clients concernés dans un délai de <strong>72 heures</strong> après constatation.</li>
      <li>Documenter l'incident, son périmètre et les mesures correctives prises.</li>
      <li>Informer les autorités compétentes conformément à la législation ivoirienne en vigueur.</li>
      <li>Mettre en œuvre un plan de remédiation pour éviter la récurrence.</li>
    </ul>

    <h2>7. Sauvegardes et reprise d'activité</h2>
    <p>
      Des sauvegardes complètes des bases de données sont effectuées quotidiennement et conservées
      pendant 30 jours. La restauration peut être effectuée sur demande dans un délai de 4 heures ouvrées
      pour les incidents critiques. Voir également notre <a href="/legal/plan-continuite">Plan de continuité d'activité</a>.
    </p>

    <h2>8. Responsabilités des utilisateurs</h2>
    <p>Les clients et leurs utilisateurs s'engagent à :</p>
    <ul>
      <li>Choisir des mots de passe robustes (minimum 12 caractères, mixte).</li>
      <li>Ne pas partager leurs identifiants de connexion.</li>
      <li>Signaler immédiatement tout accès suspect à <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a>.</li>
      <li>Maintenir leurs appareils à jour et protégés par un antivirus actif.</li>
    </ul>

    <h2>9. Contact sécurité</h2>
    <p>
      Pour signaler une vulnérabilité ou un incident de sécurité :
      <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a> — objet <em>« Sécurité — Signalement »</em>.
      IBIG Soft SARL encourage la divulgation responsable et s'engage à traiter tout signalement sérieux.
    </p>

  </LegalLayout>
</template>
