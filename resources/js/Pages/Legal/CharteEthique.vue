<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Charte éthique et RSE" last-updated="23 juillet 2026" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Notre vision</h2>
    <p>
      IBIG Soft SARL — éditeur d'<strong>IBIG FactPro</strong> — est une entreprise technologique
      africaine engagée à contribuer positivement au développement économique et social
      de la zone OHADA. Notre mission va au-delà de la simple fourniture d'un logiciel de facturation :
      nous voulons être un acteur de la modernisation et de la formalisation des entreprises
      africaines, dans le respect des personnes, de l'environnement et des valeurs éthiques.
    </p>

    <h2>2. Éthique des affaires</h2>
    <ul>
      <li><strong>Honnêteté et transparence :</strong> nous communiquons honnêtement sur nos produits, nos tarifs et nos engagements. Pas de frais cachés, pas de fausses promesses.</li>
      <li><strong>Intégrité commerciale :</strong> nous refusons toute pratique de corruption, de pot-de-vin ou de concurrence déloyale.</li>
      <li><strong>Respect des contrats :</strong> nous honorons nos engagements contractuels envers nos clients, partenaires et fournisseurs.</li>
      <li><strong>Confidentialité :</strong> les données de nos clients sont sacrées. Nous ne les monétisons jamais à des fins publicitaires.</li>
    </ul>

    <h2>3. Responsabilité sociale envers nos clients</h2>
    <ul>
      <li>Tarification accessible et adaptée aux réalités économiques des PME africaines.</li>
      <li>Support client attentif, humain et réactif, disponible en français.</li>
      <li>Formation et accompagnement à la prise en main du logiciel pour réduire la fracture numérique.</li>
      <li>Programme IBIG Partners favorisant l'émergence de revendeurs et intégrateurs locaux.</li>
      <li>Politique de remboursement équitable et transparente.</li>
    </ul>

    <h2>4. Responsabilité envers nos collaborateurs</h2>
    <ul>
      <li>Égalité de traitement et de rémunération, indépendamment du genre, de l'origine ethnique ou de la religion.</li>
      <li>Environnement de travail sain, respectueux et inclusif.</li>
      <li>Formation continue et développement des compétences de nos équipes.</li>
      <li>Refus du travail forcé, du travail des enfants et de toute forme d'exploitation.</li>
      <li>Dialogue social ouvert et participation des équipes aux décisions qui les concernent.</li>
    </ul>

    <h2>5. Responsabilité environnementale</h2>
    <ul>
      <li>Choix de prestataires d'hébergement engagés dans des démarches d'efficacité énergétique et d'énergie renouvelable.</li>
      <li>Réduction des impressions papier grâce à la dématérialisation des documents (factures électroniques, devis numériques).</li>
      <li>Optimisation continue du code pour réduire la consommation énergétique des serveurs.</li>
      <li>Sensibilisation de nos clients à la facturation électronique comme alternative au papier.</li>
    </ul>

    <h2>6. Lutte contre la corruption et la fraude</h2>
    <p>
      IBIG Soft SARL applique une politique de tolérance zéro envers la corruption sous toutes ses formes.
      Nos outils intègrent des fonctionnalités de traçabilité et d'audit pour aider nos clients
      à maintenir une comptabilité intègre et conforme aux exigences légales OHADA.
      Nous refusons d'intégrer des fonctionnalités qui faciliteraient la fraude fiscale
      ou le blanchiment de capitaux.
    </p>

    <h2>7. Inclusion numérique en Afrique</h2>
    <p>
      Nous croyons que la transformation numérique doit profiter à tous. À ce titre :
    </p>
    <ul>
      <li>IBIG FactPro est conçu pour fonctionner avec des connexions Internet de faible débit.</li>
      <li>Une interface mobile adaptée permet aux entrepreneurs sans ordinateur fixe d'accéder au service.</li>
      <li>Des partenariats avec des incubateurs et associations d'entrepreneurs sont développés pour offrir des accès subventionnés.</li>
    </ul>

    <h2>8. Signalement éthique</h2>
    <p>
      Tout collaborateur, client ou partenaire peut signaler de manière confidentielle
      un comportement contraire à la présente charte à :
      <a href="mailto:contact@ibigsoft.com">contact@ibigsoft.com</a> — objet <em>« Signalement éthique »</em>.
      Les signalements sont traités avec la plus stricte confidentialité et sans représailles.
    </p>

    <h2>9. Révision et engagement</h2>
    <p>
      La présente charte est révisée annuellement et soumise à l'approbation de la direction d'IBIG Soft SARL.
      Elle engage l'ensemble de nos collaborateurs, dirigeants et partenaires commerciaux.
    </p>

  </LegalLayout>
</template>
