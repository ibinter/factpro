<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>

<template>
  <LegalLayout title="Mentions légales" :can-login="canLogin" :can-register="canRegister">

    <h2>1. Éditeur du logiciel</h2>
    <p>
      <strong>Raison sociale :</strong> IBIG SARL – Intermark Business International Group<br>
      <strong>Marque commerciale :</strong> IBIG Soft<br>
      <strong>Produit :</strong> IBIG FactPro<br>
      <strong>Siège social :</strong> Abidjan, Côte d'Ivoire<br>
      <strong>Téléphone :</strong> +225 27 22 27 60 14 &nbsp;/&nbsp; +225 05 55 05 99 01<br>
      <strong>Email :</strong> <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a><br>
      <strong>Site web :</strong> <a href="https://ibigsoft.com" target="_blank" rel="noopener">ibigsoft.com</a>
    </p>

    <h2>2. Directeur de la publication</h2>
    <p>Le directeur de la publication est le représentant légal de IBIG SARL en exercice.</p>

    <h2>3. Hébergement</h2>
    <p>
      Le logiciel IBIG FactPro est hébergé sur des serveurs sécurisés localisés en Europe.
      Les données sont stockées et traitées conformément aux exigences légales applicables
      en Côte d'Ivoire et à l'OHADA.
    </p>

    <h2>4. Propriété intellectuelle</h2>
    <p>
      L'ensemble des éléments constituant le logiciel IBIG FactPro — code source, interfaces,
      logos, textes, documentation, modèles de documents, bases de données, assistant IA SARA,
      identité visuelle — est la propriété exclusive de IBIG SARL.
    </p>
    <ul>
      <li>Toute reproduction non autorisée est strictement interdite.</li>
      <li>Toute imitation partielle ou totale engage la responsabilité de son auteur.</li>
      <li>Les marques IBIG Soft, IBIG FactPro et IBIG PARTNERS sont protégées.</li>
    </ul>

    <h2>5. Protection des données personnelles</h2>
    <p>
      Le traitement des données personnelles des utilisateurs est décrit dans notre
      <a href="/legal/confidentialite">Politique de confidentialité</a>.
      Conformément aux législations applicables, vous disposez de droits d'accès,
      de rectification et de suppression de vos données.
    </p>

    <h2>6. Limitation de responsabilité</h2>
    <p>
      IBIG SARL s'efforce de maintenir IBIG FactPro accessible et fiable en permanence.
      Toutefois, IBIG SARL ne saurait être tenu responsable :
    </p>
    <ul>
      <li>Des interruptions temporaires de service pour maintenance ou incident technique.</li>
      <li>Des pertes de données imputables à une mauvaise utilisation par l'utilisateur.</li>
      <li>Des dommages indirects résultant de l'utilisation ou de l'impossibilité d'utiliser le logiciel.</li>
    </ul>

    <h2>7. Droit applicable et juridiction</h2>
    <p>
      Les présentes mentions légales sont soumises au droit ivoirien et aux dispositions
      de l'OHADA. Tout litige relatif à leur interprétation ou exécution sera soumis
      aux juridictions compétentes d'Abidjan, Côte d'Ivoire,
      sauf disposition impérative contraire.
    </p>

  </LegalLayout>
</template>
