<script setup>
import LegalLayout from './Layout.vue';
defineProps({ canLogin: Boolean, canRegister: Boolean });
</script>
<template>
  <LegalLayout title="Mentions légales" :can-login="canLogin" :can-register="canRegister">
    <h2>Éditeur du logiciel</h2>
    <p><strong>Raison sociale :</strong> IBIG SARL – Intermark Business International Group<br>
    <strong>Marque commerciale :</strong> IBIG Soft<br>
    <strong>Produit :</strong> IBIG FactPro<br>
    <strong>Adresse :</strong> Abidjan, Côte d'Ivoire<br>
    <strong>Téléphone :</strong> +225 27 22 27 60 14 / +225 05 55 05 99 01<br>
    <strong>Email :</strong> <a href="mailto:factpro@ibigsoft.com">factpro@ibigsoft.com</a><br>
    <strong>Site :</strong> <a href="https://ibigsoft.com" target="_blank">https://ibigsoft.com</a></p>

    <h2>Directeur de la publication</h2>
    <p>Le directeur de la publication est le représentant légal de IBIG SARL.</p>

    <h2>Hébergement</h2>
    <p>Le logiciel IBIG FactPro est hébergé sur des serveurs sécurisés. Les données sont stockées conformément aux exigences légales en vigueur.</p>

    <h2>Propriété intellectuelle</h2>
    <p>L'ensemble des éléments constituant le logiciel IBIG FactPro (code source, interfaces, logos, textes, documentation, modèles, bases de données, assistants IA, identité visuelle) est la propriété exclusive de IBIG SARL. Toute reproduction, imitation, copie ou utilisation non autorisée est strictement interdite.</p>

    <h2>Protection des données</h2>
    <p>Le traitement des données personnelles est décrit dans notre <a href="/legal/confidentialite">Politique de confidentialité</a>. Conformément aux législations applicables, vous disposez de droits sur vos données personnelles.</p>

    <h2>Responsabilité</h2>
    <p>IBIG SARL s'efforce de maintenir le logiciel IBIG FactPro accessible et fiable. Toutefois, IBIG SARL ne saurait être tenu responsable des interruptions temporaires de service, des pertes de données imputables à l'utilisateur, ou des dommages indirects résultant de l'utilisation du logiciel.</p>

    <h2>Droit applicable</h2>
    <p>Les présentes mentions légales sont soumises au droit ivoirien. Tout litige sera soumis aux juridictions compétentes d'Abidjan, Côte d'Ivoire, sauf disposition impérative contraire.</p>
  </LegalLayout>
</template>
