<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

const searchQuery = ref('');
const activeCategory = ref('general');
const openItems = ref({});

const categories = [
  {
    id: 'general',
    icon: '🌍',
    title: 'Général',
    color: '#0062CC',
    faqs: [
      {
        q: "Qu'est-ce que FactPro ?",
        a: "FactPro est un logiciel SaaS de facturation et de gestion commerciale conçu pour les entrepreneurs, TPE et PME d'Afrique et du monde. Il couvre la facturation, les devis, la gestion des stocks, les paiements Mobile Money, la comptabilité et les rapports, le tout dans une interface simple et moderne.",
      },
      {
        q: "Pour qui est fait FactPro ?",
        a: "FactPro s'adresse aux auto-entrepreneurs, commerçants, prestataires de services, PME et grandes entreprises souhaitant digitaliser leur facturation. Il est particulièrement adapté aux marchés africains (OHADA, Mobile Money, multi-devises FCFA/XAF/GNF) mais fonctionne partout dans le monde.",
      },
      {
        q: "Comment accéder à FactPro ?",
        a: "Rendez-vous sur factpro.ibigsoft.com depuis n'importe quel navigateur. Créez votre compte gratuitement en 1 minute (pas de carte bancaire requise). Vous pouvez aussi installer l'application sur votre téléphone via la PWA.",
      },
      {
        q: "FactPro fonctionne-t-il sur mobile ?",
        a: "Oui, FactPro est entièrement responsive et fonctionne sur tous les smartphones. C'est aussi une PWA (Progressive Web App) que vous pouvez installer sur Android et iPhone directement depuis votre navigateur Chrome ou Safari, sans passer par l'App Store.",
      },
      {
        q: "Qu'est-ce qu'une PWA et comment l'installer ?",
        a: "Une PWA (Progressive Web App) se comporte comme une application native. Sur Android : ouvrez Chrome, accédez à factpro.ibigsoft.com, appuyez sur les 3 points en haut à droite puis 'Ajouter à l'écran d'accueil'. Sur iPhone : ouvrez Safari, appuyez sur le bouton Partager puis 'Sur l'écran d'accueil'. L'application fonctionne même hors connexion.",
      },
      {
        q: "FactPro est-il disponible en plusieurs langues ?",
        a: "FactPro est actuellement disponible en français et en anglais. L'interface s'adapte à la langue de votre navigateur. D'autres langues (arabe, portugais) sont en cours de développement et seront disponibles prochainement.",
      },
      {
        q: "FactPro est-il conforme aux normes OHADA ?",
        a: "Oui. FactPro est conforme au Plan Comptable OHADA (SYSCOHADA révisé). Les documents générés respectent les mentions obligatoires OHADA : RCCM, numéro IFU, mentions TVA, et la numérotation séquentielle imposée par les administrations fiscales d'Afrique de l'Ouest et Centrale.",
      },
      {
        q: "Où sont hébergées mes données ?",
        a: "Vos données sont hébergées sur des serveurs sécurisés en Europe (conformité RGPD) avec des nœuds de cache en Afrique de l'Ouest pour des performances optimales. Les sauvegardes sont effectuées toutes les 24h et répliquées sur plusieurs zones géographiques.",
      },
      {
        q: "Y a-t-il un engagement de durée ou puis-je résilier à tout moment ?",
        a: "FactPro est sans engagement. Vous pouvez résilier votre abonnement à tout moment depuis Paramètres > Abonnement. La résiliation prend effet à la fin de la période déjà payée. Aucune pénalité ni frais de résiliation ne s'appliquent.",
      },
      {
        q: "Puis-je gérer plusieurs sociétés dans un seul compte ?",
        a: "Oui, à partir du plan Business. Dans Paramètres > Sociétés, vous pouvez ajouter plusieurs entités juridiques. Chaque société a ses propres documents, numérotation et paramètres. Vous basculez d'une société à l'autre depuis le sélecteur en haut de l'écran.",
      },
    ],
  },
  {
    id: 'connexion',
    icon: '🔒',
    title: 'Connexion & Sécurité',
    color: '#374151',
    faqs: [
      {
        q: "J'ai oublié mon mot de passe, comment le réinitialiser ?",
        a: "Sur la page de connexion, cliquez sur 'Mot de passe oublié ?'. Entrez votre adresse email et vous recevrez un lien de réinitialisation valable 60 minutes. Si vous ne recevez pas l'email, vérifiez votre dossier spam ou contactez support@ibigsoft.com.",
      },
      {
        q: "Comment activer la double authentification (2FA) ?",
        a: "Dans votre Profil > Sécurité, cliquez sur 'Activer l'authentification à deux facteurs'. Scannez le QR code avec Google Authenticator, Authy ou Microsoft Authenticator. À chaque connexion, un code à 6 chiffres vous sera demandé en plus de votre mot de passe.",
      },
      {
        q: "Ma session expire trop vite, comment la prolonger ?",
        a: "Par défaut, les sessions inactives expirent après 2 heures pour des raisons de sécurité. Vous pouvez activer l'option 'Se souvenir de moi' lors de la connexion pour une session de 30 jours. Cette option n'est pas recommandée sur un ordinateur partagé.",
      },
      {
        q: "Comment changer mon adresse email ?",
        a: "Dans Profil > Informations personnelles, modifiez votre email et cliquez 'Enregistrer'. Un email de vérification sera envoyé à la nouvelle adresse. Le changement n'est effectif qu'après validation du lien dans cet email.",
      },
      {
        q: "Puis-je me connecter depuis plusieurs appareils simultanément ?",
        a: "Oui, il n'y a pas de limite de sessions simultanées. Vous pouvez être connecté depuis votre ordinateur, votre téléphone et une tablette en même temps. Toutes vos données sont synchronisées en temps réel entre les appareils.",
      },
      {
        q: "Qui peut se connecter à mon compte FactPro ?",
        a: "Seuls vous et les membres de votre équipe que vous avez explicitement invités peuvent se connecter. Chaque utilisateur a ses propres identifiants. Les administrateurs peuvent voir la liste complète des utilisateurs dans Paramètres > Équipe.",
      },
      {
        q: "Comment me déconnecter de tous mes appareils ?",
        a: "Dans Profil > Sécurité > 'Sessions actives', vous voyez tous les appareils connectés à votre compte. Cliquez sur 'Déconnecter toutes les sessions' pour révoquer tous les accès simultanément. Utile si vous suspectez un accès non autorisé.",
      },
      {
        q: "Mon adresse IP a été bloquée, que faire ?",
        a: "Un blocage IP peut survenir après plusieurs tentatives de connexion échouées (protection anti-brute force). Le blocage se lève automatiquement après 30 minutes. Si le problème persiste, contactez support@ibigsoft.com en indiquant votre adresse IP.",
      },
      {
        q: "Mon compte a été suspendu, comment le réactiver ?",
        a: "Une suspension peut être due à un abonnement expiré, une violation des CGU ou une activité suspecte détectée. Vérifiez d'abord l'état de votre abonnement dans Paramètres > Abonnement. Pour toute autre raison, contactez support@ibigsoft.com.",
      },
      {
        q: "Comment voir l'historique des connexions à mon compte ?",
        a: "Dans Profil > Sécurité > 'Historique de connexions', vous accédez à la liste des 50 dernières connexions avec l'heure, l'adresse IP, le pays et le type d'appareil. En cas de connexion suspecte, déconnectez immédiatement la session et changez votre mot de passe.",
      },
    ],
  },
  {
    id: 'compte',
    icon: '👥',
    title: 'Compte & Utilisateurs',
    color: '#dc2626',
    faqs: [
      {
        q: "Comment inviter un collaborateur à rejoindre mon espace ?",
        a: "Dans Paramètres > Équipe, cliquez sur 'Inviter un membre'. Entrez son adresse email et sélectionnez son rôle. Il recevra une invitation par email avec un lien pour créer son mot de passe. L'accès est actif dès qu'il valide son invitation.",
      },
      {
        q: "Quels sont les rôles disponibles dans FactPro ?",
        a: "FactPro propose 5 rôles prédéfinis : Administrateur (accès complet), Comptable (documents et rapports financiers), Commercial (devis, factures et clients), Caissier (encaissements et POS), et Lecture seule (consultation uniquement). Vous pouvez aussi créer des rôles personnalisés.",
      },
      {
        q: "Comment modifier les permissions d'un utilisateur ?",
        a: "Dans Paramètres > Équipe, cliquez sur le membre concerné puis 'Modifier les permissions'. Vous pouvez ajuster finement les droits par module : Clients, Documents, Paiements, Stocks, Rapports, Paramètres. Les changements prennent effet immédiatement.",
      },
      {
        q: "Comment supprimer un utilisateur de mon équipe ?",
        a: "Dans Paramètres > Équipe, cliquez sur les 3 points '···' à côté du membre, puis 'Retirer de l'équipe'. L'utilisateur n'aura plus accès à votre espace. Ses documents et actions passées sont conservés dans l'historique.",
      },
      {
        q: "Y a-t-il une limite au nombre d'utilisateurs ?",
        a: "Le plan Starter est limité à 1 utilisateur (vous-même). Le plan Business permet jusqu'à 5 utilisateurs. Le plan Enterprise offre un nombre illimité d'utilisateurs. Vous pouvez voir et gérer les limites dans Paramètres > Abonnement.",
      },
      {
        q: "Comment transférer la propriété du compte à un autre utilisateur ?",
        a: "Pour transférer le compte principal, contactez support@ibigsoft.com avec une demande écrite et les informations de l'ancien et du nouveau propriétaire. Pour des raisons de sécurité, ce transfert nécessite une vérification d'identité des deux parties.",
      },
      {
        q: "Existe-t-il un compte de démonstration pour tester ?",
        a: "Oui. Accédez à factpro.ibigsoft.com/demo-login pour vous connecter avec un compte de démonstration pré-rempli de données fictives. Vous pouvez explorer toutes les fonctionnalités sans risque. Les données de démo sont réinitialisées toutes les nuits.",
      },
      {
        q: "Comment supprimer les données de test de mon compte ?",
        a: "Dans Paramètres > Compte > 'Données de test', vous pouvez supprimer en bloc tous les clients, produits, documents et paiements marqués comme 'test'. Cette action est irréversible. Utilisez-la une fois que vous êtes prêt à passer en production.",
      },
      {
        q: "Que se passe-t-il si mon compte est inactif plusieurs mois ?",
        a: "Un compte inactif (sans connexion depuis 6 mois et sans abonnement actif) passe en mode archivé. Vos données sont conservées pendant 12 mois supplémentaires. Vous recevrez des emails de rappel à 3 mois, 1 mois et 1 semaine avant la suppression définitive.",
      },
      {
        q: "Quelle est la différence entre Admin et Superadmin ?",
        a: "L'Admin gère les utilisateurs, les paramètres et les données de l'entreprise. Le Superadmin (propriétaire du compte) a en plus accès à la facturation de l'abonnement, peut supprimer le compte et modifier les paramètres de sécurité avancés. Il ne peut pas être supprimé par un Admin.",
      },
    ],
  },
  {
    id: 'facturation',
    icon: '📄',
    title: 'Facturation & Documents',
    color: '#7c3aed',
    faqs: [
      {
        q: "Comment créer ma première facture ?",
        a: "Dans le menu Documents > Nouvelle facture : sélectionnez le client (ou créez-le à la volée), ajoutez vos lignes de produits ou services, vérifiez les totaux HT/TVA/TTC, puis cliquez 'Finaliser'. La facture est numérotée automatiquement et un PDF est généré instantanément.",
      },
      {
        q: "Comment fonctionne la numérotation automatique des factures ?",
        a: "FactPro numérote séquentiellement selon le format configuré (ex : FACT-2026-0001). Vous personnalisez le préfixe, les séparateurs et le nombre de chiffres dans Paramètres > Numérotation. La séquence est strictement croissante et ne peut pas avoir de trous (conformité fiscale).",
      },
      {
        q: "Puis-je modifier une facture après l'avoir finalisée ?",
        a: "Non, une facture finalisée est verrouillée pour garantir l'intégrité comptable. Si vous avez fait une erreur, créez un avoir du même montant pour annuler la facture, puis créez une nouvelle facture correcte. Cette procédure est conforme aux normes comptables OHADA et françaises.",
      },
      {
        q: "Comment créer un avoir (note de crédit) ?",
        a: "Ouvrez la facture concernée et cliquez 'Créer un avoir'. FactPro génère un avoir lié à la facture originale avec les mêmes lignes en négatif. Vous pouvez modifier le montant pour un avoir partiel. L'avoir est numéroté automatiquement (préfixe AV-) et lié à la facture d'origine.",
      },
      {
        q: "Les factures ont-elles un QR code anti-falsification ?",
        a: "Oui. Chaque facture finalisée contient un QR code unique qui permet à vos clients et aux administrations de vérifier l'authenticité du document. En scannant le QR code, on accède à une page de vérification en ligne affichant les données originales de la facture.",
      },
      {
        q: "Comment intégrer une signature électronique sur mes documents ?",
        a: "FactPro intègre la signature électronique eIDAS. Sur le document, cliquez 'Demander une signature'. Un code OTP est envoyé par SMS au signataire. La signature horodatée est apposée et le document est verrouillé cryptographiquement. La preuve de signature est archivée.",
      },
      {
        q: "FactPro gère-t-il la facturation en plusieurs devises ?",
        a: "Oui. Vous pouvez émettre des factures dans n'importe quelle devise (XOF, XAF, EUR, USD, GBP, MAD, GNF, etc.). Chaque client peut avoir une devise par défaut. Les taux de change sont mis à jour automatiquement. Les rapports affichent les totaux dans la devise de référence de votre société.",
      },
      {
        q: "Puis-je personnaliser le modèle visuel de mes documents ?",
        a: "Oui. Dans Paramètres > Templates, choisissez parmi plus de 100 modèles PDF. Vous personnalisez les couleurs primaire et secondaire, le placement du logo, et pouvez activer ou désactiver des éléments comme le QR code, la signature, les conditions de paiement ou la mention OHADA.",
      },
      {
        q: "Comment gérer la TVA à plusieurs taux sur une même facture ?",
        a: "Chaque ligne de votre facture peut avoir son propre taux de TVA (0%, 9%, 10%, 18%, 19,25%, 20%, etc.). FactPro calcule automatiquement la TVA par taux et affiche un tableau récapitulatif de la TVA ventilée par taux en bas de la facture, conformément aux exigences fiscales.",
      },
      {
        q: "Comment créer des factures récurrentes automatiques ?",
        a: "Dans Documents > Récurrences, créez un modèle de facture et définissez la fréquence (mensuelle, trimestrielle, annuelle, ou personnalisée), la date de début et de fin, et l'action à l'échéance (brouillon, finalisation auto, envoi auto). FactPro génère et envoie les factures sans intervention.",
      },
    ],
  },
  {
    id: 'paiements',
    icon: '💰',
    title: 'Paiements & Encaissements',
    color: '#d97706',
    faqs: [
      {
        q: "Comment activer Orange Money pour recevoir des paiements ?",
        a: "Dans Paramètres > Paiements > Orange Money CI (ou Orange Money par pays), entrez votre numéro Orange Money marchand, le nom du titulaire et les instructions de paiement personnalisées. Vos clients verront ces informations sur le lien de paiement et pourront initier le transfert depuis leur téléphone.",
      },
      {
        q: "Comment activer le paiement Wave ?",
        a: "Dans Paramètres > Paiements > Wave, entrez votre numéro Wave marchand. Vous pouvez aussi connecter votre compte Wave Business via l'API Wave pour des confirmations de paiement automatiques. Les clients scannent le QR Wave ou paient via le lien de paiement FactPro.",
      },
      {
        q: "Comment configurer MTN Mobile Money (MoMo) ?",
        a: "Dans Paramètres > Paiements > MTN MoMo, entrez votre numéro MoMo marchand et le pays (CI, CM, GH, BJ, etc.). Pour les confirmations automatiques, renseignez vos clés API MTN MoMo (disponibles sur le portail développeur MTN). Les paiements sont alors confirmés en temps réel.",
      },
      {
        q: "Comment activer Moov Money ?",
        a: "Dans Paramètres > Paiements > Moov Money, entrez votre numéro Moov marchand et sélectionnez votre pays (CI, BJ, TG, BF, etc.). Le lien de paiement FactPro affichera les instructions de paiement Moov. La confirmation peut être manuelle (preuve de paiement) ou automatique via API.",
      },
      {
        q: "Comment configurer CinetPay pour les paiements carte et Mobile Money ?",
        a: "Dans Paramètres > Paiements > CinetPay, entrez votre site_id et votre clé API CinetPay (disponibles dans votre espace CinetPay). Sélectionnez les devises et méthodes activées. Les webhooks sont configurés automatiquement pour confirmer les paiements en temps réel.",
      },
      {
        q: "Comment enregistrer un paiement par virement bancaire ?",
        a: "Sur la facture, cliquez 'Enregistrer un paiement' et sélectionnez 'Virement bancaire' comme mode de paiement. Entrez le montant, la date de réception et éventuellement la référence du virement. La facture passe au statut 'Payée'. Vous pouvez aussi joindre le relevé bancaire comme justificatif.",
      },
      {
        q: "Comment gérer les acomptes sur devis ou facture ?",
        a: "Lors de la création d'un devis ou d'une facture, cliquez 'Ajouter un acompte'. Définissez le pourcentage ou le montant. Un lien de paiement d'acompte est généré. Une fois l'acompte payé, il est déduit automatiquement du solde et apparaît sur la facture finale.",
      },
      {
        q: "Comment enregistrer un paiement partiel ?",
        a: "Sur la facture, cliquez 'Enregistrer un paiement' et entrez le montant partiel reçu. La facture passe au statut 'Partiellement payée'. Le solde restant est affiché clairement. Vous pouvez enregistrer autant de paiements partiels que nécessaire jusqu'au solde complet.",
      },
      {
        q: "Comment envoyer des relances de paiement automatiques ?",
        a: "Dans Paramètres > Relances, définissez des règles automatiques : par exemple J+7, J+15, J+30 après la date d'échéance. Pour chaque règle, configurez le canal (email, WhatsApp) et personnalisez le message. FactPro envoie les relances automatiquement sans votre intervention.",
      },
      {
        q: "Comment générer et envoyer un reçu de paiement ?",
        a: "Après l'enregistrement d'un paiement, FactPro génère automatiquement un reçu PDF. Sur la page du paiement, cliquez 'Envoyer le reçu' pour l'envoyer par email ou WhatsApp au client. Vous pouvez aussi le télécharger ou le partager via un lien en ligne.",
      },
    ],
  },
  {
    id: 'clients',
    icon: '🤝',
    title: 'Clients & CRM',
    color: '#059669',
    faqs: [
      {
        q: "Comment importer ma base de clients existante ?",
        a: "Dans Paramètres > Import > Clients, téléchargez le modèle CSV fourni. Remplissez les colonnes (nom, email, téléphone, adresse, pays, RCCM, etc.) et importez le fichier. FactPro détecte automatiquement les doublons par email. L'import signale les erreurs ligne par ligne pour correction.",
      },
      {
        q: "Qu'est-ce que contient la fiche client complète ?",
        a: "La fiche client regroupe : informations de contact (coordonnées, RCCM, NIF), préférences (devise, langue, délai de paiement), onglet Documents (toutes les factures, devis, avoirs), onglet Paiements (historique des encaissements), onglet Statistiques (CA total, panier moyen, fréquence d'achat).",
      },
      {
        q: "Comment consulter l'historique complet d'un client ?",
        a: "Dans la fiche client, l'onglet 'Documents' affiche tous les devis, factures, avoirs et bons de livraison. L'onglet 'Paiements' liste chaque encaissement avec date et montant. L'onglet 'Statistiques' montre le CA total, la marge moyenne et l'évolution dans le temps.",
      },
      {
        q: "Comment configurer des relances automatiques par client ?",
        a: "Les relances globales se configurent dans Paramètres > Relances. Pour des règles spécifiques à un client, ouvrez sa fiche, onglet 'Préférences', et activez les relances personnalisées avec des délais et canaux différents des règles globales.",
      },
      {
        q: "FactPro dispose-t-il d'un pipeline commercial (CRM) ?",
        a: "Oui. Dans le module CRM (disponible à partir du plan Business), vous visualisez vos opportunités commerciales en mode Kanban avec les étapes : Prospect > Contacté > Devis envoyé > Négociation > Gagné/Perdu. Chaque opportunité est liée à un client et peut déclencher la création d'un devis en 1 clic.",
      },
      {
        q: "Comment partager un devis en ligne pour signature ?",
        a: "Sur le devis, cliquez 'Lien de signature'. Un lien unique est généré et valable 30 jours. Votre client peut consulter le devis sur n'importe quel appareil, l'accepter, le refuser ou demander des modifications. Une fois signé, vous recevez une notification et le devis est verrouillé.",
      },
      {
        q: "Puis-je blacklister un client mauvais payeur ?",
        a: "Oui. Dans la fiche client, cliquez sur les 3 points '···' > 'Marquer comme client à risque'. Une alerte rouge apparaîtra chaque fois que ce client est sélectionné sur un nouveau document. Vous pouvez également ajouter une note interne visible uniquement par votre équipe.",
      },
      {
        q: "Y a-t-il un portail client où mes clients peuvent voir leurs factures ?",
        a: "Oui. Chaque client reçoit un accès à son portail personnel où il peut consulter ses factures, télécharger les PDF, voir son solde et effectuer des paiements. Le portail est personnalisable avec votre logo. Activez-le dans Paramètres > Portail client.",
      },
      {
        q: "Comment voir le solde dû par chaque client ?",
        a: "Le solde client (somme des factures impayées) est visible directement sur la fiche client. Dans Rapports > Clients > Analyse des créances, vous obtenez la liste de tous les clients avec solde positif, triés par montant dû ou par ancienneté de retard.",
      },
      {
        q: "Comment exporter ma liste de clients ?",
        a: "Dans la liste des clients, cliquez sur 'Exporter > Excel' ou 'Exporter > CSV'. Le fichier contient toutes les colonnes affichées plus les champs cachés (RCCM, NIF, notes). Vous pouvez filtrer avant export pour n'exporter qu'un sous-ensemble de clients.",
      },
    ],
  },
  {
    id: 'stocks',
    icon: '📦',
    title: 'Stock & Produits',
    color: '#0891b2',
    faqs: [
      {
        q: "Comment créer un produit ou service dans FactPro ?",
        a: "Dans Produits > Nouveau produit, renseignez le nom, le SKU (référence unique), la catégorie, le prix de vente HT, le taux de TVA applicable, et si c'est un produit physique, activez le suivi de stock avec la quantité initiale et le prix d'achat pour le calcul de marge.",
      },
      {
        q: "Que se passe-t-il si mon stock descend en négatif ?",
        a: "Par défaut, FactPro avertit avant de permettre une vente qui mettrait le stock en négatif. Vous pouvez autoriser ou interdire les stocks négatifs dans Paramètres > Stocks. En cas de stock négatif autorisé, la quantité s'affiche en rouge comme alerte visuelle.",
      },
      {
        q: "Comment configurer les alertes de stock faible ?",
        a: "Dans la fiche de chaque produit, définissez le 'Seuil d'alerte'. Quand le stock disponible descend en dessous de ce seuil, une notification apparaît sur votre tableau de bord et vous recevez un email ou SMS d'alerte. Vous pouvez aussi paramétrer une alerte globale dans Paramètres > Stocks.",
      },
      {
        q: "FactPro supporte-t-il les codes-barres et QR codes ?",
        a: "Oui. Chaque produit peut avoir un ou plusieurs codes-barres (EAN-8, EAN-13, Code 128, QR code). Lors de la vente ou de l'inventaire, utilisez un lecteur de code-barres USB ou la caméra de votre téléphone (via l'interface POS ou la page Inventaire) pour identifier instantanément les produits.",
      },
      {
        q: "Comment organiser mes produits en catégories ?",
        a: "Dans Produits > Catégories, créez une arborescence de catégories et sous-catégories (ex : Électronique > Téléphones > Smartphones). Chaque produit est assigné à une catégorie. Les catégories apparaissent dans les filtres de la liste produits et dans les rapports de vente par catégorie.",
      },
      {
        q: "FactPro gère-t-il plusieurs entrepôts ou points de vente ?",
        a: "Oui, à partir du plan Business. Dans Stocks > Entrepôts, créez vos sites (magasin principal, entrepôt B, boutique centre-ville, etc.). Chaque stock est suivi par entrepôt. Les transferts inter-entrepôts génèrent automatiquement un bon de transfert traçable.",
      },
      {
        q: "Comment réaliser un inventaire physique dans FactPro ?",
        a: "Dans Stocks > Inventaire > Nouvel inventaire, sélectionnez l'entrepôt. FactPro affiche les stocks théoriques. Saisissez les quantités réelles (ou scannez les codes-barres). FactPro calcule les écarts, génère un rapport d'inventaire et met à jour les stocks après votre validation.",
      },
      {
        q: "Puis-je définir des prix différents par client ou par quantité ?",
        a: "Oui, via les tarifs personnalisés. Dans la fiche client > Tarification ou dans la fiche produit > Grilles tarifaires, définissez des prix spéciaux ou des remises selon le client ou la quantité commandée (ex : -5% dès 10 unités, -10% dès 50 unités).",
      },
      {
        q: "Comment gérer les promotions et remises temporaires ?",
        a: "Dans Produits > Promotions, créez des promotions avec date de début, date de fin, type de remise (% ou montant fixe) et produits concernés. Les promotions actives s'appliquent automatiquement lors de la création de devis et factures sans saisie manuelle.",
      },
      {
        q: "Comment importer un catalogue de produits via CSV ?",
        a: "Dans Produits > Importer, téléchargez le modèle CSV (colonnes : nom, SKU, catégorie, prix HT, TVA, stock initial, seuil d'alerte, prix d'achat). L'import met à jour les produits existants (identifiés par SKU) et crée les nouveaux. Les erreurs sont signalées ligne par ligne.",
      },
    ],
  },
  {
    id: 'rapports',
    icon: '📊',
    title: 'Rapports & Exports',
    color: '#be185d',
    faqs: [
      {
        q: "Comment voir le chiffre d'affaires du mois en cours ?",
        a: "Le CA du mois est affiché en temps réel sur votre tableau de bord principal. Pour plus de détails, allez dans Rapports > Chiffre d'affaires > sélectionnez 'Mois en cours'. Vous voyez le CA par semaine, par client et par produit avec comparaison au mois précédent.",
      },
      {
        q: "Comment exporter mes données en Excel ?",
        a: "Sur toutes les listes de FactPro (clients, factures, produits, paiements, stocks), un bouton 'Exporter > Excel' est disponible. Le fichier .xlsx téléchargé contient toutes les colonnes visibles et filtrées. Pour des exports personnalisés, utilisez Rapports > Export personnalisé.",
      },
      {
        q: "Comment générer et télécharger des rapports en PDF ?",
        a: "Dans Rapports, ouvrez le rapport souhaité, appliquez vos filtres, puis cliquez 'Télécharger PDF'. Le PDF est généré en quelques secondes avec mise en page professionnelle incluant votre logo. Vous pouvez aussi l'envoyer directement par email depuis l'interface.",
      },
      {
        q: "Comment générer le rapport de TVA pour ma déclaration fiscale ?",
        a: "Dans Rapports > Fiscal > TVA, choisissez le régime (mensuel ou trimestriel) et la période. FactPro liste la TVA collectée sur vos ventes, la TVA déductible sur vos achats, et calcule le solde net à reverser à l'administration. Le rapport est exportable en PDF et Excel.",
      },
      {
        q: "FactPro génère-t-il le FEC (Fichier des Écritures Comptables) ?",
        a: "Oui, pour les entreprises soumises aux obligations comptables françaises. Dans Rapports > Comptabilité > Export FEC, sélectionnez l'exercice fiscal. Le fichier respecte strictement le format FEC imposé par l'administration fiscale française (article L.47 A du LPF).",
      },
      {
        q: "Comment obtenir un rapport de performance par vendeur ?",
        a: "Dans Rapports > Équipe > Performance commerciale, filtrez par membre de l'équipe ou par période. Vous voyez le CA généré, le nombre de devis et factures créés, le taux de conversion devis-facture et la valeur moyenne des transactions pour chaque commercial.",
      },
      {
        q: "Comment comparer les performances de cette année avec l'an dernier ?",
        a: "Dans Rapports > Analytics > Comparaison annuelle, sélectionnez les deux exercices à comparer. Le graphique superpose les deux courbes mois par mois avec les écarts en valeur absolue et en pourcentage. Vous identifiez rapidement les mois de croissance ou de baisse.",
      },
      {
        q: "Puis-je créer des rapports avec des filtres personnalisés ?",
        a: "Oui. Tous les rapports FactPro offrent des filtres avancés : période, client, produit, catégorie, commercial, devise, statut de paiement, etc. Vous pouvez enregistrer vos filtres favoris comme 'Rapport sauvegardé' pour y accéder en un clic à chaque fois.",
      },
      {
        q: "Les rapports sont-ils accessibles depuis le mobile ?",
        a: "Oui, tous les rapports sont optimisés pour mobile. Les graphiques sont interactifs et s'adaptent à la taille de l'écran. Sur mobile, certains tableaux très larges passent en mode défilement horizontal. L'export PDF et Excel fonctionne également depuis le mobile.",
      },
      {
        q: "Comment automatiser l'envoi de rapports par email chaque mois ?",
        a: "Dans Rapports > [Rapport souhaité] > 'Planifier l'envoi', configurez la fréquence (hebdomadaire, mensuel, trimestriel), le jour d'envoi et les destinataires (email). FactPro génère et envoie automatiquement le rapport PDF à la date prévue, sans votre intervention.",
      },
    ],
  },
  {
    id: 'abonnement',
    icon: '💳',
    title: 'Abonnement & Licence',
    color: '#7c3aed',
    faqs: [
      {
        q: "Comment changer de formule d'abonnement ?",
        a: "Dans Paramètres > Abonnement > 'Changer de plan', consultez les formules disponibles et cliquez sur celle qui vous convient. La mise à niveau est immédiate. En cas de montée en gamme, la différence de prix est calculée au prorata du temps restant dans votre période actuelle.",
      },
      {
        q: "Comment annuler mon abonnement ?",
        a: "Dans Paramètres > Abonnement > 'Résilier mon abonnement', confirmez votre souhait de résiliation. L'abonnement se termine à la fin de la période déjà payée. Vous conservez un accès complet jusqu'à cette date. Pensez à exporter vos données avant la fin.",
      },
      {
        q: "Puis-je obtenir un remboursement si je ne suis pas satisfait ?",
        a: "IBIG Soft propose une garantie satisfait ou remboursé de 14 jours pour les nouveaux abonnements. Si vous n'êtes pas satisfait dans les 14 jours suivant votre premier paiement, contactez support@ibigsoft.com pour un remboursement intégral, sans condition ni justification.",
      },
      {
        q: "Comment fonctionne l'activation par clé de licence ?",
        a: "Pour les licences perpétuelles ou White-label, vous recevez une clé d'activation après paiement. Dans Paramètres > Licence, entrez cette clé pour activer votre accès. En cas de perte de clé, contactez support@ibigsoft.com avec votre confirmation de commande.",
      },
      {
        q: "Combien de temps dure l'essai gratuit et qu'inclut-il ?",
        a: "L'essai gratuit dure 7 jours avec accès complet à toutes les fonctionnalités du plan Business (pas de version limitée). Aucune carte bancaire n'est requise. Toutes vos données créées pendant l'essai sont conservées si vous souscrivez un abonnement payant.",
      },
      {
        q: "Comment télécharger mes factures d'abonnement pour ma comptabilité ?",
        a: "Dans Paramètres > Abonnement > 'Historique des paiements', chaque paiement a une facture PDF téléchargeable. Ces factures sont établies au nom de votre entreprise avec votre RCCM/NIF si vous les avez renseignés dans Paramètres > Société.",
      },
      {
        q: "Puis-je payer mon abonnement en FCFA ou en XAF ?",
        a: "Oui. Les prix sont affichés en FCFA (XOF) pour l'Afrique de l'Ouest et en XAF pour le Cameroun. Vous pouvez payer par Mobile Money (Orange Money, Wave, MTN MoMo), carte bancaire (CinetPay) ou virement bancaire. Le paiement en euros est aussi disponible pour la diaspora.",
      },
      {
        q: "Le renouvellement de l'abonnement est-il automatique ?",
        a: "Si vous payez par carte bancaire, le renouvellement est automatique. Vous recevez un email de rappel 7 jours avant l'échéance. Si vous payez par Mobile Money ou virement, le renouvellement est manuel. Vous recevez une notification 14 jours avant l'expiration.",
      },
      {
        q: "Existe-t-il une formule sur mesure pour les grandes entreprises ?",
        a: "Oui. Pour les entreprises avec des besoins spécifiques (nombre d'utilisateurs très élevé, intégrations sur mesure, SLA garanti, formation dédiée, etc.), contactez-nous à contact@ibigsoft.com pour un devis personnalisé. Des tarifs groupe et réseau de franchise sont disponibles.",
      },
      {
        q: "Puis-je changer de pays de facturation pour mon abonnement ?",
        a: "Oui. Si vous déménagez ou ouvrez une filiale dans un autre pays, contactez support@ibigsoft.com pour mettre à jour vos informations de facturation (devise, TVA locale, adresse). Certains tarifs varient selon le pays pour s'adapter au pouvoir d'achat local.",
      },
    ],
  },
  {
    id: 'support',
    icon: '🎧',
    title: 'Support & Aide',
    color: '#0f766e',
    faqs: [
      {
        q: "Comment contacter le support FactPro ?",
        a: "Plusieurs canaux sont disponibles : (1) SARA l'assistant IA disponible 24h/24 sur toutes les pages ; (2) Email : support@ibigsoft.com ; (3) WhatsApp : +225 07 78 88 25 92 ; (4) Téléphone : +225 27 22 27 60 14 (du lundi au vendredi, 8h-18h GMT) ; (5) Ticket de support depuis Aide > Ouvrir un ticket.",
      },
      {
        q: "Quel est le délai de réponse du support ?",
        a: "Le délai de réponse dépend de votre plan : Starter — réponse sous 48h (email uniquement) ; Business — réponse sous 24h (email + WhatsApp) ; Enterprise — réponse sous 4h avec account manager dédié. SARA répond instantanément 24h/24 pour les questions courantes.",
      },
      {
        q: "Comment ouvrir un ticket de support ?",
        a: "Dans l'application, cliquez sur 'Aide' dans le menu ou allez dans Aide > Support > 'Nouveau ticket'. Décrivez votre problème, ajoutez des captures d'écran si nécessaire, et indiquez le niveau d'urgence. Vous recevrez un numéro de ticket et serez notifié par email de chaque mise à jour.",
      },
      {
        q: "Des formations sont-elles disponibles pour mon équipe ?",
        a: "Oui. Plusieurs options de formation : (1) Tutoriels vidéo intégrés (accessibles depuis Aide > Tutoriels) ; (2) Webinaires en direct chaque semaine (inscription sur ibigsoft.com/webinaires) ; (3) Formation individuelle en visioconférence (payante, sur devis) ; (4) Formation en présentiel disponible en Côte d'Ivoire.",
      },
      {
        q: "Existe-t-il un guide utilisateur téléchargeable en PDF ?",
        a: "Oui. Dans Aide > Documentation, téléchargez le Guide Utilisateur Complet (PDF, 200+ pages) couvrant toutes les fonctionnalités de FactPro avec des captures d'écran étape par étape. Le guide est mis à jour à chaque nouvelle version majeure.",
      },
      {
        q: "Qu'est-ce que SARA et comment l'utiliser ?",
        a: "SARA (Smart Assistant for Real-time Assistance) est l'assistant IA intégré à FactPro. Elle répond instantanément à vos questions sur les fonctionnalités, vous guide pas à pas, peut effectuer des actions à votre place (créer un client, chercher une facture) et escalade vers un humain si elle ne peut pas résoudre votre problème.",
      },
      {
        q: "Comment demander une démonstration personnalisée ?",
        a: "Visitez factpro.ibigsoft.com/demo pour réserver une démo en direct avec un expert FactPro via Calendly. La démo dure 30 minutes et est adaptée à votre secteur d'activité et vos besoins spécifiques. Vous pouvez aussi accéder immédiatement au compte de démo sur /demo-login.",
      },
      {
        q: "Comment savoir quand une mise à jour est disponible ?",
        a: "Les mises à jour sont déployées automatiquement — vous n'avez rien à faire. Une bannière de notification apparaît dans l'application lors d'une mise à jour majeure. Consultez la page /changelog pour le détail de chaque mise à jour ou abonnez-vous à la newsletter des nouveautés.",
      },
      {
        q: "Comment signaler un bug ou suggérer une amélioration ?",
        a: "Pour un bug : ouvrez un ticket depuis Aide > Support avec la description du problème, les étapes pour le reproduire et une capture d'écran. Pour des suggestions : accédez à /roadmap pour voter sur les fonctionnalités existantes ou proposer les vôtres. Les meilleures idées sont intégrées dans les prochaines versions.",
      },
      {
        q: "Comment rejoindre le programme partenaires FactPro ?",
        a: "Visitez ibigpartners.com pour vous inscrire gratuitement au programme de partenariat IBIG. En recommandant FactPro, vous gagnez une commission allant jusqu'à 20% sur 3 niveaux de filleuls. Le tableau de bord partenaire affiche vos gains en temps réel et vous permet de générer vos liens de parrainage.",
      },
    ],
  },
];

// Search & filter
const filteredCategories = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return categories;
  return categories
    .map(cat => ({
      ...cat,
      faqs: cat.faqs.filter(
        faq => faq.q.toLowerCase().includes(q) || faq.a.toLowerCase().includes(q)
      ),
    }))
    .filter(cat => cat.faqs.length > 0);
});

const isSearching = computed(() => searchQuery.value.trim().length > 0);

const displayedCategories = computed(() => {
  if (isSearching.value) return filteredCategories.value;
  return categories.filter(c => c.id === activeCategory.value);
});

const totalFaqs = computed(() => categories.reduce((s, c) => s + c.faqs.length, 0));

const searchResultCount = computed(() =>
  filteredCategories.value.reduce((s, c) => s + c.faqs.length, 0)
);

// Highlight
function highlight(text) {
  const q = searchQuery.value.trim();
  if (!q) return text;
  const regex = new RegExp(`(${q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
  return text.replace(regex, '<mark class="bg-yellow-200 text-yellow-900 rounded px-0.5">$1</mark>');
}

// Accordion
function toggleFaq(catId, idx) {
  const key = `${catId}-${idx}`;
  openItems.value[key] = !openItems.value[key];
}
function isOpen(catId, idx) {
  return !!openItems.value[`${catId}-${idx}`];
}

// Category selection
function selectCategory(catId) {
  activeCategory.value = catId;
  openItems.value = {};
}

// SARA
function openSara() {
  window.dispatchEvent(new CustomEvent('sara:open'));
}
</script>

<template>
  <Head>
    <title>FAQ IBIG FactPro — 100 questions fréquentes</title>
    <meta name="description" content="Trouvez les réponses à vos questions sur IBIG FactPro : facturation, clients, stock, paiements, abonnement et support. 100 réponses disponibles.">
    <meta property="og:title" content="FAQ IBIG FactPro">
    <meta property="og:description" content="Trouvez les réponses à vos questions sur IBIG FactPro : facturation, clients, stock, paiements, abonnement et support. 100 réponses disponibles.">
    <link rel="canonical" href="https://factpro.ibigsoft.com/faq">
  </Head>

  <PublicNav />

  <!-- ── Hero Header ── -->
  <section style="background: linear-gradient(135deg, #002D5B 0%, #0062CC 60%, #1a56db 100%);" class="pt-28 pb-16 px-4">
    <div class="max-w-4xl mx-auto text-center">
      <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold mb-5 text-blue-200" style="background: rgba(255,255,255,0.12);">
        <span>❓</span> Centre de questions fréquentes
      </div>
      <h1 class="text-4xl sm:text-5xl font-extrabold text-white mb-4 leading-tight">
        Comment pouvons-nous<br class="hidden sm:block" /> vous aider ?
      </h1>
      <p class="text-blue-200 text-lg mb-2">
        <span class="text-white font-bold">{{ totalFaqs }} réponses disponibles</span> réparties en {{ categories.length }} catégories
      </p>
      <p class="text-blue-300 text-sm mb-8">Trouvez rapidement la réponse à votre question</p>

      <!-- Search -->
      <div class="relative max-w-2xl mx-auto">
        <span class="absolute inset-y-0 left-4 flex items-center text-gray-400 text-lg pointer-events-none">🔍</span>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Rechercher parmi les 100 questions..."
          class="w-full pl-11 pr-10 py-4 rounded-2xl border-0 shadow-xl text-gray-900 text-base focus:outline-none focus:ring-4 focus:ring-blue-300"
        />
        <button
          v-if="searchQuery"
          @click="searchQuery = ''"
          class="absolute inset-y-0 right-4 flex items-center text-gray-400 hover:text-gray-600"
        >✕</button>
      </div>
      <p v-if="isSearching" class="mt-3 text-sm text-blue-200">
        <template v-if="searchResultCount > 0">
          {{ searchResultCount }} résultat(s) pour « {{ searchQuery }} »
        </template>
        <template v-else>Aucun résultat — essayez d'autres mots-clés</template>
      </p>
    </div>
  </section>

  <!-- ── Main content ── -->
  <section class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
      <div class="flex flex-col lg:flex-row gap-8">

        <!-- ── Sidebar categories (desktop) ── -->
        <aside class="hidden lg:block w-64 flex-shrink-0">
          <div class="sticky top-8 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
              <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Catégories</p>
            </div>
            <nav class="py-2">
              <button
                v-for="cat in categories"
                :key="cat.id"
                @click="selectCategory(cat.id); searchQuery = ''"
                class="w-full flex items-center gap-3 px-4 py-2.5 text-left transition-all duration-150 group"
                :class="activeCategory === cat.id && !isSearching
                  ? 'bg-blue-50 border-r-2 border-blue-600'
                  : 'hover:bg-gray-50'"
              >
                <span class="text-lg flex-shrink-0">{{ cat.icon }}</span>
                <div class="flex-1 min-w-0">
                  <span
                    class="text-sm font-medium block truncate"
                    :class="activeCategory === cat.id && !isSearching ? 'text-blue-700' : 'text-gray-700 group-hover:text-gray-900'"
                  >{{ cat.title }}</span>
                </div>
                <span
                  class="text-xs font-bold px-1.5 py-0.5 rounded-full flex-shrink-0"
                  :style="activeCategory === cat.id && !isSearching
                    ? `background:${cat.color}18; color:${cat.color}`
                    : 'background:#f3f4f6; color:#9ca3af'"
                >{{ cat.faqs.length }}</span>
              </button>
            </nav>
          </div>
        </aside>

        <!-- ── Mobile category pills (horizontal scroll) ── -->
        <div class="lg:hidden -mx-4 px-4 overflow-x-auto pb-2">
          <div class="flex gap-2 w-max">
            <button
              v-for="cat in categories"
              :key="cat.id"
              @click="selectCategory(cat.id); searchQuery = ''"
              class="flex-shrink-0 flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold transition-all duration-150 whitespace-nowrap"
              :class="activeCategory === cat.id && !isSearching
                ? 'text-white shadow-md'
                : 'bg-white text-gray-600 border border-gray-200 hover:border-gray-300'"
              :style="activeCategory === cat.id && !isSearching ? `background:${cat.color}` : ''"
            >
              <span>{{ cat.icon }}</span>
              <span>{{ cat.title }}</span>
            </button>
          </div>
        </div>

        <!-- ── FAQ content ── -->
        <div class="flex-1 min-w-0">

          <!-- Search results -->
          <template v-if="isSearching">
            <div v-if="filteredCategories.length === 0" class="text-center py-20">
              <div class="text-5xl mb-4">🔍</div>
              <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucun résultat trouvé</h3>
              <p class="text-gray-500 mb-6">Essayez avec d'autres mots-clés ou contactez notre support.</p>
              <div class="flex flex-wrap justify-center gap-3">
                <button
                  @click="searchQuery = ''"
                  class="px-5 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors"
                >Effacer la recherche</button>
                <button
                  @click="openSara"
                  class="px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-900 hover:shadow-md transition-all"
                  style="background:#F0C040"
                >Parler à SARA</button>
              </div>
            </div>

            <div v-else class="space-y-6">
              <p class="text-sm text-gray-500 mb-4">{{ searchResultCount }} résultat(s) pour « <strong class="text-gray-800">{{ searchQuery }}</strong> »</p>
              <div
                v-for="cat in filteredCategories"
                :key="cat.id"
                class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden"
              >
                <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100" :style="`border-left: 4px solid ${cat.color}`">
                  <span class="text-xl">{{ cat.icon }}</span>
                  <span class="font-bold text-gray-800">{{ cat.title }}</span>
                  <span class="text-xs text-gray-400 ml-auto">{{ cat.faqs.length }} résultat(s)</span>
                </div>
                <div class="divide-y divide-gray-100">
                  <div v-for="(faq, idx) in cat.faqs" :key="idx">
                    <button
                      @click="toggleFaq(cat.id + '-search', idx)"
                      class="w-full flex items-start gap-3 px-6 py-4 text-left hover:bg-gray-50 transition-colors duration-150 focus:outline-none"
                    >
                      <span
                        class="mt-0.5 flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                        :style="isOpen(cat.id + '-search', idx) ? `background:${cat.color}; color:white` : `background:${cat.color}15; color:${cat.color}`"
                      >{{ isOpen(cat.id + '-search', idx) ? '−' : '+' }}</span>
                      <span class="font-medium text-gray-800 text-sm leading-relaxed flex-1" v-html="highlight(faq.q)" />
                    </button>
                    <Transition enter-active-class="transition-all duration-200 ease-out" enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-screen" leave-active-class="transition-all duration-150 ease-in" leave-from-class="opacity-100 max-h-screen" leave-to-class="opacity-0 max-h-0">
                      <div v-if="isOpen(cat.id + '-search', idx)" class="overflow-hidden">
                        <div class="px-6 pb-5">
                          <div class="ml-9 text-sm text-gray-600 leading-relaxed bg-gray-50 rounded-xl p-4 border-l-4" :style="`border-left-color: ${cat.color}`" v-html="highlight(faq.a)" />
                        </div>
                      </div>
                    </Transition>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- Category FAQs -->
          <template v-else>
            <div
              v-for="cat in displayedCategories"
              :key="cat.id"
              class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden"
            >
              <!-- Category header -->
              <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100" :style="`border-left: 5px solid ${cat.color}`">
                <span class="text-3xl">{{ cat.icon }}</span>
                <div>
                  <h2 class="font-bold text-gray-900 text-lg">{{ cat.title }}</h2>
                  <p class="text-sm text-gray-500">{{ cat.faqs.length }} questions dans cette catégorie</p>
                </div>
              </div>

              <!-- FAQ accordion -->
              <div class="divide-y divide-gray-100">
                <div v-for="(faq, idx) in cat.faqs" :key="idx">
                  <button
                    @click="toggleFaq(cat.id, idx)"
                    class="w-full flex items-start gap-3 px-6 py-4 text-left hover:bg-gray-50 transition-colors duration-150 focus:outline-none group"
                  >
                    <span
                      class="mt-0.5 flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200"
                      :style="isOpen(cat.id, idx) ? `background:${cat.color}; color:white` : `background:${cat.color}15; color:${cat.color}`"
                    >{{ isOpen(cat.id, idx) ? '−' : '+' }}</span>
                    <span class="font-medium text-gray-800 text-sm leading-relaxed flex-1 group-hover:text-gray-900">{{ faq.q }}</span>
                  </button>

                  <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 max-h-0"
                    enter-to-class="opacity-100 max-h-screen"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 max-h-screen"
                    leave-to-class="opacity-0 max-h-0"
                  >
                    <div v-if="isOpen(cat.id, idx)" class="overflow-hidden">
                      <div class="px-6 pb-5">
                        <div
                          class="ml-9 text-sm text-gray-600 leading-relaxed bg-gray-50 rounded-xl p-4 border-l-4"
                          :style="`border-left-color: ${cat.color}`"
                        >{{ faq.a }}</div>
                      </div>
                    </div>
                  </Transition>
                </div>
              </div>
            </div>
          </template>

          <!-- ── Contact / SARA CTA ── -->
          <div class="mt-8 rounded-2xl border-2 border-dashed border-gray-200 p-8 text-center bg-white">
            <div class="text-4xl mb-3">💬</div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Vous n'avez pas trouvé la réponse ?</h3>
            <p class="text-gray-500 mb-6 max-w-md mx-auto text-sm">
              Notre équipe est disponible 7j/7. SARA répond instantanément 24h/24 à toutes vos questions.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
              <button
                @click="openSara"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-gray-900 transition-all hover:shadow-lg hover:-translate-y-0.5"
                style="background:#F0C040"
              >
                <span>🤖</span> Parler à SARA (IA)
              </button>
              <a
                href="https://wa.me/2250778882592"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-white transition-all hover:shadow-lg hover:-translate-y-0.5"
                style="background:#25D366"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
              </a>
              <a
                href="mailto:support@ibigsoft.com"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold text-sm text-white transition-all hover:shadow-lg hover:-translate-y-0.5"
                style="background:#0062CC"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Email support
              </a>
            </div>
            <p class="mt-4 text-xs text-gray-400">Temps de réponse moyen : &lt; 2h en semaine · Support WhatsApp 24h/24</p>
          </div>

        </div>
      </div>
    </div>
  </section>

  <PublicFooter />
</template>

<style scoped>
.max-h-0 { max-height: 0; }
.max-h-screen { max-height: 100vh; }
</style>
