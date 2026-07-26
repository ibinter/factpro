<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

const props = defineProps({
    page: { type: String, required: true },
    slug: { type: String, required: true },
    year: { type: Number, required: true },
});

const UPDATE_DATE = '26 juillet 2026';
const EDITOR      = 'IBIG SARL – Intermark Business International Group';
const BRAND       = 'IBIG Soft';
const PRODUCT     = 'IBIG FactPro';
const SITE        = 'factpro.ibigsoft.com';
const EMAIL_LEGAL = 'legal@ibigsoft.com';
const EMAIL_SUP   = 'support@ibigsoft.com';
const TEL1        = '+225 27 22 27 60 14';
const TEL2        = '+225 05 55 05 99 01';
const WA          = '+225 07 78 88 25 92';
const COUNTRY     = 'Côte d\'Ivoire';

const otherPages = [
    { slug: 'mentions',               label: 'Mentions légales' },
    { slug: 'cgu',                    label: 'CGU' },
    { slug: 'conditions-commerciales',label: 'Conditions commerciales' },
    { slug: 'contrat-licence',        label: 'Contrat de licence' },
    { slug: 'confidentialite',        label: 'Confidentialité' },
    { slug: 'cookies',                label: 'Cookies' },
    { slug: 'sauvegarde',             label: 'Sauvegarde' },
    { slug: 'support',                label: 'Support' },
    { slug: 'resiliation',            label: 'Résiliation' },
    { slug: 'remboursement',          label: 'Remboursement' },
    { slug: 'traitement-donnees',     label: 'Traitement des données' },
    { slug: 'pi',                     label: 'Propriété intellectuelle' },
    { slug: 'propriete-marque',       label: 'Marque' },
    { slug: 'essai',                  label: 'Programme d\'essai' },
    { slug: 'sara',                   label: 'SARA' },
    { slug: 'ia',                     label: 'IA & Responsabilité' },
    { slug: 'suppression-compte',     label: 'Suppression du compte' },
    { slug: 'reclamations',           label: 'Réclamations' },
];
</script>

<template>
    <Head :title="page + ' — IBIG FactPro'">
        <meta name="description" :content="'Page légale ' + page + ' de ' + 'IBIG FactPro, éditeur IBIG SARL.'">
    </Head>

    <PublicNav />

    <!-- Hero -->
    <section style="background:#002D5B;color:#fff;padding:3.5rem 1.5rem 2.5rem">
        <div style="max-width:900px;margin:0 auto">
            <p style="color:#D4A017;font-size:.85rem;font-weight:600;letter-spacing:.05em;text-transform:uppercase;margin-bottom:.5rem">
                Informations légales
            </p>
            <h1 style="font-size:2rem;font-weight:800;margin:0 0 .75rem">{{ page }}</h1>
            <p style="font-size:.9rem;color:rgba(255,255,255,.65)">Dernière mise à jour : {{ UPDATE_DATE }}</p>
        </div>
    </section>

    <!-- Content -->
    <main style="background:#f8fafc;min-height:60vh;padding:3rem 1.5rem">
        <div style="max-width:900px;margin:0 auto;background:#fff;border-radius:12px;padding:2.5rem 2rem;box-shadow:0 1px 6px rgba(0,0,0,.06)">

            <!-- ===== MENTIONS LÉGALES ===== -->
            <template v-if="slug === 'mentions'">
                <h2>Éditeur du site</h2>
                <p><strong>Raison sociale :</strong> IBIG SARL – Intermark Business International Group<br>
                <strong>Marque commerciale :</strong> IBIG Soft<br>
                <strong>Forme juridique :</strong> Société à Responsabilité Limitée (SARL)<br>
                <strong>Pays d'établissement :</strong> Côte d'Ivoire<br>
                <strong>Site web :</strong> <a href="https://factpro.ibigsoft.com">factpro.ibigsoft.com</a><br>
                <strong>Email :</strong> <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a><br>
                <strong>Téléphone :</strong> +225 27 22 27 60 14 / +225 05 55 05 99 01</p>

                <h2>Directeur de la publication</h2>
                <p>Le directeur de la publication est le représentant légal d'IBIG SARL, joignable à l'adresse <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a>.</p>

                <h2>Hébergement</h2>
                <p>IBIG FactPro est hébergé sur des infrastructures cloud sécurisées situées en Europe et en Afrique. Pour toute demande relative à l'hébergement, contactez <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a>.</p>

                <h2>Propriété intellectuelle</h2>
                <p>L'ensemble des contenus présents sur le site <strong>factpro.ibigsoft.com</strong> (textes, graphismes, logos, icônes, images, clips audio ou vidéo, données, logiciels) sont la propriété exclusive d'IBIG SARL ou de ses partenaires et sont protégés par les lois ivoiriennes et internationales relatives à la propriété intellectuelle. Toute reproduction, représentation, modification ou exploitation totale ou partielle de ces contenus, sans autorisation écrite préalable d'IBIG SARL, est strictement interdite.</p>

                <h2>Données personnelles</h2>
                <p>IBIG SARL traite les données personnelles de ses utilisateurs conformément à sa <a href="/legal/confidentialite">Politique de confidentialité</a>. En utilisant IBIG FactPro, vous acceptez les conditions de traitement décrites dans cette politique.</p>

                <h2>Loi applicable</h2>
                <p>Les présentes mentions légales sont régies par le droit ivoirien. Tout litige relatif à l'interprétation ou à l'exécution des présentes sera soumis aux tribunaux compétents d'Abidjan, Côte d'Ivoire.</p>

                <h2>Contact</h2>
                <p>Pour toute question d'ordre légal, veuillez écrire à : <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a></p>
            </template>

            <!-- ===== CGU ===== -->
            <template v-else-if="slug === 'cgu'">
                <h2>1. Objet et champ d'application</h2>
                <p>Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation du logiciel SaaS <strong>IBIG FactPro</strong>, édité par IBIG SARL – Intermark Business International Group, ci-après dénommé « l'Éditeur ». En créant un compte ou en utilisant IBIG FactPro, l'utilisateur accepte sans réserve les présentes CGU.</p>

                <h2>2. Accès au service</h2>
                <p>L'accès à IBIG FactPro est conditionné à la création d'un compte utilisateur. L'utilisateur doit fournir des informations exactes, complètes et à jour. Tout accès non autorisé est strictement interdit. L'Éditeur se réserve le droit de suspendre ou résilier tout compte en cas de violation des présentes CGU.</p>

                <h2>3. Utilisation acceptable</h2>
                <p>L'utilisateur s'engage à utiliser IBIG FactPro uniquement à des fins légales, dans le respect de la réglementation applicable en Côte d'Ivoire et dans son pays d'établissement. Il est notamment interdit de : tenter de contourner les mesures de sécurité, reproduire ou revendre le logiciel, utiliser des scripts automatisés non autorisés, ou porter atteinte à l'intégrité des données.</p>

                <h2>4. Responsabilités de l'utilisateur</h2>
                <p>L'utilisateur est seul responsable des données qu'il saisit dans IBIG FactPro, de la sécurité de ses identifiants de connexion, de la conformité fiscale et comptable de ses opérations, et du respect des droits des tiers (clients, fournisseurs).</p>

                <h2>5. Responsabilités de l'Éditeur</h2>
                <p>IBIG SARL s'engage à mettre en œuvre tous les moyens raisonnables pour assurer la disponibilité et la sécurité du service. L'Éditeur ne saurait être tenu responsable des interruptions liées à des opérations de maintenance, à des cas de force majeure, ou à des défaillances de réseaux tiers.</p>

                <h2>6. Propriété intellectuelle</h2>
                <p>IBIG FactPro, ses composants, son code source, ses interfaces et sa documentation sont la propriété exclusive d'IBIG SARL. L'utilisation du logiciel ne confère à l'utilisateur aucun droit de propriété sur ces éléments.</p>

                <h2>7. Modification des CGU</h2>
                <p>L'Éditeur se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs seront informés par email ou notification in-app. La poursuite de l'utilisation du service après modification vaut acceptation des nouvelles conditions.</p>

                <h2>8. Loi applicable et juridiction</h2>
                <p>Les présentes CGU sont soumises au droit ivoirien. En cas de litige, les parties s'efforceront de trouver une solution amiable. À défaut, le litige sera soumis aux tribunaux compétents d'Abidjan.</p>
            </template>

            <!-- ===== CONDITIONS COMMERCIALES ===== -->
            <template v-else-if="slug === 'conditions-commerciales'">
                <h2>1. Tarification et abonnements</h2>
                <p>IBIG FactPro est proposé sous forme d'abonnement mensuel ou annuel. Les tarifs en vigueur sont affichés sur la page <a href="/pricing">Tarifs</a>. L'Éditeur se réserve le droit de modifier ses tarifs avec un préavis de 30 jours communiqué par email.</p>

                <h2>2. Facturation</h2>
                <p>La facturation est effectuée en début de période d'abonnement (mensuelle ou annuelle). Les paiements sont traités via les moyens de paiement disponibles sur la plateforme : Mobile Money (Orange Money, Wave, MTN MoMo, Moov Money), virement bancaire, carte bancaire. Les factures sont disponibles dans l'espace client.</p>

                <h2>3. Essai gratuit</h2>
                <p>Un essai gratuit de 14 jours est proposé sans engagement ni carte bancaire. À l'issue de la période d'essai, l'accès est suspendu si aucun abonnement n'est souscrit. Les données saisies pendant l'essai sont conservées pendant 30 jours supplémentaires.</p>

                <h2>4. Renouvellement automatique</h2>
                <p>Les abonnements sont renouvelés automatiquement à leur échéance, sauf résiliation effectuée au moins 48 heures avant la date de renouvellement via l'espace client ou en contactant support@ibigsoft.com.</p>

                <h2>5. Remboursements</h2>
                <p>Les conditions de remboursement sont détaillées dans la <a href="/legal/remboursement">Politique de remboursement</a>. En règle générale, les abonnements annuels peuvent faire l'objet d'un remboursement au prorata dans les 30 jours suivant la souscription.</p>

                <h2>6. Modification des offres</h2>
                <p>L'Éditeur se réserve le droit de modifier les fonctionnalités incluses dans chaque offre. Toute suppression de fonctionnalité majeure sera notifiée avec un préavis de 60 jours. L'utilisateur pourra alors résilier son abonnement sans frais.</p>

                <h2>7. Contact commercial</h2>
                <p>Pour toute question commerciale, contactez notre équipe à <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a> ou au {{ TEL1 }}.</p>
            </template>

            <!-- ===== CONTRAT DE LICENCE ===== -->
            <template v-else-if="slug === 'contrat-licence'">
                <h2>1. Objet de la licence</h2>
                <p>IBIG SARL octroie à l'abonné une licence d'utilisation non exclusive, non transférable et révocable du logiciel <strong>IBIG FactPro</strong>, pour la durée de l'abonnement souscrit. Cette licence autorise uniquement l'utilisation du logiciel en mode SaaS (Software as a Service), via un navigateur web ou l'API officielle.</p>

                <h2>2. Restrictions</h2>
                <p>Il est strictement interdit à l'abonné de : copier, modifier, décompiler, désassembler ou faire de l'ingénierie inverse sur le logiciel ; louer, céder, sous-licencier ou transférer la licence à un tiers ; supprimer ou modifier les mentions de propriété intellectuelle ; accéder au code source ou aux bases de données sous-jacentes.</p>

                <h2>3. Durée</h2>
                <p>La présente licence entre en vigueur à la date d'activation du compte et prend fin à l'expiration ou à la résiliation de l'abonnement. En cas de résiliation pour violation des termes, la licence est révoquée immédiatement et sans préavis.</p>

                <h2>4. Mises à jour</h2>
                <p>Les mises à jour du logiciel IBIG FactPro sont incluses dans l'abonnement actif. IBIG SARL se réserve le droit d'apporter toute modification technique ou fonctionnelle jugée nécessaire, sans obligation d'en informer préalablement l'abonné, sauf modification substantielle affectant les fonctionnalités essentielles.</p>

                <h2>5. Garantie limitée</h2>
                <p>Le logiciel est fourni « en l'état ». IBIG SARL garantit un niveau de service conforme à la <a href="/legal/support">Politique de support</a>, sans garantir que le logiciel sera exempt de bugs ou d'interruptions. La responsabilité de l'Éditeur est limitée au montant des sommes versées par l'abonné au cours des 12 derniers mois.</p>

                <h2>6. Droit applicable</h2>
                <p>La présente licence est régie par le droit ivoirien. Tout différend sera soumis à la juridiction des tribunaux d'Abidjan.</p>
            </template>

            <!-- ===== CONFIDENTIALITÉ ===== -->
            <template v-else-if="slug === 'confidentialite'">
                <h2>1. Responsable du traitement</h2>
                <p>IBIG SARL – Intermark Business International Group, éditeur de IBIG FactPro, est responsable du traitement de vos données personnelles. Contact : <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a>.</p>

                <h2>2. Données collectées</h2>
                <p>Nous collectons les données suivantes : informations d'identité (nom, prénom, dénomination sociale), coordonnées (email, téléphone, adresse), données de connexion et d'utilisation, données de facturation (sans stockage des données bancaires complètes), et données relatives à vos clients et transactions commerciales saisies dans le logiciel.</p>

                <h2>3. Finalités du traitement</h2>
                <p>Vos données sont traitées pour : la fourniture et l'amélioration du service IBIG FactPro, la gestion de votre compte et de votre abonnement, la facturation et le suivi des paiements, l'envoi de communications relatives au service (notifications, mises à jour, sécurité), le support technique, et la conformité avec nos obligations légales.</p>

                <h2>4. Base légale</h2>
                <p>Le traitement de vos données repose sur : l'exécution du contrat d'abonnement, votre consentement pour les communications marketing (révocable à tout moment), nos obligations légales (notamment en matière fiscale et comptable), et notre intérêt légitime à améliorer nos services.</p>

                <h2>5. Partage des données</h2>
                <p>Nous ne vendons jamais vos données. Nous pouvons partager vos données avec des sous-traitants techniques (hébergeurs, prestataires de paiement) dans le cadre de la fourniture du service, et avec les autorités compétentes sur réquisition légale. Tout sous-traitant est contractuellement tenu aux mêmes obligations de protection.</p>

                <h2>6. Conservation</h2>
                <p>Vos données sont conservées pendant la durée de votre abonnement, augmentée d'une période de 5 ans pour respecter nos obligations légales et comptables. Les données de vos clients sont conservées tant que votre compte est actif.</p>

                <h2>7. Vos droits</h2>
                <p>Vous disposez des droits d'accès, de rectification, d'effacement, de portabilité, de limitation et d'opposition au traitement de vos données. Pour exercer ces droits, contactez <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a>.</p>

                <h2>8. Sécurité</h2>
                <p>Nous mettons en œuvre des mesures techniques et organisationnelles appropriées pour protéger vos données : chiffrement TLS en transit, chiffrement au repos, accès restreint aux données, journalisation des accès, et sauvegardes régulières.</p>
            </template>

            <!-- ===== COOKIES ===== -->
            <template v-else-if="slug === 'cookies'">
                <h2>1. Qu'est-ce qu'un cookie ?</h2>
                <p>Un cookie est un petit fichier texte déposé sur votre terminal (ordinateur, smartphone, tablette) lors de votre visite sur notre site. Il permet de mémoriser des informations relatives à votre navigation afin d'améliorer votre expérience.</p>

                <h2>2. Cookies utilisés par IBIG FactPro</h2>
                <p><strong>Cookies strictement nécessaires :</strong> ils garantissent le bon fonctionnement de la plateforme (session, authentification, sécurité CSRF). Ces cookies ne peuvent pas être désactivés.</p>
                <p><strong>Cookies de préférences :</strong> ils mémorisent vos préférences d'affichage (langue, thème, paramètres). Vous pouvez les refuser, mais certaines fonctionnalités seront moins pratiques.</p>
                <p><strong>Cookies analytiques :</strong> ils nous permettent de mesurer l'audience et d'améliorer nos services (pages visitées, durée de session, erreurs). Ces données sont anonymisées.</p>

                <h2>3. Cookies tiers</h2>
                <p>Certaines fonctionnalités peuvent faire appel à des services tiers (ex. : prestataires de paiement Mobile Money) qui peuvent déposer leurs propres cookies soumis à leurs propres politiques de confidentialité.</p>

                <h2>4. Gestion des cookies</h2>
                <p>Vous pouvez configurer votre navigateur pour refuser les cookies ou être alerté lors de leur dépôt. La suppression des cookies nécessaires peut perturber l'utilisation de la plateforme. Pour paramétrer vos cookies, consultez l'aide de votre navigateur (Chrome, Firefox, Safari, Edge).</p>

                <h2>5. Durée de conservation</h2>
                <p>Les cookies de session expirent à la fermeture du navigateur. Les cookies persistants ont une durée de vie maximale de 13 mois.</p>

                <h2>6. Contact</h2>
                <p>Pour toute question relative aux cookies : <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a></p>
            </template>

            <!-- ===== SAUVEGARDE ===== -->
            <template v-else-if="slug === 'sauvegarde'">
                <h2>1. Engagement de sauvegarde</h2>
                <p>IBIG SARL s'engage à mettre en œuvre des procédures de sauvegarde régulières et automatiques pour protéger les données de ses clients hébergées sur IBIG FactPro.</p>

                <h2>2. Fréquence des sauvegardes</h2>
                <p>Les données sont sauvegardées selon le calendrier suivant : sauvegarde complète quotidienne (conservée 30 jours), sauvegarde hebdomadaire (conservée 3 mois), sauvegarde mensuelle (conservée 12 mois). Les sauvegardes sont chiffrées et stockées sur des serveurs distincts du serveur de production.</p>

                <h2>3. Restauration</h2>
                <p>En cas d'incident majeur entraînant une perte de données, IBIG SARL s'engage à restaurer les données dans un délai de 24 à 72 heures ouvrées à partir de la dernière sauvegarde disponible. Les demandes de restauration doivent être adressées à <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a>.</p>

                <h2>4. Responsabilité de l'utilisateur</h2>
                <p>L'utilisateur est encouragé à effectuer ses propres exports de données régulièrement via les fonctions d'export disponibles dans IBIG FactPro (PDF, Excel, JSON). IBIG SARL ne saurait être tenue responsable de pertes de données résultant de suppressions volontaires effectuées par l'utilisateur.</p>

                <h2>5. Limites</h2>
                <p>La politique de sauvegarde couvre les données hébergées sur l'infrastructure IBIG FactPro. Elle ne couvre pas les fichiers stockés localement sur les postes des utilisateurs ni les données supprimées intentionnellement depuis plus de 30 jours.</p>
            </template>

            <!-- ===== SUPPORT ===== -->
            <template v-else-if="slug === 'support'">
                <h2>1. Canaux de support</h2>
                <p>IBIG SARL propose plusieurs canaux pour accompagner ses utilisateurs :<br>
                <strong>Email :</strong> <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a><br>
                <strong>Téléphone :</strong> {{ TEL1 }} / {{ TEL2 }}<br>
                <strong>WhatsApp :</strong> {{ WA }}<br>
                <strong>Portail :</strong> Centre d'aide intégré dans IBIG FactPro</p>

                <h2>2. Horaires</h2>
                <p>Notre équipe est disponible du lundi au vendredi de 8h00 à 18h00 (heure d'Abidjan, GMT). En dehors de ces horaires, les demandes urgentes peuvent être envoyées par WhatsApp et seront traitées au prochain jour ouvrable.</p>

                <h2>3. Délais de réponse</h2>
                <p><strong>Incident bloquant (service inaccessible) :</strong> réponse sous 4 heures ouvrées.<br>
                <strong>Bug fonctionnel majeur :</strong> réponse sous 24 heures ouvrées.<br>
                <strong>Question ou demande d'assistance :</strong> réponse sous 48 heures ouvrées.</p>

                <h2>4. Base de connaissances</h2>
                <p>Une base de connaissances (FAQ, tutoriels vidéo, guides PDF) est disponible dans le centre d'aide de la plateforme. Nous vous encourageons à la consulter avant de soumettre une demande de support.</p>

                <h2>5. Politique de mise à jour</h2>
                <p>Les corrections de bugs critiques sont déployées dans les 48 heures suivant leur identification. Les mises à jour mineures sont regroupées en releases hebdomadaires. Les mises à jour majeures sont annoncées avec un préavis de 7 jours.</p>
            </template>

            <!-- ===== RÉSILIATION ===== -->
            <template v-else-if="slug === 'resiliation'">
                <h2>1. Résiliation à l'initiative de l'utilisateur</h2>
                <p>L'utilisateur peut résilier son abonnement à tout moment depuis son espace client (section « Abonnement ») ou en envoyant une demande à <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a>. La résiliation prend effet à la fin de la période d'abonnement en cours. Aucun remboursement au prorata n'est accordé pour les abonnements mensuels.</p>

                <h2>2. Résiliation par l'Éditeur</h2>
                <p>IBIG SARL peut résilier un compte sans préavis en cas de : violation grave des CGU, comportement frauduleux, tentative de piratage ou d'accès non autorisé, non-paiement après relance. Dans ce cas, aucun remboursement n'est dû.</p>

                <h2>3. Conséquences de la résiliation</h2>
                <p>À la date effective de résiliation, l'accès au service est suspendu. Les données de l'utilisateur sont conservées pendant 30 jours supplémentaires pour permettre un export. Passé ce délai, les données sont supprimées définitivement.</p>

                <h2>4. Export des données avant résiliation</h2>
                <p>Avant de résilier, nous vous recommandons d'exporter l'ensemble de vos données (factures, clients, produits) via les fonctions d'export de la plateforme. IBIG SARL ne saurait être responsable d'une perte de données suite à une résiliation sans export préalable.</p>

                <h2>5. Contact</h2>
                <p>Pour toute demande de résiliation : <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a> ou {{ TEL1 }}.</p>
            </template>

            <!-- ===== REMBOURSEMENT ===== -->
            <template v-else-if="slug === 'remboursement'">
                <h2>1. Politique générale</h2>
                <p>IBIG SARL s'engage à offrir un service de qualité. Si vous n'êtes pas satisfait dans les conditions décrites ci-dessous, nous examinerons votre demande de remboursement avec bienveillance.</p>

                <h2>2. Abonnements annuels</h2>
                <p>Pour les abonnements annuels, un remboursement au prorata des mois restants peut être accordé si la demande est formulée dans les 30 premiers jours suivant la souscription. Au-delà de ce délai, aucun remboursement n'est accordé sauf cas exceptionnel apprécié par notre équipe.</p>

                <h2>3. Abonnements mensuels</h2>
                <p>Les abonnements mensuels ne donnent pas lieu à remboursement. En cas d'insatisfaction, vous pouvez résilier avant la date de renouvellement pour éviter le prélèvement du mois suivant.</p>

                <h2>4. Cas éligibles au remboursement</h2>
                <p>Sont éligibles : défaillance technique grave du service pendant plus de 72 heures consécutives non résolue par IBIG SARL, double facturation avérée, facturation erronée. Les remboursements sont traités sous 10 jours ouvrés via le même canal de paiement utilisé lors de la souscription.</p>

                <h2>5. Demande de remboursement</h2>
                <p>Toute demande doit être adressée à <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a> en précisant le numéro d'abonnement, la date et le montant de la transaction, et le motif détaillé de la demande.</p>
            </template>

            <!-- ===== TRAITEMENT DES DONNÉES ===== -->
            <template v-else-if="slug === 'traitement-donnees'">
                <h2>1. Cadre légal</h2>
                <p>IBIG SARL traite les données personnelles de ses utilisateurs en conformité avec la législation ivoirienne en vigueur, notamment la loi relative à la protection des données à caractère personnel, ainsi que les standards internationaux reconnus (RGPD pour les utilisateurs européens).</p>

                <h2>2. Catégories de données traitées</h2>
                <p><strong>Données d'identification :</strong> nom, prénom, raison sociale, numéro de contribuable.<br>
                <strong>Données de contact :</strong> adresse email, numéro de téléphone, adresse postale.<br>
                <strong>Données financières :</strong> informations de facturation, historique des paiements.<br>
                <strong>Données opérationnelles :</strong> factures, devis, bons de livraison, fiches clients et produits saisis dans la plateforme.<br>
                <strong>Données techniques :</strong> logs de connexion, adresses IP, type de navigateur.</p>

                <h2>3. Sous-traitants</h2>
                <p>Nous faisons appel à des sous-traitants de confiance pour certaines opérations techniques (hébergement, paiement, emails transactionnels). Chaque sous-traitant est lié par un contrat de traitement de données garantissant un niveau de protection équivalent au nôtre.</p>

                <h2>4. Transferts internationaux</h2>
                <p>Vos données peuvent être hébergées sur des serveurs situés hors de Côte d'Ivoire. Dans ce cas, nous nous assurons que les transferts sont encadrés par des garanties appropriées (clauses contractuelles types, certifications).</p>

                <h2>5. Droits des personnes concernées</h2>
                <p>Toute personne dont les données sont traitées par IBIG SARL dispose des droits suivants : droit d'accès, de rectification, d'effacement, de portabilité, de limitation et d'opposition. Pour exercer ces droits : <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a>. Délai de réponse : 30 jours maximum.</p>

                <h2>6. Durées de conservation</h2>
                <p>Les données de compte : durée d'abonnement + 5 ans. Les données de facturation : 10 ans (obligations comptables). Les logs techniques : 12 mois. Les données effacées à la demande de l'utilisateur sont supprimées dans les 30 jours suivant la demande.</p>
            </template>

            <!-- ===== PROPRIÉTÉ INTELLECTUELLE ===== -->
            <template v-else-if="slug === 'pi'">
                <h2>1. Droits d'auteur</h2>
                <p>IBIG FactPro, son code source, ses interfaces, ses algorithmes, ses bases de données, sa documentation et l'ensemble de ses composants sont protégés par le droit d'auteur ivoirien et les conventions internationales applicables. Ces éléments sont la propriété exclusive d'IBIG SARL.</p>

                <h2>2. Marques et logos</h2>
                <p>Les dénominations « IBIG », « IBIG Soft », « IBIG FactPro », « SARA » ainsi que les logos associés sont des marques déposées ou en cours de dépôt. Toute utilisation sans autorisation écrite préalable d'IBIG SARL est interdite.</p>

                <h2>3. Licence d'utilisation</h2>
                <p>L'abonnement à IBIG FactPro confère uniquement une licence d'utilisation telle que définie dans le <a href="/legal/contrat-licence">Contrat de licence utilisateur</a>. Il ne transfère aucun droit de propriété intellectuelle à l'utilisateur.</p>

                <h2>4. Contenu utilisateur</h2>
                <p>L'utilisateur conserve la pleine propriété de ses données (factures, fichiers clients, produits). En utilisant IBIG FactPro, il accorde à IBIG SARL une licence limitée, non exclusive et révocable pour traiter ces données dans le seul but de fournir le service.</p>

                <h2>5. Signalement de violations</h2>
                <p>Si vous constatez une utilisation non autorisée de nos droits de propriété intellectuelle, veuillez le signaler à <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a>. Nous traiterons votre signalement dans les meilleurs délais.</p>
            </template>

            <!-- ===== PROTECTION DE LA MARQUE ===== -->
            <template v-else-if="slug === 'propriete-marque'">
                <h2>1. Nos marques</h2>
                <p>Les marques « IBIG », « IBIG Soft », « IBIG FactPro », « SARA » et leurs déclinaisons graphiques sont des actifs stratégiques d'IBIG SARL. Elles identifient nos produits et services auprès de nos clients en Côte d'Ivoire et à l'international.</p>

                <h2>2. Utilisations autorisées</h2>
                <p>Les partenaires officiels d'IBIG SARL peuvent utiliser nos marques dans le cadre défini par leur contrat de partenariat. Les journalistes et créateurs de contenu peuvent mentionner nos marques à des fins éditoriales et informatives, sans en faire une utilisation commerciale ou trompeuse.</p>

                <h2>3. Utilisations interdites</h2>
                <p>Il est interdit de : utiliser nos marques dans un nom de domaine, un nom de société ou un nom de produit sans autorisation ; créer des logos ou visuels similaires susceptibles de créer une confusion ; utiliser nos marques de façon à porter atteinte à notre réputation ; associer nos marques à des contenus illicites ou contraires à nos valeurs.</p>

                <h2>4. Signalement d'abus</h2>
                <p>Toute utilisation abusive de nos marques doit être signalée à <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a>. IBIG SARL se réserve le droit d'engager des poursuites judiciaires en cas d'atteinte à ses droits de marque.</p>

                <h2>5. Contact</h2>
                <p>Pour toute demande d'autorisation d'utilisation de nos marques : <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a></p>
            </template>

            <!-- ===== PROGRAMME D'ESSAI ===== -->
            <template v-else-if="slug === 'essai'">
                <h2>1. Description du programme</h2>
                <p>IBIG SARL propose un accès gratuit à IBIG FactPro pendant une période d'essai de <strong>14 jours</strong>, sans carte bancaire requise et sans engagement. Cet essai donne accès à l'ensemble des fonctionnalités de l'offre souscrite.</p>

                <h2>2. Conditions d'éligibilité</h2>
                <p>L'essai gratuit est réservé aux nouveaux utilisateurs n'ayant jamais souscrit à un abonnement IBIG FactPro. Un seul essai par entreprise et par adresse email est autorisé. IBIG SARL se réserve le droit de refuser l'accès à l'essai en cas d'abus constatés.</p>

                <h2>3. Données pendant l'essai</h2>
                <p>Les données saisies pendant la période d'essai sont conservées et restent accessibles si l'utilisateur souscrit un abonnement avant l'expiration. En cas de non-souscription, les données sont conservées 30 jours supplémentaires après l'expiration de l'essai, puis supprimées définitivement.</p>

                <h2>4. Conversion en abonnement payant</h2>
                <p>À tout moment pendant ou après la période d'essai, l'utilisateur peut souscrire un abonnement depuis son espace client. Les données saisies pendant l'essai sont automatiquement transférées vers le compte payant.</p>

                <h2>5. Fin de l'essai</h2>
                <p>À l'expiration de la période d'essai, l'accès aux fonctionnalités est limité. L'utilisateur reçoit des notifications par email pour l'inciter à souscrire. Aucune facturation automatique n'est effectuée sans action volontaire de l'utilisateur.</p>
            </template>

            <!-- ===== SARA ===== -->
            <template v-else-if="slug === 'sara'">
                <h2>1. Présentation de SARA</h2>
                <p><strong>SARA</strong> (Système d'Assistance et de Réponse Automatisée) est l'assistant intelligent intégré à IBIG FactPro. SARA utilise des technologies d'intelligence artificielle pour aider les utilisateurs dans leurs tâches de facturation, de gestion commerciale et de support.</p>

                <h2>2. Fonctionnalités de SARA</h2>
                <p>SARA peut : répondre aux questions relatives à l'utilisation d'IBIG FactPro, suggérer des libellés de produits ou de services, analyser vos données pour vous proposer des insights, générer des brouillons de documents (factures, devis), et orienter vers le support humain pour les demandes complexes.</p>

                <h2>3. Données traitées par SARA</h2>
                <p>Pour fonctionner, SARA accède aux données de votre compte IBIG FactPro (produits, clients, historique de transactions) dans le strict respect de la <a href="/legal/confidentialite">Politique de confidentialité</a>. Vos données ne sont pas utilisées pour entraîner des modèles d'IA tiers sans votre consentement explicite.</p>

                <h2>4. Limitations de SARA</h2>
                <p>SARA est un outil d'aide et non un conseiller fiscal, juridique ou comptable. Les réponses de SARA sont fournies à titre informatif. L'utilisateur reste responsable de la vérification et de la validation des informations générées. Pour les questions réglementaires, consultez un professionnel qualifié.</p>

                <h2>5. Désactivation</h2>
                <p>L'utilisation de SARA est optionnelle. L'utilisateur peut désactiver SARA depuis les paramètres de son compte. La désactivation de SARA n'affecte pas l'accès aux autres fonctionnalités de la plateforme.</p>

                <h2>6. Évolution</h2>
                <p>SARA est en constante amélioration. IBIG SARL se réserve le droit de modifier les fonctionnalités de SARA sans préavis, et d'en faire évoluer les conditions d'utilisation avec notification préalable.</p>
            </template>

            <!-- ===== IA & RESPONSABILITÉ ===== -->
            <template v-else-if="slug === 'ia'">
                <h2>1. Utilisation de l'IA dans IBIG FactPro</h2>
                <p>IBIG FactPro intègre des fonctionnalités d'intelligence artificielle (notamment via SARA) pour améliorer l'expérience utilisateur et automatiser certaines tâches. Ces fonctionnalités sont conçues comme des outils d'assistance et non comme des décisionnaires autonomes.</p>

                <h2>2. Absence de garantie sur les sorties de l'IA</h2>
                <p>Les contenus générés par les composants IA d'IBIG FactPro (textes, suggestions, analyses, prédictions) sont fournis à titre informatif. IBIG SARL ne garantit pas leur exactitude, exhaustivité ou adéquation à un usage particulier. L'utilisateur doit vérifier et valider toute sortie de l'IA avant de l'utiliser dans un contexte professionnel ou réglementaire.</p>

                <h2>3. Responsabilité de l'utilisateur</h2>
                <p>L'utilisateur est seul responsable des décisions prises sur la base des suggestions ou analyses produites par les composants IA. IBIG SARL décline toute responsabilité pour les erreurs, omissions ou préjudices résultant d'une utilisation sans vérification des sorties de l'IA.</p>

                <h2>4. Usages prohibés de l'IA</h2>
                <p>Il est interdit d'utiliser les fonctionnalités IA d'IBIG FactPro pour : générer des documents frauduleux, contourner des contrôles réglementaires, traiter des données sensibles non autorisées, ou porter atteinte aux droits de tiers.</p>

                <h2>5. Limitation de responsabilité</h2>
                <p>Dans les limites autorisées par la loi, la responsabilité d'IBIG SARL pour tout dommage lié à l'utilisation des fonctionnalités IA est limitée au montant payé par l'utilisateur au cours des 12 derniers mois précédant le dommage allégué.</p>

                <h2>6. Contact</h2>
                <p>Pour signaler un dysfonctionnement d'une fonctionnalité IA : <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a></p>
            </template>

            <!-- ===== SUPPRESSION DU COMPTE ===== -->
            <template v-else-if="slug === 'suppression-compte'">
                <h2>1. Demande de suppression</h2>
                <p>Tout utilisateur peut demander la suppression de son compte IBIG FactPro à tout moment. La demande peut être effectuée depuis les paramètres du compte (section « Mon compte &gt; Supprimer mon compte ») ou en envoyant un email à <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a> avec pour objet « Suppression de compte ».</p>

                <h2>2. Procédure de suppression</h2>
                <p>Étape 1 : export de vos données (recommandé avant toute suppression). Étape 2 : soumission de la demande de suppression. Étape 3 : confirmation par email dans un délai de 48 heures ouvrées. Étape 4 : désactivation du compte et suppression des données dans les 30 jours suivant la confirmation.</p>

                <h2>3. Données conservées après suppression</h2>
                <p>Conformément à nos obligations légales et comptables, certaines données peuvent être conservées au-delà de la suppression du compte : données de facturation (10 ans, obligations comptables), logs de connexion (12 mois, obligations de sécurité). Ces données ne sont pas accessibles par l'utilisateur après la suppression.</p>

                <h2>4. Conséquences de la suppression</h2>
                <p>La suppression du compte entraîne : la perte d'accès définitive à la plateforme, la suppression de toutes les données non conservées pour obligation légale, l'annulation des abonnements actifs (sans remboursement de la période en cours), et la révocation de toutes les intégrations API.</p>

                <h2>5. Réactivation</h2>
                <p>Un compte supprimé ne peut pas être réactivé. Pour recommencer à utiliser IBIG FactPro, il sera nécessaire de créer un nouveau compte.</p>
            </template>

            <!-- ===== RÉCLAMATIONS ===== -->
            <template v-else-if="slug === 'reclamations'">
                <h2>1. Notre engagement</h2>
                <p>IBIG SARL s'engage à traiter chaque réclamation avec sérieux, transparence et dans les meilleurs délais. La satisfaction de nos clients est au cœur de notre démarche qualité.</p>

                <h2>2. Comment soumettre une réclamation</h2>
                <p>Vous pouvez soumettre une réclamation via les canaux suivants :<br>
                <strong>Email :</strong> <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a> (objet : « Réclamation – [votre numéro de compte] »)<br>
                <strong>Téléphone :</strong> {{ TEL1 }} ou {{ TEL2 }}<br>
                <strong>WhatsApp :</strong> {{ WA }}<br>
                <strong>Courrier :</strong> IBIG SARL, Abidjan, Côte d'Ivoire</p>

                <h2>3. Informations à fournir</h2>
                <p>Pour un traitement efficace, précisez : votre nom et email de compte, la date et la nature de l'incident, les démarches déjà entreprises, et les pièces justificatives éventuelles (captures d'écran, numéros de transaction).</p>

                <h2>4. Délais de traitement</h2>
                <p><strong>Accusé de réception :</strong> sous 24 heures ouvrées.<br>
                <strong>Réponse de fond :</strong> sous 5 jours ouvrés pour les réclamations standard, sous 10 jours ouvrés pour les réclamations complexes nécessitant une investigation approfondie.</p>

                <h2>5. Escalade</h2>
                <p>Si vous n'êtes pas satisfait de la réponse apportée, vous pouvez demander une escalade auprès de la direction en écrivant à <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a> avec pour objet « Escalade réclamation ». La direction traitera votre dossier dans un délai de 15 jours ouvrés.</p>

                <h2>6. Résolution amiable</h2>
                <p>IBIG SARL privilégie la résolution amiable des litiges. En cas de désaccord persistant, les parties peuvent recourir à un médiateur agréé avant toute action judiciaire.</p>
            </template>

            <template v-else-if="slug === 'sla'">
                <h2>1. Objet</h2>
                <p>Le présent Accord de Niveau de Service (SLA) définit les engagements d'IBIG SARL concernant la disponibilité, la performance et le support de la plateforme IBIG FactPro.</p>
                <h2>2. Disponibilité garantie</h2>
                <p>IBIG SARL s'engage à maintenir une disponibilité de la plateforme de <strong>99,5 % par mois</strong> (hors maintenances planifiées). La disponibilité est calculée comme suit : ((minutes du mois – minutes d'indisponibilité non planifiée) / minutes du mois) × 100.</p>
                <h2>3. Temps de réponse du support</h2>
                <p><strong>Critique (service inaccessible) :</strong> réponse sous 4 heures ouvrées.<br>
                <strong>Majeur (fonctionnalité bloquante) :</strong> réponse sous 8 heures ouvrées.<br>
                <strong>Mineur (question, amélioration) :</strong> réponse sous 2 jours ouvrés.</p>
                <h2>4. Maintenances planifiées</h2>
                <p>Les maintenances planifiées sont communiquées au minimum 48 heures à l'avance par email et notification in-app. Elles sont généralement effectuées entre 22h00 et 06h00 UTC.</p>
                <h2>5. Crédits SLA</h2>
                <p>En cas de non-respect des engagements de disponibilité (hors cas de force majeure), des crédits de service peuvent être accordés sur demande via <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a>.</p>
                <h2>6. Exclusions</h2>
                <p>Le SLA ne s'applique pas aux interruptions dues à : des cas de force majeure, des actes malveillants de tiers, des pannes Internet du client, ou des opérations effectuées par le client contraires aux CGU.</p>
            </template>

            <template v-else-if="slug === 'securite'">
                <h2>1. Engagement sécurité</h2>
                <p>IBIG SARL accorde une importance primordiale à la sécurité de vos données. Nous appliquons une politique de sécurité multicouche conforme aux bonnes pratiques de l'industrie.</p>
                <h2>2. Chiffrement des données</h2>
                <p>Toutes les communications entre votre navigateur et nos serveurs sont chiffrées via TLS 1.3. Les mots de passe sont stockés avec bcrypt (facteur de coût ≥ 12). Les données sensibles au repos sont chiffrées avec AES-256.</p>
                <h2>3. Contrôle d'accès</h2>
                <p>L'accès aux systèmes de production est limité aux ingénieurs habilités, via SSH avec clés asymétriques uniquement. L'authentification à deux facteurs (2FA) est obligatoire pour tous les accès administratifs.</p>
                <h2>4. Surveillance et détection</h2>
                <p>Nos serveurs sont surveillés 24h/24 par des outils automatisés détectant les anomalies, les tentatives d'intrusion et les comportements suspects. Les journaux d'accès sont conservés 12 mois.</p>
                <h2>5. Tests et audits</h2>
                <p>IBIG SARL réalise des tests de pénétration réguliers et des audits de code. Toute vulnérabilité identifiée est corrigée en priorité selon sa criticité.</p>
                <h2>6. Signalement de vulnérabilités</h2>
                <p>Si vous découvrez une vulnérabilité, merci de la signaler de façon responsable à <a href="mailto:security@ibigsoft.com">security@ibigsoft.com</a>. Nous nous engageons à vous répondre sous 72 heures.</p>
            </template>

            <template v-else-if="slug === 'accessibilite'">
                <h2>1. Notre engagement</h2>
                <p>IBIG SARL s'engage à rendre IBIG FactPro accessible au plus grand nombre, y compris aux personnes en situation de handicap, conformément aux standards WCAG 2.1 niveau AA.</p>
                <h2>2. Fonctionnalités d'accessibilité</h2>
                <p>La plateforme est conçue pour être navigable au clavier, compatible avec les lecteurs d'écran (ARIA labels), et offre un contraste suffisant (ratio ≥ 4.5:1 pour le texte normal). Le texte est redimensionnable sans perte de fonctionnalité.</p>
                <h2>3. Limitations connues</h2>
                <p>Certaines fonctionnalités avancées (éditeur PDF, galerie d'images) peuvent présenter des limitations d'accessibilité. Nous travaillons continuellement à leur amélioration.</p>
                <h2>4. Contact accessibilité</h2>
                <p>Pour signaler un problème d'accessibilité ou obtenir une assistance spécifique, contactez-nous à <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a> en précisant votre situation.</p>
            </template>

            <template v-else-if="slug === 'anti-spam'">
                <h2>1. Politique anti-spam</h2>
                <p>IBIG SARL s'engage à ne jamais envoyer de communications non sollicitées. Tous les emails envoyés via IBIG FactPro (relances, notifications, rapports) sont liés à l'utilisation du service ou à des communications que vous avez explicitement acceptées.</p>
                <h2>2. Emails transactionnels</h2>
                <p>Les emails transactionnels (factures, devis, confirmations) sont envoyés en votre nom à vos clients. Vous êtes seul responsable de l'utilisation de cette fonctionnalité dans le respect des lois anti-spam applicables.</p>
                <h2>3. Désinscription</h2>
                <p>Chaque email marketing contient un lien de désinscription. Les emails transactionnels liés à votre compte (factures, sécurité) ne peuvent pas être désactivés car ils sont essentiels au service.</p>
                <h2>4. Signalement</h2>
                <p>Si vous pensez avoir reçu un email abusif depuis notre plateforme, signalez-le à <a href="mailto:support@ibigsoft.com">support@ibigsoft.com</a>. Nous prenons ces signalements très au sérieux.</p>
            </template>

            <template v-else-if="slug === 'conditions-api'">
                <h2>1. Accès à l'API</h2>
                <p>L'accès à l'API REST publique IBIG FactPro est réservé aux abonnés des formules Business et Enterprise. Une clé API personnelle est requise pour chaque requête.</p>
                <h2>2. Limites de débit</h2>
                <p><strong>Business :</strong> 1 000 requêtes / heure.<br><strong>Enterprise :</strong> 10 000 requêtes / heure.<br>En cas de dépassement, les requêtes excédentaires sont rejetées avec un code HTTP 429.</p>
                <h2>3. Utilisations autorisées</h2>
                <p>L'API peut être utilisée pour intégrer FactPro à vos propres systèmes, automatiser des tâches, ou développer des applications pour vos clients. La revente d'accès API à des tiers sans accord préalable est interdite.</p>
                <h2>4. Responsabilité</h2>
                <p>Vous êtes responsable de la sécurité de votre clé API. En cas de compromission, révoquez-la immédiatement depuis votre espace Paramètres > API.</p>
                <h2>5. Évolution</h2>
                <p>IBIG SARL se réserve le droit de modifier ou de déprécier des endpoints avec un préavis de 90 jours pour les modifications majeures.</p>
            </template>

            <template v-else-if="slug === 'partenaires'">
                <h2>1. Programme IBIG PARTNERS</h2>
                <p>Le programme IBIG PARTNERS permet aux revendeurs, intégrateurs et consultants de distribuer et implémenter IBIG FactPro auprès de leurs clients.</p>
                <h2>2. Conditions d'adhésion</h2>
                <p>Pour rejoindre le programme, le partenaire doit : soumettre une demande via ibigsoft.com, disposer d'une structure légalement constituée, et s'engager à respecter les standards de qualité IBIG SOFT.</p>
                <h2>3. Avantages partenaires</h2>
                <p>Les partenaires bénéficient de remises sur les licences (jusqu'à 30%), d'un accès prioritaire au support, de formations exclusives, de matériaux marketing co-brandés, et d'une visibilité dans l'annuaire partenaires.</p>
                <h2>4. Obligations</h2>
                <p>Les partenaires s'engagent à ne pas dégrader l'image de marque IBIG SOFT, à ne pas sous-licencier sans autorisation, et à maintenir un niveau de service conforme aux attentes de qualité du programme.</p>
                <h2>5. Contact</h2>
                <p>Pour rejoindre le programme : <a href="mailto:partenaires@ibigsoft.com">partenaires@ibigsoft.com</a></p>
            </template>

            <template v-else-if="slug === 'utilisation-acceptable'">
                <h2>1. Utilisations autorisées</h2>
                <p>IBIG FactPro est destiné à la gestion commerciale et administrative légale de votre entreprise. Toute utilisation dans un cadre légal et conforme aux lois de votre pays est autorisée.</p>
                <h2>2. Utilisations interdites</h2>
                <p>Sont strictement interdits : l'utilisation de la plateforme pour des activités illégales, la fraude fiscale, le blanchiment d'argent, le spam, la tentative d'accès non autorisé à des systèmes tiers, ou tout acte contraire à l'ordre public.</p>
                <h2>3. Contenu interdit</h2>
                <p>Il est interdit de stocker, partager ou transmettre via la plateforme : du contenu offensant, illicite, diffamatoire, ou portant atteinte aux droits de tiers (marques, brevets, droits d'auteur).</p>
                <h2>4. Sanctions</h2>
                <p>Toute violation de cette politique peut entraîner la suspension immédiate du compte, sans remboursement, et le cas échéant des poursuites judiciaires.</p>
            </template>

            <template v-else-if="slug === 'rgpd-details'">
                <h2>1. Base légale du traitement</h2>
                <p>Le traitement de vos données personnelles repose sur : l'exécution du contrat (CGU), votre consentement (cookies non essentiels, communications marketing), l'intérêt légitime (sécurité, amélioration du service), et les obligations légales (conservation fiscale).</p>
                <h2>2. Durées de conservation</h2>
                <p><strong>Données de compte :</strong> durée de l'abonnement + 3 ans.<br>
                <strong>Données de facturation :</strong> 10 ans (obligation légale).<br>
                <strong>Logs techniques :</strong> 12 mois.<br>
                <strong>Cookies analytiques :</strong> 13 mois maximum.</p>
                <h2>3. Transferts hors UE/OHADA</h2>
                <p>Vos données sont hébergées sur des serveurs situés en Europe (OVH Cloud). Certains sous-traitants peuvent être établis hors UE ; dans ce cas, des clauses contractuelles types (CCT) approuvées par la Commission européenne encadrent le transfert.</p>
                <h2>4. Sous-traitants</h2>
                <p>Nous faisons appel à des sous-traitants de confiance : OVH (hébergement), Stripe/CinetPay (paiement), Postmark (email transactionnel), Anthropic (IA SARA). Chacun est lié par un DPA (Data Processing Agreement).</p>
                <h2>5. Vos droits</h2>
                <p>Conformément au RGPD et aux lois OHADA applicables, vous disposez des droits d'accès, de rectification, d'effacement, de portabilité, de limitation et d'opposition. Exercez-les à <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a>.</p>
            </template>

            <template v-else-if="slug === 'dpa'">
                <h2>1. Objet du DPA</h2>
                <p>Le présent Accord de Traitement des Données (DPA) régit le traitement des données personnelles par IBIG SARL en qualité de sous-traitant pour le compte du client (responsable de traitement) dans le cadre des services IBIG FactPro.</p>
                <h2>2. Instructions de traitement</h2>
                <p>IBIG SARL traite les données personnelles uniquement sur instruction documentée du responsable de traitement, sauf obligation légale contraire. Le client conserve la maîtrise de ses données et peut en demander l'export ou la suppression à tout moment.</p>
                <h2>3. Mesures de sécurité</h2>
                <p>IBIG SARL met en œuvre les mesures techniques et organisationnelles visées à l'article 32 du RGPD : chiffrement, contrôle d'accès, journalisation, tests de pénétration réguliers.</p>
                <h2>4. Sous-traitants ultérieurs</h2>
                <p>Le client autorise IBIG SARL à avoir recours aux sous-traitants listés dans la Politique RGPD détaillée. Toute modification de cette liste fait l'objet d'une notification préalable de 30 jours.</p>
                <h2>5. Notification de violation</h2>
                <p>En cas de violation de données susceptible d'engager la notification RGPD, IBIG SARL notifie le client dans un délai de 72 heures suivant la détection, avec toutes les informations requises.</p>
                <h2>6. Contact DPO</h2>
                <p>Pour toute question relative au DPA : <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a></p>
            </template>

            <template v-else-if="slug === 'plan-continuite'">
                <h2>1. Objectif</h2>
                <p>Le Plan de Continuité d'Activité (PCA) d'IBIG SARL vise à garantir la continuité des services IBIG FactPro en cas d'incident majeur (panne serveur, sinistre, cyberattaque).</p>
                <h2>2. Sauvegardes</h2>
                <p>Les données sont sauvegardées automatiquement toutes les 24 heures. Les sauvegardes sont répliquées sur deux datacenters géographiquement distincts. La rétention est de 30 jours glissants.</p>
                <h2>3. Objectifs de reprise</h2>
                <p><strong>RTO (Recovery Time Objective) :</strong> reprise du service en moins de 4 heures.<br>
                <strong>RPO (Recovery Point Objective) :</strong> perte de données maximale de 24 heures.</p>
                <h2>4. Tests</h2>
                <p>Des exercices de reprise sont réalisés au minimum une fois par trimestre. Les résultats sont documentés et utilisés pour améliorer le plan.</p>
                <h2>5. Communication de crise</h2>
                <p>En cas d'incident majeur, les clients sont notifiés par email et via la page de statut dans les 2 heures suivant la détection.</p>
            </template>

            <template v-else-if="slug === 'charte-ethique'">
                <h2>1. Nos valeurs fondamentales</h2>
                <p>IBIG SARL s'engage à exercer ses activités avec intégrité, transparence, respect et responsabilité. Ces valeurs guident chacune de nos décisions, de notre relation clients à notre management interne.</p>
                <h2>2. Respect des droits humains</h2>
                <p>IBIG SARL respecte les droits fondamentaux de ses employés, partenaires et clients. Nous condamnons toute forme de discrimination, harcèlement, travail forcé ou exploitation.</p>
                <h2>3. Protection de l'environnement</h2>
                <p>Nous nous efforçons de réduire notre empreinte numérique (optimisation des serveurs, hébergement vert) et encourageons nos clients à adopter la facturation électronique comme alternative écologique au papier.</p>
                <h2>4. Lutte contre la corruption</h2>
                <p>IBIG SARL rejette toute forme de corruption, de détournement de fonds ou de pratiques déloyales. Aucun employé, partenaire ou fournisseur ne doit offrir ou accepter des avantages indus.</p>
                <h2>5. Confidentialité et données</h2>
                <p>Nous traitons les données de nos clients avec le plus grand respect, uniquement pour les finalités déclarées, et sans jamais les vendre à des tiers à des fins commerciales.</p>
                <h2>6. Signalement éthique</h2>
                <p>Tout manquement à cette charte peut être signalé de manière confidentielle à <a href="mailto:legal@ibigsoft.com">legal@ibigsoft.com</a>. Les signalements de bonne foi sont protégés.</p>
            </template>

        </div>

        <!-- Liens vers les autres pages légales -->
        <div style="max-width:900px;margin:2rem auto;padding:1.5rem 2rem;background:#fff;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.06)">
            <h3 style="color:#002D5B;font-size:1rem;font-weight:700;margin:0 0 1rem">Autres pages légales</h3>
            <div style="display:flex;flex-wrap:wrap;gap:.5rem">
                <template v-for="p in otherPages" :key="p.slug">
                    <Link
                        v-if="p.slug !== slug"
                        :href="'/legal/' + p.slug"
                        style="display:inline-block;padding:.35rem .75rem;background:#f1f5f9;border-radius:6px;color:#002D5B;font-size:.8rem;text-decoration:none;transition:background .15s"
                        onmouseover="this.style.background='#e2e8f0'"
                        onmouseout="this.style.background='#f1f5f9'"
                    >{{ p.label }}</Link>
                </template>
            </div>
            <p style="margin:1.25rem 0 0;font-size:.85rem;color:#64748b">
                Pour toute question relative à ces pages légales, contactez-nous :
                <a href="mailto:legal@ibigsoft.com" style="color:#002D5B;font-weight:600">legal@ibigsoft.com</a>
            </p>
        </div>
    </main>

    <PublicFooter />
</template>

<style scoped>
main h2 {
    color: #002D5B;
    font-size: 1.1rem;
    font-weight: 700;
    margin: 1.75rem 0 .5rem;
}
main h2:first-child {
    margin-top: 0;
}
main p {
    color: #374151;
    line-height: 1.7;
    margin: 0 0 .75rem;
    font-size: .92rem;
}
main a {
    color: #002D5B;
    font-weight: 600;
    text-decoration: underline;
}
main a:hover {
    color: #D4A017;
}
</style>
