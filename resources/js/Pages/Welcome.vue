<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';
import Sara from '@/Components/Sara.vue';
import CookieBanner from '@/Components/CookieBanner.vue';
import WhatsAppButton from '@/Components/WhatsAppButton.vue';
import Analytics from '@/Components/Analytics.vue';

const props = defineProps({
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

/* ── Langue ── */
const lang = ref('fr');
const t = computed(() => lang.value === 'fr' ? FR : EN);
function toggleLang() { lang.value = lang.value === 'fr' ? 'en' : 'fr'; }

/* ── Tarifs ── */
const billing   = ref('monthly');
const plans     = ref([]);
const loadingPlans = ref(true);
const fmt = (v) => v == null ? '–' : Number(v).toLocaleString('fr-FR');
const limitLabel = (v) => (v === 'unlimited' || v == null ? (lang.value === 'fr' ? 'Illimité' : 'Unlimited') : fmt(v));

onMounted(async () => {
    try {
        const { data } = await axios.get('/pricing-data');
        const all = Array.isArray(data) ? data : (data.plans ?? []);
        // Afficher max 4 plans sur le landing (les 4 premiers triés par sort_order)
        plans.value = all.slice(0, 4);
    } catch {}
    loadingPlans.value = false;
});

/* ── FAQ ── */
const openFaq = ref(null);
const toggleFaq = (i) => (openFaq.value = openFaq.value === i ? null : i);

/* ── Hero slides ── */
const slideIndex = ref(0);
const slidePaused = ref(false);
const SLIDES = [
    {
        bg: 'linear-gradient(135deg,#001120 0%,#001d3d 50%,#003580 100%)',
        accent: '#0062CC',
        accentLight: '#3b89ff',
        fr: { tag: 'Facturation professionnelle', h1: 'Vos factures, prêtes\nen 30 secondes', sub: 'Générez des factures conformes OHADA avec QR code anti-falsification. Envoyez par email ou WhatsApp. Encaissez via Mobile Money en un clic.', cta1: 'Créer ma première facture →', cta2: 'Voir la démo' },
        en: { tag: 'Professional invoicing', h1: 'Your invoices, ready\nin 30 seconds', sub: 'Generate OHADA-compliant invoices with anti-fraud QR codes. Send by email or WhatsApp. Get paid via Mobile Money in one click.', cta1: 'Create my first invoice →', cta2: 'Watch demo' },
        doc: {
            type: 'FACTURE', num: 'FAC-2024-0842',
            emetteur: 'KOFFI & ASSOCIÉS SARL',
            emetteurSub: 'RC CI-ABJ-2019-B-15234 · RCCM 06-B-15234',
            client: 'ORANGE CÔTE D\'IVOIRE S.A.',
            clientSub: 'Direction Achats · Abidjan-Plateau, CI',
            date: '22 juillet 2024', echeance: '21 août 2024',
            statusLabel: '✓ PAYÉE', statusBg: '#d1fae5', statusFg: '#065f46',
            headerBg: '#001d3d',
            rows: [
                { desc: 'Audit infrastructure réseau & sécurité', qty: '1', pu: '350 000', total: '350 000' },
                { desc: 'Déploiement VLAN multi-sites (×3 sites)', qty: '3', pu: '85 000', total: '255 000' },
                { desc: 'Formation équipe IT (5 techniciens × 3j)', qty: '15j', pu: '18 000', total: '270 000' },
                { desc: 'Support prioritaire 12 mois', qty: '1', pu: '180 000', total: '180 000' },
            ],
            ht: '1 055 000', tva: '190 000', ttc: '1 245 000', devise: 'FCFA',
            equiv: '≈ 1 898 € · 2 068 $',
            payMode: 'Wave CI — +225 07 08 09 10',
            qrNote: 'Vérifiez sur factpro.ibigsoft.com',
        },
    },
    {
        bg: 'linear-gradient(135deg,#0d1117 0%,#1a1000 50%,#3d2200 100%)',
        accent: '#d97706',
        accentLight: '#fbbf24',
        fr: { tag: 'Devis interactif & signature', h1: 'Vos devis acceptés\nsans rendez-vous', sub: 'Envoyez un lien de devis signable en ligne. Le client accepte et signe directement depuis son téléphone. Convertissez en facture en un clic.', cta1: 'Créer mon premier devis →', cta2: 'Voir la démo' },
        en: { tag: 'Interactive quotes & e-signature', h1: 'Quotes accepted\nwithout meetings', sub: 'Send a signable quote link online. The client accepts and signs directly from their phone. Convert to invoice in one click.', cta1: 'Create my first quote →', cta2: 'Watch demo' },
        doc: {
            type: 'DEVIS', num: 'DEV-2024-0317',
            emetteur: 'SOGEMI BTP CONSTRUCTION',
            emetteurSub: 'RCCM SN-DKR-2021-B-8811 · NINEA 00789241',
            client: 'RÉPUBLIQUE DU SÉNÉGAL — DGPU',
            clientSub: 'Direction Générale des Projets Urbains · Dakar',
            date: '18 juillet 2024', echeance: 'Valide 30 jours',
            statusLabel: '✍ ACCEPTÉ', statusBg: '#fef3c7', statusFg: '#92400e',
            headerBg: '#3d2200',
            rows: [
                { desc: 'Étude géotechnique & plan masse', qty: '1', pu: '620 000', total: '620 000' },
                { desc: 'Fourniture & pose béton armé (m³)', qty: '120m³', pu: '15 000', total: '1 800 000' },
                { desc: 'Main d\'œuvre qualifiée (ouvriers spécialisés)', qty: '45j', pu: '22 000', total: '990 000' },
                { desc: 'Location grue & équipements lourds', qty: '10j', pu: '85 000', total: '850 000' },
            ],
            ht: '4 260 000', tva: '766 800', ttc: '5 026 800', devise: 'FCFA',
            equiv: '≈ 7 661 € · 8 350 $',
            payMode: 'Virement SGBS — RIB joint',
            qrNote: 'Signé électroniquement',
        },
    },
    {
        bg: 'linear-gradient(135deg,#001a0d 0%,#003320 50%,#00552e 100%)',
        accent: '#059669',
        accentLight: '#34d399',
        fr: { tag: 'Caisse POS & commerce', h1: 'Votre caisse tactile,\npartout en Afrique', sub: 'Point de vente complet : scan code-barres, impression tickets 58/80mm, gestion stocks, clôture de caisse. Fonctionne hors-ligne.', cta1: 'Ouvrir ma caisse →', cta2: 'Voir la démo' },
        en: { tag: 'POS & retail', h1: 'Your touchscreen POS,\nanywhere in Africa', sub: 'Complete point of sale: barcode scan, 58/80mm thermal printing, stock management, end-of-day reports. Works offline.', cta1: 'Open my cash register →', cta2: 'Watch demo' },
        doc: {
            type: 'BON DE LIVRAISON', num: 'BL-2024-1124',
            emetteur: 'PROMO DISTRIBUTION SARL',
            emetteurSub: 'RC CM-YAO-2020-B-3344 · NIU P012345678M',
            client: 'HYPERMARCHÉ CENTRAL YAOUNDÉ',
            clientSub: 'Service Approvisionnement · Yaoundé, CM',
            date: '22 juillet 2024', echeance: 'Livré le 22/07/2024',
            statusLabel: '✓ LIVRÉ', statusBg: '#d1fae5', statusFg: '#065f46',
            headerBg: '#003a1a',
            rows: [
                { desc: 'Huile de palme raffinée 5L (cartons)', qty: '120 crt', pu: '3 000', total: '360 000' },
                { desc: 'Riz parfumé longue grain 25kg', qty: '40 sacs', pu: '7 000', total: '280 000' },
                { desc: 'Farine de blé type 45 — 50kg', qty: '30 sacs', pu: '6 500', total: '195 000' },
                { desc: 'Sucre cristallisé 50kg', qty: '25 sacs', pu: '8 500', total: '212 500' },
            ],
            ht: '1 047 500', tva: '0 (exonéré)', ttc: '1 047 500', devise: 'FCFA',
            equiv: '≈ 1 596 € · 1 739 $',
            payMode: 'MTN MoMo — +237 67 00 00 00',
            qrNote: 'Livraison confirmée par GPS',
        },
    },
    {
        bg: 'linear-gradient(135deg,#0d0020 0%,#1a0040 50%,#2d0070 100%)',
        accent: '#7c3aed',
        accentLight: '#a78bfa',
        fr: { tag: 'Tableau de bord & KPIs', h1: 'Pilotez votre activité\nen temps réel', sub: 'Dashboard complet : chiffre d\'affaires, taux de recouvrement, documents en attente, top clients. Toutes vos données business en un coup d\'œil.', cta1: 'Accéder au dashboard →', cta2: 'Voir la démo' },
        en: { tag: 'Dashboard & KPIs', h1: 'Run your business\nin real time', sub: 'Full dashboard: revenue, collection rate, pending documents, top clients. All your business data at a glance.', cta1: 'Go to dashboard →', cta2: 'Watch demo' },
        doc: {
            type: 'RELEVÉ DE COMPTE CLIENT', num: 'REL-2024-Q3',
            emetteur: 'CABINET COMPTABLE DIALLO & FILS',
            emetteurSub: 'Expert-comptable agréé ONECCA-BF · Ouagadougou',
            client: 'SOCIÉTÉ MINE OR BURKINA SA',
            clientSub: 'Direction Financière · Ouagadougou, BF',
            date: '01 juil. – 30 sept. 2024', echeance: 'Trimestre Q3 2024',
            statusLabel: '◉ SOLDE DÛ', statusBg: '#ede9fe', statusFg: '#5b21b6',
            headerBg: '#2d0070',
            rows: [
                { desc: 'Facture FAC-2024-0701 — Audit fiscal annuel', qty: '1', pu: '850 000', total: '850 000' },
                { desc: 'Facture FAC-2024-0745 — Liasse fiscale BF', qty: '1', pu: '420 000', total: '420 000' },
                { desc: 'Facture FAC-2024-0803 — Conseil juridique', qty: '4h', pu: '75 000', total: '300 000' },
                { desc: 'Facture FAC-2024-0841 — Formation SYSCOHADA', qty: '2j', pu: '180 000', total: '360 000' },
            ],
            ht: '1 930 000', tva: '347 400', ttc: '2 277 400', devise: 'FCFA',
            equiv: '≈ 3 471 € · 3 782 $',
            payMode: 'Orange Money BF — +226 70 00 00 00',
            qrNote: 'Conforme SYSCOHADA révisé',
        },
    },
];
let slideTimer = null;
onMounted(() => {
    slideTimer = setInterval(() => {
        if (!slidePaused.value) slideIndex.value = (slideIndex.value + 1) % SLIDES.length;
    }, 5500);
});
const slideBase = computed(() => SLIDES[slideIndex.value]);
const slide = computed(() => ({ ...slideBase.value, ...slideBase.value[lang.value] }));

/* ── Compteur animé ── */
const counters = ref({ clients: 0, docs: 0, pays: 0, uptime: 0 });
const targets  = { clients: 120, docs: 4800, pays: 5, uptime: 99 };
onMounted(() => {
    const duration = 2000;
    const step = 16;
    const steps = duration / step;
    let frame = 0;
    const timer = setInterval(() => {
        frame++;
        const progress = frame / steps;
        const ease = 1 - Math.pow(1 - progress, 3);
        Object.keys(targets).forEach(k => {
            counters.value[k] = Math.round(targets[k] * Math.min(ease, 1));
        });
        if (frame >= steps) clearInterval(timer);
    }, step);
});

/* ── Données statiques FR/EN ── */
const FR = {
    hero: {
        badge: '7 jours d\'essai gratuit · sans carte bancaire',
        h1a: 'Les outils de facturation des multinationales,',
        h1b: 'pour chaque entrepreneur',
        sub: 'Devis, factures et documents commerciaux professionnels avec QR anti-falsification, impression thermique, Mobile Money et multi-devises. Du vendeur de rue au directeur de PME.',
        cta1: 'Démarrer l\'essai gratuit 7 jours →',
        cta2: 'Voir une démo live →',
        cta3: 'Voir les tarifs',
        note: 'Sans carte bancaire · Résiliable à tout moment',
    },
    stats: [
        { value: 'clients', suffix: '+', label: 'Clients actifs' },
        { value: 'docs',    suffix: '+', label: 'Documents générés' },
        { value: 'pays',    suffix: '',  label: 'Pays couverts' },
        { value: 'uptime',  suffix: '%', label: 'Disponibilité' },
    ],
    trustBadges: ['Conforme OHADA', 'Factur-X 2026', 'Mobile Money', 'QR anti-falsification', 'SSL sécurisé'],
    featuresTitle: 'Tout ce qu\'il faut pour facturer comme un pro',
    featuresSub: 'Une suite complète pensée pour les entrepreneurs et PME d\'Afrique et du monde.',
    features: [
        { icon: '🔐', title: 'QR Anti-falsification', text: 'Chaque document porte un QR unique vérifiable en ligne. Zéro fraude possible.' },
        { icon: '🖨️', title: 'Impression thermique', text: 'Compatible imprimantes 58mm et 80mm. Reçus et tickets instantanés.' },
        { icon: '🏪', title: 'Caisse POS', text: 'Point de vente tactile avec gestion de tables, multi-caissier et rapport X.' },
        { icon: '💱', title: 'Multi-devises', text: 'FCFA, EUR, USD, GHS… Taux de change en temps réel ou personnalisables.' },
        { icon: '📱', title: 'Mobile Money', text: 'Orange Money, Wave, MTN, Moov intégrés. Paiement en un clic depuis la facture.' },
        { icon: '🌐', title: 'Portail client', text: 'Vos clients consultent, téléchargent et paient leurs factures 24h/24.' },
        { icon: '📊', title: 'Comptabilité', text: 'Export FEC, Sage 100, QuickBooks, Pennylane. Conformité OHADA incluse.' },
        { icon: '⚡', title: 'API REST', text: 'Connectez vos outils : Zapier, Make, vos propres apps. 100% documentée.' },
    ],
    whyTitle: 'Pourquoi IBIG FactPro ?',
    why: [
        { icon: '🌍', title: 'Afrique-first', text: 'Conçu pour les réalités africaines : Mobile Money, OHADA, multi-devises, hors-ligne.' },
        { icon: '🔒', title: 'Infalsifiable', text: 'QR unique sur chaque document. Vos factures sont authentifiables instantanément.' },
        { icon: '🚀', title: 'Tout-en-un', text: 'Facturation, caisse POS, stock, compta, CRM, RH — une seule plateforme.' },
        { icon: '⚡', title: 'Prêt en 2 minutes', text: 'Inscription → première facture en moins de 2 minutes. Aucune formation requise.' },
    ],
    pricingTitle: 'Des tarifs simples, en FCFA',
    pricingSub: 'Choisissez le forfait adapté. Changez ou résiliez quand vous voulez.',
    monthly: 'Mensuel',
    yearly: 'Annuel',
    popular: 'Populaire',
    start: 'Commencer',
    perMonth: 'FCFA / mois',
    compareFull: 'Comparer tous les forfaits en détail →',
    partnersTitle: 'Gagnez en vendant FactPro',
    partnersSub: 'Rejoignez IBIG Partners, le programme d\'affiliation multi-niveaux du groupe IBIG SARL. Vendez, parrainez, touchez des commissions sur 3 niveaux.',
    faqTitle: 'Questions fréquentes',
    faqs: [
        { q: 'L\'essai est-il vraiment gratuit ?', a: 'Oui. 7 jours complets, sans carte bancaire. Aucun prélèvement automatique à la fin.' },
        { q: 'Qu\'est-ce que le filigrane d\'essai ?', a: 'Pendant l\'essai, vos documents portent la mention « VERSION ESSAI ». Elle disparaît dès votre premier abonnement.' },
        { q: 'Quels moyens de paiement acceptez-vous ?', a: 'Mobile Money (Orange Money, Wave, MTN, Moov), virement bancaire national et international.' },
        { q: 'Puis-je résilier à tout moment ?', a: 'Oui. Aucun engagement : abonnements mensuels ou annuels, résiliables depuis votre espace.' },
        { q: 'Fonctionne-t-il sans connexion internet ?', a: 'Oui, grâce au mode PWA hors-ligne avec synchronisation automatique dès le retour de connexion.' },
        { q: 'Puis-je utiliser ma propre imprimante thermique ?', a: 'Oui. Compatible avec toutes les imprimantes thermiques 58mm et 80mm du marché.' },
    ],
    ctaTitle: 'Prêt à facturer comme les grands ?',
    ctaSub: 'Lancez votre essai gratuit de 7 jours. Sans carte bancaire, sans engagement.',
    ctaBtn1: 'Démarrer gratuitement',
    ctaBtn2: 'J\'ai déjà un compte',
};

const EN = {
    hero: {
        badge: '7-day free trial · no credit card',
        h1a: 'Enterprise-grade invoicing tools,',
        h1b: 'for every entrepreneur',
        sub: 'Professional quotes, invoices and commercial documents with QR anti-fraud, thermal printing, Mobile Money and multi-currency. From street vendors to SME directors.',
        cta1: 'Start free 7-day trial →',
        cta2: 'See live demo →',
        cta3: 'View pricing',
        note: 'No credit card · Cancel anytime',
    },
    stats: [
        { value: 'clients', suffix: '+', label: 'Active clients' },
        { value: 'docs',    suffix: '+', label: 'Documents generated' },
        { value: 'pays',    suffix: '',  label: 'Countries covered' },
        { value: 'uptime',  suffix: '%', label: 'Uptime' },
    ],
    trustBadges: ['OHADA Compliant', 'Factur-X 2026', 'Mobile Money', 'QR Anti-fraud', 'SSL Secured'],
    featuresTitle: 'Everything you need to invoice like a pro',
    featuresSub: 'A complete suite designed for entrepreneurs and SMEs in Africa and beyond.',
    features: [
        { icon: '🔐', title: 'Anti-fraud QR', text: 'Every document has a unique verifiable QR code. Zero fraud possible.' },
        { icon: '🖨️', title: 'Thermal printing', text: 'Compatible with 58mm and 80mm printers. Instant receipts and tickets.' },
        { icon: '🏪', title: 'POS Register', text: 'Touchscreen point of sale with table management, multi-cashier and X-report.' },
        { icon: '💱', title: 'Multi-currency', text: 'FCFA, EUR, USD, GHS… Real-time or custom exchange rates.' },
        { icon: '📱', title: 'Mobile Money', text: 'Orange Money, Wave, MTN, Moov integrated. One-click payment from invoice.' },
        { icon: '🌐', title: 'Client portal', text: 'Your clients view, download and pay their invoices 24/7.' },
        { icon: '📊', title: 'Accounting', text: 'FEC, Sage 100, QuickBooks, Pennylane export. OHADA compliance included.' },
        { icon: '⚡', title: 'REST API', text: 'Connect your tools: Zapier, Make, your own apps. Fully documented.' },
    ],
    whyTitle: 'Why IBIG FactPro?',
    why: [
        { icon: '🌍', title: 'Africa-first', text: 'Built for African realities: Mobile Money, OHADA, multi-currency, offline mode.' },
        { icon: '🔒', title: 'Tamper-proof', text: 'Unique QR on every document. Your invoices are instantly verifiable.' },
        { icon: '🚀', title: 'All-in-one', text: 'Invoicing, POS, stock, accounting, CRM, HR — one single platform.' },
        { icon: '⚡', title: 'Ready in 2 minutes', text: 'Sign up → first invoice in under 2 minutes. No training required.' },
    ],
    pricingTitle: 'Simple pricing in FCFA',
    pricingSub: 'Choose the right plan. Upgrade or cancel whenever you want.',
    monthly: 'Monthly',
    yearly: 'Yearly',
    popular: 'Popular',
    start: 'Get started',
    perMonth: 'FCFA / month',
    compareFull: 'Compare all plans in detail →',
    partnersTitle: 'Earn by selling FactPro',
    partnersSub: 'Join IBIG Partners, the multi-level affiliate program of IBIG SARL group. Sell, refer, earn commissions on 3 levels.',
    faqTitle: 'Frequently asked questions',
    faqs: [
        { q: 'Is the trial really free?', a: 'Yes. Full 7 days, no credit card. No automatic billing at the end.' },
        { q: 'What is the trial watermark?', a: 'During trial, your documents display "TRIAL VERSION". It disappears with your first subscription.' },
        { q: 'What payment methods do you accept?', a: 'Mobile Money (Orange Money, Wave, MTN, Moov), national and international bank transfer.' },
        { q: 'Can I cancel anytime?', a: 'Yes. No commitment: monthly or yearly subscriptions, cancellable from your account.' },
        { q: 'Does it work offline?', a: 'Yes, thanks to PWA offline mode with automatic sync when connection returns.' },
        { q: 'Can I use my own thermal printer?', a: 'Yes. Compatible with all 58mm and 80mm thermal printers on the market.' },
    ],
    ctaTitle: 'Ready to invoice like the big players?',
    ctaSub: 'Start your free 7-day trial. No credit card, no commitment.',
    ctaBtn1: 'Get started free',
    ctaBtn2: 'I already have an account',
};

/* ── Info Bar ── */
const showInfoBar = ref(!sessionStorage.getItem('factpro_infobar_hidden'));
function dismissInfoBar() {
    sessionStorage.setItem('factpro_infobar_hidden', '1');
    showInfoBar.value = false;
}

/* ── Testimonials ── */
const testimonials = [
    { name: 'Kouamé A.', role: 'Commerçant, Abidjan', text: 'FactPro a transformé ma gestion. Je crée mes factures en 2 minutes et mes clients reçoivent tout automatiquement.', rating: 5, avatar: 'K' },
    { name: 'Marie T.', role: 'Restauratrice, Dakar', text: 'Le suivi des encaissements Mobile Money est parfait pour mon activité. Je recommande à tous les entrepreneurs.', rating: 5, avatar: 'M' },
    { name: 'Jean-Paul B.', role: 'IT Consultant, Lomé', text: "L'API REST m'a permis d'intégrer FactPro dans mes outils existants. Support réactif et excellent.", rating: 5, avatar: 'J' },
];

const partnerStatuses = [
    { label: 'STARTER', icon: '⭐', color: '#6b7280', bg: '#f9fafb', min: 0, desc_fr: 'Débutant actif', desc_en: 'Active beginner' },
    { label: 'SILVER',  icon: '⭐⭐', color: '#64748b', bg: '#f1f5f9', min: 5,  desc_fr: '5+ ventes/mois', desc_en: '5+ sales/month' },
    { label: 'GOLD',    icon: '⭐⭐⭐', color: '#b45309', bg: '#fefce8', min: 15, desc_fr: '15+ ventes/mois', desc_en: '15+ sales/month' },
    { label: 'MASTER',  icon: '🏆', color: '#7c3aed', bg: '#faf5ff', min: 30, desc_fr: '30+ ventes/mois', desc_en: '30+ sales/month' },
];

const partnerCommissions = [
    { level: 'N1', pct: '20%', label_fr: 'Vos ventes directes', label_en: 'Your direct sales' },
    { level: 'N2', pct: '10%', label_fr: 'Ventes de vos filleuls', label_en: 'Your referrals\' sales' },
    { level: 'N3', pct: '5%',  label_fr: 'Ventes de leurs filleuls', label_en: 'Their referrals\' sales' },
];
</script>

<template>
    <Head title="IBIG FactPro — Logiciel de facturation pour PME africaines">
        <meta name="description" content="Créez vos factures, devis et bons de livraison en quelques secondes. Logiciel de gestion commerciale OHADA pour les PME d'Afrique francophone.">
        <meta property="og:title" content="IBIG FactPro — Logiciel de facturation pour PME africaines">
        <meta property="og:description" content="Créez vos factures, devis et bons de livraison en quelques secondes. Logiciel de gestion commerciale OHADA pour les PME d'Afrique francophone.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://factpro.ibigsoft.com">
        <meta property="og:site_name" content="IBIG FactPro">
        <meta property="og:locale" content="fr_FR">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="IBIG FactPro — Logiciel de facturation PME Afrique">
        <meta name="twitter:description" content="Facturation, devis, stocks, caisse POS. Conforme OHADA. Essai gratuit 14 jours.">
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "SoftwareApplication",
            "name": "IBIG FactPro",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": "Web, iOS, Android",
            "description": "Logiciel de facturation et gestion commerciale pour PME africaines, conforme OHADA",
            "url": "https://factpro.ibigsoft.com",
            "author": { "@type": "Organization", "name": "IBIG Soft SARL", "address": { "@type": "PostalAddress", "addressLocality": "Abidjan", "addressCountry": "CI" } },
            "offers": { "@type": "Offer", "price": "0", "priceCurrency": "XOF", "description": "Essai gratuit 14 jours" },
            "aggregateRating": { "@type": "AggregateRating", "ratingValue": "4.8", "reviewCount": "312" }
        }
        </script>
    </Head>

    <div class="min-h-screen bg-white text-gray-800">
        <!-- ═══════════════════════════════ INFO BAR ═══════════════════════════════ -->
        <div v-if="showInfoBar" style="background:#001d3d" class="relative flex items-center justify-center px-4 py-2 text-xs text-white">
            <span class="mr-1">🎉</span>
            <span v-if="lang === 'fr'">
                Essai gratuit 7 jours · Sans carte bancaire · Accès complet immédiat →
                <a href="/register" class="ml-1 font-bold underline" style="color:#F0C040">Commencer gratuitement</a>
            </span>
            <span v-else>
                7-day free trial · No credit card · Full access immediately →
                <a href="/register" class="ml-1 font-bold underline" style="color:#F0C040">Get started free</a>
            </span>
            <button @click="dismissInfoBar" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/60 hover:text-white text-base leading-none" aria-label="Fermer">×</button>
        </div>

        <!-- NAV avec toggle langue -->
        <nav class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
                <a href="/" class="flex items-center">
                    <img src="/logo.svg" alt="IBIG FactPro" class="h-10 w-auto" />
                </a>
                <div class="hidden items-center gap-8 md:flex">
                    <a href="/#fonctionnalites" class="text-sm font-semibold text-gray-600 hover:text-brand-600">{{ lang === 'fr' ? 'Fonctionnalités' : 'Features' }}</a>
                    <a href="/pricing" class="text-sm font-semibold text-gray-600 hover:text-brand-600">{{ lang === 'fr' ? 'Tarifs' : 'Pricing' }}</a>
                    <a href="/#partners" class="text-sm font-semibold text-gray-600 hover:text-brand-600">Partners</a>
                    <a href="/#faq" class="text-sm font-semibold text-gray-600 hover:text-brand-600">FAQ</a>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Lang toggle -->
                    <button @click="toggleLang" class="flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-bold text-gray-600 transition hover:border-brand-400 hover:text-brand-600">
                        <span>{{ lang === 'fr' ? '🇫🇷 FR' : '🇬🇧 EN' }}</span>
                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                    </button>
                    <a v-if="props.canLogin" href="/login" class="hidden px-3 py-2 text-sm font-semibold text-brand-900 hover:text-brand-600 md:block">
                        {{ lang === 'fr' ? 'Se connecter' : 'Sign in' }}
                    </a>
                    <a v-if="props.canRegister" href="/register" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-bold text-white shadow transition hover:bg-brand-700">
                        {{ lang === 'fr' ? 'Essai gratuit' : 'Free trial' }}
                    </a>
                </div>
            </div>
        </nav>

        <!-- ═══════════════════════════════ HERO SLIDER ═══════════════════════════════ -->
        <section class="relative overflow-hidden" style="min-height:94vh"
                 @mouseenter="slidePaused=true" @mouseleave="slidePaused=false">

            <!-- Fond animé qui change avec le slide -->
            <Transition name="bg-fade" mode="out-in">
                <div :key="slideIndex" class="absolute inset-0 transition-all duration-700" :style="`background:${slide.bg}`">
                    <svg class="absolute inset-0 w-full h-full opacity-4" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="g" width="60" height="60" patternUnits="userSpaceOnUse"><path d="M60 0L0 0 0 60" fill="none" stroke="white" stroke-width="0.4" opacity="0.4"/></pattern></defs><rect width="100%" height="100%" fill="url(#g)"/></svg>
                    <div class="absolute" style="top:-10%;right:-5%;width:55vw;height:55vw;border-radius:50%;animation:float1 10s ease-in-out infinite" :style="`background:radial-gradient(circle,${slide.accent}44,transparent 70%)`"></div>
                    <div class="absolute" style="bottom:-15%;left:-5%;width:40vw;height:40vw;border-radius:50%;animation:float2 14s ease-in-out infinite" :style="`background:radial-gradient(circle,rgba(240,192,64,.08),transparent 70%)`"></div>
                </div>
            </Transition>

            <!-- Contenu du slide -->
            <Transition name="slide-content" mode="out-in">
                <div :key="slideIndex" class="relative mx-auto grid max-w-7xl items-center gap-8 px-6 py-16 lg:grid-cols-2 lg:gap-16 lg:py-24" style="min-height:90vh">

                    <!-- ── COPY gauche ── -->
                    <div class="z-10 flex flex-col justify-center">
                        <!-- Tag de slide -->
                        <div class="inline-flex w-fit items-center gap-2 rounded-full px-4 py-1.5 text-xs font-bold mb-5" :style="`background:${slide.accent}22;color:${slide.accentLight};border:1px solid ${slide.accent}44`">
                            <span class="inline-block h-1.5 w-1.5 rounded-full animate-pulse" :style="`background:${slide.accentLight}`"></span>
                            {{ slide.tag }}
                        </div>

                        <h1 class="text-4xl font-black leading-[1.1] tracking-tight text-white sm:text-5xl xl:text-[3.4rem]" style="white-space:pre-line">{{ slide.h1 }}</h1>

                        <p class="mt-5 max-w-md text-base leading-relaxed" style="color:rgba(255,255,255,.72)">{{ slide.sub }}</p>

                        <!-- Proof -->
                        <div class="mt-6 flex items-center gap-3">
                            <div class="flex -space-x-2">
                                <div v-for="(c,i) in ['#0062CC','#10b981','#d97706','#ef4444','#7c3aed']" :key="i"
                                     class="h-8 w-8 rounded-full border-2 flex items-center justify-center text-xs font-extrabold text-white"
                                     style="border-color:rgba(255,255,255,.2)" :style="`background:${c}`">{{ 'KASMT'[i] }}</div>
                            </div>
                            <div class="text-sm">
                                <span class="font-bold text-white">{{ lang === 'fr' ? '120+ clients actifs' : '120+ active clients' }}</span>
                                <span class="ml-2" style="color:#F0C040">★★★★★ 4.9</span>
                            </div>
                        </div>

                        <!-- CTAs -->
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a v-if="props.canRegister" href="/register"
                               class="group inline-flex items-center gap-2 rounded-xl px-7 py-3.5 text-sm font-extrabold shadow-2xl transition-all hover:scale-105 active:scale-95"
                               style="background:linear-gradient(135deg,#F0C040,#e8a800);color:#001d3d;box-shadow:0 8px 30px rgba(240,192,64,.38)">
                                {{ slide.cta1 }}
                                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                            <a href="/demo-login"
                               class="inline-flex items-center gap-2 rounded-xl border px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10"
                               style="border-color:rgba(255,255,255,.25)">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full text-xs" style="background:rgba(255,255,255,.15)">▶</span>
                                {{ slide.cta2 }}
                            </a>
                        </div>
                        <p class="mt-3 text-xs" style="color:rgba(255,255,255,.38)">{{ t.hero.note }}</p>

                        <!-- Trust pills -->
                        <div class="mt-7 flex flex-wrap gap-2">
                            <span v-for="b in t.trustBadges" :key="b"
                                  class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold"
                                  style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.65);border:1px solid rgba(255,255,255,.11)">
                                <span :style="`color:${slide.accentLight}`">✓</span> {{ b }}
                            </span>
                        </div>
                    </div>

                    <!-- ── DOCUMENT PDF MOCKUP droite ── -->
                    <div class="z-10 flex justify-center lg:justify-end">
                        <div class="relative" style="perspective:1200px">
                            <!-- Ombre portée -->
                            <div class="absolute inset-x-4 bottom-0 h-8 rounded-b-2xl blur-2xl opacity-40" :style="`background:${slide.accent}`"></div>

                            <!-- Document A4 simulé -->
                            <div class="relative w-full max-w-sm rounded-xl overflow-hidden shadow-2xl bg-white"
                                 style="font-size:10px;line-height:1.4;transform:rotateY(-2deg) rotateX(1deg);box-shadow:0 32px 80px rgba(0,0,0,.45),0 0 0 1px rgba(255,255,255,.08)">

                                <!-- ═ EN-TÊTE DOCUMENT ═ -->
                                <div class="flex items-start justify-between px-5 py-4" :style="`background:${slide.doc.headerBg}`">
                                    <div>
                                        <!-- Logo zone -->
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="h-7 w-7 rounded flex items-center justify-center font-black text-sm" :style="`background:${slide.accent};color:white`">FP</div>
                                            <div>
                                                <div class="text-white font-extrabold text-xs">{{ slide.doc.emetteur }}</div>
                                                <div class="text-xs" style="color:rgba(255,255,255,.5)">{{ slide.doc.emetteurSub }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-black uppercase tracking-wider" :style="`color:${slide.accentLight}`">{{ slide.doc.type }}</div>
                                        <div class="text-xs text-white font-bold mt-0.5">{{ slide.doc.num }}</div>
                                        <div class="mt-1.5 rounded px-2 py-0.5 text-xs font-bold inline-block" :style="`background:${slide.doc.statusBg};color:${slide.doc.statusFg}`">{{ slide.doc.statusLabel }}</div>
                                    </div>
                                </div>

                                <!-- ═ INFOS CLIENT / DATE ═ -->
                                <div class="grid grid-cols-2 gap-0 border-b" style="border-color:#e5e7eb">
                                    <div class="px-5 py-3 border-r" style="border-color:#e5e7eb">
                                        <div class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Facturé à</div>
                                        <div class="font-extrabold text-gray-800 text-xs">{{ slide.doc.client }}</div>
                                        <div class="text-gray-400 text-xs mt-0.5">{{ slide.doc.clientSub }}</div>
                                    </div>
                                    <div class="px-5 py-3">
                                        <div class="text-xs font-black uppercase tracking-widest text-gray-400 mb-1">Dates</div>
                                        <div class="text-gray-700 text-xs"><span class="text-gray-400">Émis :</span> {{ slide.doc.date }}</div>
                                        <div class="text-gray-700 text-xs mt-0.5"><span class="text-gray-400">Échéance :</span> {{ slide.doc.echeance }}</div>
                                    </div>
                                </div>

                                <!-- ═ TABLEAU DES LIGNES ═ -->
                                <div class="px-5 pt-3">
                                    <!-- En-tête tableau -->
                                    <div class="grid text-xs font-black uppercase tracking-widest text-gray-400 pb-1.5 border-b" style="grid-template-columns:1fr 3rem 4.5rem 4.5rem;border-color:#e5e7eb">
                                        <span>Description</span>
                                        <span class="text-center">Qté</span>
                                        <span class="text-right">P.U.</span>
                                        <span class="text-right">Total</span>
                                    </div>
                                    <!-- Lignes -->
                                    <div v-for="(row, i) in slide.doc.rows" :key="i"
                                         class="grid py-2 border-b"
                                         :style="`grid-template-columns:1fr 3rem 4.5rem 4.5rem;border-color:${i%2===0?'#f3f4f6':'#e5e7eb'};background:${i%2===0?'white':'#fafafa'}`">
                                        <span class="text-gray-700 pr-2" style="line-height:1.3">{{ row.desc }}</span>
                                        <span class="text-center text-gray-500">{{ row.qty }}</span>
                                        <span class="text-right text-gray-600">{{ row.pu }}</span>
                                        <span class="text-right font-bold text-gray-800">{{ row.total }}</span>
                                    </div>
                                </div>

                                <!-- ═ TOTAUX + QR ═ -->
                                <div class="flex items-end gap-4 px-5 py-3">
                                    <!-- QR code zone -->
                                    <div class="flex-shrink-0">
                                        <div class="h-16 w-16 rounded-lg flex items-center justify-center" :style="`background:${slide.doc.headerBg}`">
                                            <svg viewBox="0 0 44 44" fill="none" class="h-12 w-12">
                                                <rect x="1" y="1" width="5" height="5" fill="white"/><rect x="2" y="2" width="3" height="3" :fill="slide.accent"/>
                                                <rect x="8" y="1" width="2" height="5" fill="white"/>
                                                <rect x="12" y="1" width="5" height="5" fill="white"/><rect x="13" y="2" width="3" height="3" :fill="slide.accent"/>
                                                <rect x="1" y="8" width="5" height="2" fill="white"/><rect x="12" y="8" width="5" height="2" fill="white"/>
                                                <rect x="1" y="12" width="5" height="5" fill="white"/><rect x="2" y="13" width="3" height="3" :fill="slide.accent"/>
                                                <rect x="8" y="12" width="2" height="2" fill="white"/>
                                                <rect x="12" y="12" width="5" height="5" fill="white"/><rect x="13" y="13" width="3" height="3" :fill="slide.accent"/>
                                                <rect x="19" y="1" width="3" height="3" fill="white"/><rect x="23" y="2" width="5" height="2" fill="white"/>
                                                <rect x="19" y="6" width="5" height="2" fill="white"/><rect x="25" y="5" width="3" height="3" fill="white"/>
                                                <rect x="19" y="10" width="3" height="7" fill="white"/><rect x="23" y="8" width="5" height="5" fill="white"/>
                                                <rect x="1" y="19" width="8" height="2" fill="white"/><rect x="11" y="19" width="6" height="2" fill="white"/>
                                                <rect x="1" y="23" width="4" height="4" fill="white"/><rect x="6" y="23" width="3" height="3" fill="white"/>
                                                <rect x="12" y="22" width="3" height="5" fill="white"/>
                                                <rect x="17" y="19" width="3" height="8" fill="white"/>
                                                <rect x="21" y="21" width="4" height="3" fill="white"/>
                                                <rect x="21" y="25" width="3" height="3" fill="white"/>
                                            </svg>
                                        </div>
                                        <div class="text-center text-xs mt-1 text-gray-400">{{ slide.doc.qrNote }}</div>
                                    </div>

                                    <!-- Blocs totaux -->
                                    <div class="flex-1 space-y-1.5">
                                        <div class="flex justify-between text-gray-500">
                                            <span>Sous-total HT</span>
                                            <span class="font-semibold text-gray-700">{{ slide.doc.ht }} {{ slide.doc.devise }}</span>
                                        </div>
                                        <div class="flex justify-between text-gray-500">
                                            <span>TVA</span>
                                            <span class="font-semibold text-gray-700">{{ slide.doc.tva }} {{ slide.doc.devise }}</span>
                                        </div>
                                        <div class="flex justify-between rounded-lg px-3 py-2 mt-1" :style="`background:${slide.accent};color:white`">
                                            <span class="font-black uppercase text-xs tracking-widest">Total TTC</span>
                                            <div class="text-right">
                                                <div class="font-black text-sm">{{ slide.doc.ttc }} {{ slide.doc.devise }}</div>
                                                <div class="text-xs opacity-70">{{ slide.doc.equiv }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ═ FOOTER DOCUMENT ═ -->
                                <div class="px-5 py-2.5 flex items-center justify-between" :style="`background:${slide.doc.headerBg}22;border-top:1px solid ${slide.doc.headerBg}33`">
                                    <span class="text-gray-500">Mode de paiement : <strong class="text-gray-700">{{ slide.doc.payMode }}</strong></span>
                                    <span class="rounded px-2 py-0.5 text-xs font-bold" :style="`background:${slide.accentLight}22;color:${slide.accent}`">IBIG FactPro</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- ── Slide controls ── -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 z-20 flex items-center gap-3">
                <button v-for="(s, i) in SLIDES" :key="i"
                        @click="slideIndex=i"
                        class="transition-all duration-400 rounded-full"
                        :class="i===slideIndex ? 'h-2.5 w-8' : 'h-2.5 w-2.5 hover:w-4'"
                        :style="i===slideIndex ? `background:${SLIDES[i].accentLight}` : 'background:rgba(255,255,255,.25)'">
                </button>
            </div>

            <!-- Slide labels (desktop) -->
            <div class="absolute right-6 top-1/2 -translate-y-1/2 z-20 hidden xl:flex flex-col gap-3">
                <button v-for="(s, i) in SLIDES" :key="i"
                        @click="slideIndex=i"
                        class="flex items-center gap-2 rounded-full py-1 pl-1 pr-3 text-xs font-bold transition-all duration-300"
                        :class="i===slideIndex ? 'opacity-100' : 'opacity-30 hover:opacity-60'"
                        :style="i===slideIndex ? `background:${s.accent}55;color:white;border:1px solid ${s.accent}` : 'background:rgba(255,255,255,.08);color:white;border:1px solid rgba(255,255,255,.1)'">
                    <span class="h-5 w-5 rounded-full flex items-center justify-center text-xs" :style="`background:${s.accent}`">{{ i+1 }}</span>
                    {{ s.doc.type }}
                </button>
            </div>

            <!-- Wave bottom -->
            <div class="absolute bottom-0 left-0 right-0 z-10 pointer-events-none">
                <svg viewBox="0 0 1440 56" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="w-full block"><path d="M0 56V28Q360 0 720 22Q1080 44 1440 18V56Z" fill="white"/></svg>
            </div>
        </section>

        <!-- ═══════════════════════════════ TRUST BADGES ═══════════════════════════════ -->
        <section class="bg-white border-t border-gray-100 py-4 px-6">
            <div class="mx-auto max-w-5xl flex flex-wrap items-center justify-center gap-4">
                <span v-for="badge in [
                    { icon: '🔒', label_fr: 'SSL/TLS Sécurisé',          label_en: 'SSL/TLS Secured' },
                    { icon: '🏦', label_fr: 'Paiement Mobile Money',      label_en: 'Mobile Money Payment' },
                    { icon: '⭐', label_fr: 'Note 4.8/5',                 label_en: 'Rated 4.8/5' },
                    { icon: '🌍', label_fr: '9 Pays Afrique',             label_en: '9 African Countries' },
                    { icon: '📋', label_fr: 'Conforme OHADA',             label_en: 'OHADA Compliant' },
                    { icon: '🔄', label_fr: 'Synchronisation temps réel', label_en: 'Real-time Sync' },
                ]" :key="badge.label_fr"
                    class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-600 shadow-sm">
                    <span>{{ badge.icon }}</span>
                    <span>{{ lang === 'fr' ? badge.label_fr : badge.label_en }}</span>
                </span>
            </div>
        </section>

        <!-- ═══════════════════════════════ STATS ═══════════════════════════════ -->
        <section class="bg-white px-6 pt-16 pb-10">
            <div class="mx-auto max-w-5xl grid grid-cols-2 gap-6 lg:grid-cols-4">
                <div v-for="stat in t.stats" :key="stat.label"
                     class="relative overflow-hidden rounded-2xl p-5 text-center shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-md">
                    <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl" style="background:linear-gradient(90deg,#0062CC,#0099ff)"></div>
                    <div class="text-4xl font-black" style="color:#0062CC">
                        {{ stat.value === 'clients' ? counters.clients.toLocaleString('fr-FR')
                         : stat.value === 'docs'    ? counters.docs.toLocaleString('fr-FR')
                         : stat.value === 'pays'    ? counters.pays
                         :                            counters.uptime }}{{ stat.suffix }}
                    </div>
                    <div class="mt-1.5 text-sm font-semibold text-gray-500">{{ stat.label }}</div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ FONCTIONNALITÉS ═══════════════════════════════ -->
        <section id="fonctionnalites" class="mx-auto max-w-7xl px-6 py-24">
            <div class="text-center">
                <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#eff6ff;color:#0062CC">{{ lang === 'fr' ? 'Fonctionnalités' : 'Features' }}</span>
                <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">{{ t.featuresTitle }}</h2>
                <p class="mx-auto mt-3 max-w-2xl text-gray-500">{{ t.featuresSub }}</p>
            </div>
            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="feature in t.features"
                    :key="feature.title"
                    class="group relative rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-2 hover:border-brand-200 hover:shadow-lg"
                >
                    <div class="grid h-12 w-12 place-items-center rounded-xl text-2xl transition group-hover:scale-110" style="background:#eff6ff">{{ feature.icon }}</div>
                    <h3 class="mt-4 font-bold text-brand-900">{{ feature.title }}</h3>
                    <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ feature.text }}</p>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ POURQUOI ═══════════════════════════════ -->
        <section class="px-6 py-24" style="background:linear-gradient(180deg,#f8faff 0%,#ffffff 100%)">
            <div class="mx-auto max-w-7xl">
                <div class="text-center">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#fef9ee;color:#b45309">{{ lang === 'fr' ? 'Notre différence' : 'Our edge' }}</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">{{ t.whyTitle }}</h2>
                </div>
                <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="item in t.why" :key="item.title"
                         class="flex flex-col rounded-2xl p-6 ring-1 ring-brand-100 transition hover:-translate-y-1 hover:ring-brand-300"
                         style="background:linear-gradient(160deg,#eff6ff,#ffffff)">
                        <div class="text-4xl">{{ item.icon }}</div>
                        <h3 class="mt-4 text-lg font-bold text-brand-900">{{ item.title }}</h3>
                        <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ item.text }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ PROBLÈMES RÉSOLUS ═══════════════════════════════ -->
        <section class="px-6 py-20 bg-white">
            <div class="mx-auto max-w-5xl">
                <div class="text-center mb-12">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#fef2f2;color:#dc2626">{{ lang === 'fr' ? 'La transformation' : 'The transformation' }}</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">
                        {{ lang === 'fr' ? 'Avant vs. Avec IBIG FactPro' : 'Before vs. With IBIG FactPro' }}
                    </h2>
                </div>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Sans FactPro -->
                    <div class="rounded-2xl p-8 space-y-4" style="background:#fff5f5;border:1px solid #fecaca">
                        <h3 class="font-extrabold text-lg mb-4" style="color:#dc2626">
                            {{ lang === 'fr' ? '❌ Sans FactPro' : '❌ Without FactPro' }}
                        </h3>
                        <div v-for="item in lang === 'fr' ? [
                            'Factures Excel désorganisées',
                            'Aucun suivi des paiements',
                            'Oublis de relance clients',
                            'Comptabilité manuelle',
                            'Documents non sécurisés',
                        ] : [
                            'Disorganized Excel invoices',
                            'No payment tracking',
                            'Forgotten client follow-ups',
                            'Manual accounting',
                            'Unsecured documents',
                        ]" :key="item" class="flex items-center gap-3 text-sm text-gray-700">
                            <span class="flex-shrink-0 text-base">❌</span>
                            <span>{{ item }}</span>
                        </div>
                    </div>
                    <!-- Avec FactPro -->
                    <div class="rounded-2xl p-8 space-y-4" style="background:#f0fdf4;border:1px solid #bbf7d0">
                        <h3 class="font-extrabold text-lg mb-4" style="color:#16a34a">
                            {{ lang === 'fr' ? '✅ Avec IBIG FactPro' : '✅ With IBIG FactPro' }}
                        </h3>
                        <div v-for="item in lang === 'fr' ? [
                            'Facturation professionnelle en 2 clics',
                            'Tableau de bord en temps réel',
                            'Relances automatiques par WhatsApp/Email',
                            'Rapports financiers automatisés',
                            'Coffre-fort numérique AES-256',
                        ] : [
                            'Professional invoicing in 2 clicks',
                            'Real-time dashboard',
                            'Automatic WhatsApp/Email reminders',
                            'Automated financial reports',
                            'AES-256 digital vault',
                        ]" :key="item" class="flex items-center gap-3 text-sm text-gray-700">
                            <span class="flex-shrink-0 text-base">✅</span>
                            <span>{{ item }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ TARIFS ═══════════════════════════════ -->
        <section id="tarifs" class="px-6 py-24" style="background:#f1f5f9">
            <div class="mx-auto max-w-7xl">
                <div class="text-center">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#eff6ff;color:#0062CC">{{ lang === 'fr' ? 'Tarifs' : 'Pricing' }}</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">{{ t.pricingTitle }}</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-gray-500">{{ t.pricingSub }}</p>

                    <div class="mt-8 inline-flex items-center gap-1 rounded-full bg-white p-1 shadow ring-1 ring-gray-100">
                        <button class="rounded-full px-5 py-2 text-sm font-semibold transition"
                            :class="billing === 'monthly' ? 'text-white' : 'text-gray-500 hover:text-gray-700'"
                            :style="billing === 'monthly' ? 'background:#0062CC' : ''"
                            @click="billing = 'monthly'">{{ t.monthly }}</button>
                        <button class="rounded-full px-5 py-2 text-sm font-semibold transition"
                            :class="billing === 'yearly' ? 'text-white' : 'text-gray-500 hover:text-gray-700'"
                            :style="billing === 'yearly' ? 'background:#0062CC' : ''"
                            @click="billing = 'yearly'">{{ t.yearly }} <span style="color:#b45309">-20%</span></button>
                    </div>
                </div>

                <div v-if="loadingPlans" class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="i in 4" :key="i" class="h-96 animate-pulse rounded-2xl bg-white shadow"></div>
                </div>

                <div v-else class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="plan in plans" :key="plan.code"
                         class="relative flex flex-col rounded-2xl bg-white p-6 shadow transition hover:-translate-y-1 hover:shadow-lg"
                         :class="plan.highlight ? 'ring-2 lg:-translate-y-2' : 'ring-1 ring-gray-100'"
                         :style="plan.highlight ? 'ring-color:#F0C040' : ''">
                        <span v-if="plan.highlight"
                              class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full px-3 py-1 text-xs font-bold"
                              style="background:#F0C040;color:#001d3d">{{ t.popular }}</span>

                        <div class="text-xs font-bold uppercase tracking-widest" style="color:#0062CC">{{ plan.name }}</div>
                        <p class="mt-1 h-10 text-xs text-gray-500">{{ plan.short_description }}</p>

                        <div class="mt-5">
                            <span class="text-3xl font-extrabold text-brand-900">
                                {{ billing === 'monthly' ? fmt(plan.price_monthly) : fmt(plan.price_yearly / 12) }}
                            </span>
                            <span class="text-sm text-gray-400"> {{ t.perMonth }}</span>
                        </div>
                        <div class="mt-1 text-xs text-gray-400">
                            ≈ {{ fmt(plan.eur) }} € · {{ fmt(plan.usd) }} $
                            <template v-if="billing === 'yearly'"> · {{ fmt(plan.price_yearly) }} FCFA / {{ lang === 'fr' ? 'an' : 'yr' }}</template>
                        </div>

                        <ul class="mt-5 flex-1 space-y-2 border-t border-gray-100 pt-5 text-sm text-gray-600">
                            <li class="flex items-center gap-2"><span style="color:#0062CC">✓</span> {{ limitLabel(plan.limits?.documents_per_month) }} {{ lang === 'fr' ? 'docs / mois' : 'docs / month' }}</li>
                            <li class="flex items-center gap-2"><span style="color:#0062CC">✓</span> {{ limitLabel(plan.limits?.users) }} {{ lang === 'fr' ? 'utilisateur(s)' : 'user(s)' }}</li>
                            <li class="flex items-center gap-2"><span style="color:#0062CC">✓</span> {{ limitLabel(plan.limits?.companies) }} {{ lang === 'fr' ? 'société(s)' : 'company(ies)' }}</li>
                            <li v-for="feat in (plan.features || []).slice(0, 3)" :key="feat" class="flex items-center gap-2">
                                <span style="color:#0062CC">✓</span> {{ feat }}
                            </li>
                        </ul>

                        <a v-if="props.canRegister" href="/register"
                           class="mt-6 block rounded-xl px-4 py-3 text-center text-sm font-bold transition hover:scale-105"
                           :style="plan.highlight ? 'background:#0062CC;color:#fff' : 'background:#eff6ff;color:#0062CC'">
                            {{ t.start }}
                        </a>
                    </div>
                </div>

                <div class="mt-10 text-center">
                    <a href="/pricing" class="text-sm font-semibold hover:underline" style="color:#0062CC">{{ t.compareFull }}</a>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ IBIG PARTNERS ═══════════════════════════════ -->
        <section id="partners" class="px-6 py-24 bg-white">
            <div class="mx-auto max-w-7xl">
                <div class="text-center">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#fef9ee;color:#b45309">IBIG Partners</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">{{ t.partnersTitle }}</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-gray-500">{{ t.partnersSub }}</p>
                </div>

                <!-- Commissions 3 niveaux -->
                <div class="mt-14 grid gap-4 sm:grid-cols-3">
                    <div v-for="c in partnerCommissions" :key="c.level"
                         class="relative overflow-hidden rounded-2xl p-6 text-center"
                         style="background:linear-gradient(135deg,#eff6ff,#fff);border:1px solid #dbeafe">
                        <div class="text-4xl font-extrabold" style="color:#0062CC">{{ c.pct }}</div>
                        <div class="mt-1 text-lg font-bold text-brand-900">{{ c.level }}</div>
                        <div class="mt-1 text-sm text-gray-500">{{ lang === 'fr' ? c.label_fr : c.label_en }}</div>
                        <div class="absolute right-3 top-3 rounded-full px-2 py-0.5 text-[10px] font-bold" style="background:#0062CC;color:white">{{ c.level }}</div>
                    </div>
                </div>

                <!-- Statuts partenaires -->
                <div class="mt-10">
                    <h3 class="mb-6 text-center text-xs font-bold uppercase tracking-widest text-gray-400">
                        {{ lang === 'fr' ? 'Votre statut évolue avec vos performances' : 'Your status grows with your performance' }}
                    </h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div v-for="s in partnerStatuses" :key="s.label"
                             class="flex flex-col items-center rounded-2xl border p-5 text-center transition hover:-translate-y-1 hover:shadow-md"
                             :style="`border-color:${s.color}40;background:${s.bg}`">
                            <div class="text-3xl">{{ s.icon }}</div>
                            <div class="mt-2 text-base font-extrabold" :style="`color:${s.color}`">{{ s.label }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ lang === 'fr' ? s.desc_fr : s.desc_en }}</div>
                        </div>
                    </div>
                </div>

                <!-- Stats clés -->
                <div class="mt-10 grid grid-cols-2 gap-4 rounded-2xl p-6 sm:grid-cols-4" style="background:#f8faff">
                    <div class="text-center">
                        <div class="text-2xl font-extrabold" style="color:#0062CC">9</div>
                        <div class="text-xs text-gray-500">{{ lang === 'fr' ? 'Branches du groupe' : 'Group branches' }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-extrabold" style="color:#0062CC">3</div>
                        <div class="text-xs text-gray-500">{{ lang === 'fr' ? 'Niveaux de commission' : 'Commission levels' }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-extrabold" style="color:#0062CC">50%</div>
                        <div class="text-xs text-gray-500">{{ lang === 'fr' ? 'Commission max N1' : 'Max N1 commission' }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-extrabold" style="color:#0062CC">7j</div>
                        <div class="text-xs text-gray-500">{{ lang === 'fr' ? 'Délai de paiement' : 'Payment delay' }}</div>
                    </div>
                </div>

                <!-- CTA devenir partenaire -->
                <div class="mt-12 rounded-2xl p-8 text-center" style="background:linear-gradient(135deg,#001d3d,#0062CC)">
                    <div class="mb-3 inline-block rounded-full px-3 py-1 text-xs font-bold" style="background:rgba(240,192,64,.2);color:#F0C040;border:1px solid rgba(240,192,64,.3)">
                        {{ lang === 'fr' ? '🔥 Programme tout juste lancé' : '🔥 Program just launched' }}
                    </div>
                    <h3 class="text-xl font-extrabold text-white">{{ lang === 'fr' ? "Devenez partenaire IBIG — c'est gratuit" : "Become an IBIG Partner — it's free" }}</h3>
                    <p class="mx-auto mt-2 max-w-lg text-sm text-white/70">
                        {{ lang === 'fr'
                            ? "Vendez FactPro et gagnez jusqu'à 20% de commission. Parrainez des partenaires et touchez sur 3 niveaux. Inscription 100% gratuite, paiement Mobile Money en 7 jours."
                            : "Sell FactPro and earn up to 20% commission. Refer partners and earn on 3 levels. 100% free to join, Mobile Money payment within 7 days." }}
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-4">
                        <a href="https://www.ibigpartners.com/" target="_blank" rel="noopener"
                           class="inline-block rounded-xl px-8 py-3 text-sm font-bold transition hover:scale-105"
                           style="background:#F0C040;color:#001d3d">
                            {{ lang === 'fr' ? 'Rejoindre IBIG Partners →' : 'Join IBIG Partners →' }}
                        </a>
                        <a href="https://www.ibigpartners.com/" target="_blank" rel="noopener"
                           class="inline-block rounded-xl border px-8 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                           style="border-color:rgba(255,255,255,.3)">
                            {{ lang === 'fr' ? 'En savoir plus' : 'Learn more' }}
                        </a>
                    </div>
                    <div class="mt-5 flex flex-wrap justify-center gap-4 text-xs text-white/50">
                        <span>✓ {{ lang === 'fr' ? 'Inscription 100% gratuite' : '100% free to join' }}</span>
                        <span>✓ {{ lang === 'fr' ? 'Mobile Money & banque' : 'Mobile Money & bank' }}</span>
                        <span>✓ {{ lang === 'fr' ? 'Kit marketing offert' : 'Free marketing kit' }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ IBIG SOFT PRODUCTS (carrousel universel) ═══════════════════════════════ -->
        <!-- Le script ibigsoft-universal.js injecte ici le carrousel réel des 16 solutions IBIG SOFT -->
        <div data-ibig="solutions"></div>

        <!-- ═══════════════════════════════ FAQ ═══════════════════════════════ -->
        <section id="faq" class="px-6 py-24" style="background:#f8faff">
            <div class="mx-auto max-w-3xl">
                <div class="text-center">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#eff6ff;color:#0062CC">FAQ</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">{{ t.faqTitle }}</h2>
                </div>
                <div class="mt-12 space-y-3">
                    <div v-for="(faq, i) in t.faqs" :key="i"
                         class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-100 transition hover:ring-brand-200">
                        <button class="flex w-full items-center justify-between px-6 py-5 text-left font-semibold text-brand-900"
                                @click="toggleFaq(i)">
                            {{ faq.q }}
                            <span class="ml-4 flex-shrink-0 text-xl transition duration-200" :class="openFaq === i ? 'rotate-45' : ''" style="color:#0062CC">+</span>
                        </button>
                        <Transition name="faq-slide">
                            <div v-if="openFaq === i" class="px-6 pb-5 text-sm text-gray-600 leading-relaxed">{{ faq.a }}</div>
                        </Transition>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ COMMENT ÇA MARCHE ═══════════════════════════════ -->
        <section class="px-6 py-24 bg-white">
            <div class="mx-auto max-w-5xl">
                <div class="mb-14 text-center">
                    <span class="rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#e8f0fe;color:#0062CC">Démarrage rapide</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-gray-900 sm:text-4xl">{{ lang === 'fr' ? 'Opérationnel en 4 étapes' : 'Up and running in 4 steps' }}</h2>
                </div>
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(step, i) in [
                        { num:'01', icon:'📝', title: lang==='fr' ? 'Créez votre compte' : 'Create your account', desc: lang==='fr' ? 'Inscription gratuite en 60 secondes, sans carte bancaire.' : 'Free sign-up in 60 seconds, no card required.' },
                        { num:'02', icon:'🏢', title: lang==='fr' ? 'Configurez votre société' : 'Set up your company', desc: lang==='fr' ? 'Renseignez vos informations, logo et paramètres fiscaux.' : 'Add your info, logo and tax settings.' },
                        { num:'03', icon:'📄', title: lang==='fr' ? 'Créez vos documents' : 'Create your documents', desc: lang==='fr' ? 'Devis, factures, avoirs en quelques clics avec vos clients et produits.' : 'Quotes, invoices, credits in a few clicks.' },
                        { num:'04', icon:'💰', title: lang==='fr' ? 'Encaissez & suivez' : 'Collect & track', desc: lang==='fr' ? 'Recevez les paiements par Mobile Money ou carte et suivez vos KPIs.' : 'Receive payments via Mobile Money or card.' },
                    ]" :key="i" class="relative rounded-2xl border border-gray-100 p-6 shadow-sm">
                        <div class="mb-3 flex items-center gap-3">
                            <span class="text-2xl">{{ step.icon }}</span>
                            <span class="text-4xl font-black" style="color:#e8f0fe">{{ step.num }}</span>
                        </div>
                        <h3 class="mb-2 font-bold text-gray-900">{{ step.title }}</h3>
                        <p class="text-sm text-gray-500 leading-relaxed">{{ step.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ PUBLICS CONCERNÉS ═══════════════════════════════ -->
        <section class="px-6 py-20" style="background:#f8faff">
            <div class="mx-auto max-w-6xl">
                <h2 class="mb-12 text-center text-3xl font-extrabold text-gray-900">{{ lang === 'fr' ? 'Fait pour vous, quel que soit votre secteur' : 'Built for you, whatever your sector' }}</h2>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    <div v-for="p in [
                        { icon:'🛍️', label: lang==='fr' ? 'Commerce' : 'Retail' },
                        { icon:'🍽️', label: lang==='fr' ? 'Restauration' : 'Food' },
                        { icon:'💻', label: lang==='fr' ? 'IT & Tech' : 'IT & Tech' },
                        { icon:'⚕️', label: lang==='fr' ? 'Santé' : 'Health' },
                        { icon:'🏗️', label: lang==='fr' ? 'BTP' : 'Construction' },
                        { icon:'✂️', label: lang==='fr' ? 'Beauté' : 'Beauty' },
                        { icon:'📚', label: lang==='fr' ? 'Formation' : 'Education' },
                        { icon:'🚚', label: lang==='fr' ? 'Transport' : 'Transport' },
                        { icon:'⚖️', label: lang==='fr' ? 'Conseil / Avocat' : 'Consulting' },
                        { icon:'🏠', label: lang==='fr' ? 'Immobilier' : 'Real Estate' },
                        { icon:'🎨', label: lang==='fr' ? 'Créatif' : 'Creative' },
                        { icon:'🌾', label: lang==='fr' ? 'Agriculture' : 'Agriculture' },
                    ]" :key="p.label" class="flex flex-col items-center gap-2 rounded-xl bg-white p-4 text-center shadow-sm">
                        <span class="text-3xl">{{ p.icon }}</span>
                        <span class="text-xs font-semibold text-gray-700">{{ p.label }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ PWA + MOBILE ═══════════════════════════════ -->
        <section class="px-6 py-24 bg-white">
            <div class="mx-auto max-w-5xl flex flex-col items-center gap-12 lg:flex-row">
                <div class="flex-1">
                    <span class="rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#e8f0fe;color:#0062CC">PWA</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-gray-900">{{ lang === 'fr' ? 'Votre bureau de gestion dans votre poche' : 'Your management desk in your pocket' }}</h2>
                    <p class="mt-4 text-gray-500 leading-relaxed">{{ lang === 'fr' ? 'IBIG FactPro s\'installe comme une app native sur votre smartphone — sans passer par l\'App Store. Fonctionne même sans connexion internet grâce à la synchronisation différée.' : 'IBIG FactPro installs like a native app on your phone — no App Store needed. Works offline with deferred sync.' }}</p>
                    <ul class="mt-6 space-y-2">
                        <li v-for="f in (lang==='fr' ? ['📲 Installation one-tap sur Android & iPhone','🔔 Notifications push en temps réel','📴 Mode hors-ligne avec synchro auto','🚀 Chargement instantané (cache PWA)'] : ['📲 One-tap install on Android & iPhone','🔔 Real-time push notifications','📴 Offline mode with auto-sync','🚀 Instant load (PWA cache)'])" :key="f" class="flex items-center gap-2 text-sm text-gray-700">
                            <span>{{ f }}</span>
                        </li>
                    </ul>
                </div>
                <div class="flex-1 flex justify-center">
                    <div class="relative rounded-3xl shadow-2xl overflow-hidden" style="width:200px;height:380px;background:linear-gradient(135deg,#001d3d,#0062CC)">
                        <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-white p-4">
                            <div class="text-5xl">📱</div>
                            <div class="text-center">
                                <div class="font-bold text-sm">IBIG FactPro</div>
                                <div class="text-xs text-white/60 mt-1">PWA installable</div>
                            </div>
                            <div class="mt-4 rounded-xl px-4 py-2 text-xs font-bold" style="background:#F0C040;color:#001d3d">+ Installer l'app</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ SÉCURITÉ ═══════════════════════════════ -->
        <section class="px-6 py-20" style="background:#001d3d">
            <div class="mx-auto max-w-5xl text-center">
                <span class="rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:rgba(240,192,64,.15);color:#F0C040">Sécurité & Conformité</span>
                <h2 class="mt-4 text-3xl font-extrabold text-white">{{ lang === 'fr' ? 'Vos données protégées, vos documents certifiés' : 'Your data protected, your documents certified' }}</h2>
                <div class="mt-12 grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-6">
                    <div v-for="s in [
                        { icon:'🔐', label:'Chiffrement SSL/TLS' },
                        { icon:'📋', label:'Conforme OHADA' },
                        { icon:'🛡️', label:'2FA disponible' },
                        { icon:'🔍', label:'QR anti-falsification' },
                        { icon:'💾', label:'Sauvegardes quotidiennes' },
                        { icon:'📜', label:'Journal d\'audit complet' },
                    ]" :key="s.label" class="flex flex-col items-center gap-2 rounded-xl p-4" style="background:rgba(255,255,255,.06)">
                        <span class="text-3xl">{{ s.icon }}</span>
                        <span class="text-xs text-white/70 text-center">{{ s.label }}</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ INTÉGRATIONS ═══════════════════════════════ -->
        <section class="px-6 py-20 bg-white">
            <div class="mx-auto max-w-5xl text-center">
                <h2 class="text-3xl font-extrabold text-gray-900 mb-4">{{ lang === 'fr' ? 'Connecté à votre écosystème' : 'Connected to your ecosystem' }}</h2>
                <p class="text-gray-500 mb-12">{{ lang === 'fr' ? 'Zapier, Make, webhooks entrants/sortants, API REST, WhatsApp et Mobile Money.' : 'Zapier, Make, webhooks, REST API, WhatsApp and Mobile Money.' }}</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <div v-for="integ in ['CinetPay','FedaPay','Flutterwave','Orange Money','MTN MoMo','Zapier','Make','WhatsApp','Stripe','API REST']" :key="integ"
                         class="rounded-xl border border-gray-100 px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:shadow-md transition">
                        {{ integ }}
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ TÉMOIGNAGES ═══════════════════════════════ -->
        <section v-if="testimonials.length > 0" class="px-6 py-24" style="background:#f8faff">
            <div class="mx-auto max-w-7xl">
                <div class="text-center mb-12">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#eff6ff;color:#0062CC">{{ lang === 'fr' ? 'Témoignages' : 'Testimonials' }}</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">
                        {{ lang === 'fr' ? 'Ce que disent nos clients' : 'What our clients say' }}
                    </h2>
                </div>
                <div class="grid gap-6 sm:grid-cols-1 lg:grid-cols-3">
                    <div v-for="testi in testimonials" :key="testi.name"
                         class="flex flex-col rounded-2xl bg-white p-7 shadow-sm ring-1 ring-gray-100 transition hover:-translate-y-1 hover:shadow-md">
                        <!-- Stars -->
                        <div class="flex gap-0.5 mb-4">
                            <span v-for="n in testi.rating" :key="n" style="color:#F0C040">⭐</span>
                        </div>
                        <!-- Quote -->
                        <p class="text-sm text-gray-600 leading-relaxed flex-1">"{{ testi.text }}"</p>
                        <!-- Author -->
                        <div class="mt-6 flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center text-sm font-extrabold text-white flex-shrink-0" style="background:linear-gradient(135deg,#001d3d,#0062CC)">{{ testi.avatar }}</div>
                            <div>
                                <div class="font-bold text-sm text-brand-900">{{ testi.name }}</div>
                                <div class="text-xs text-gray-400">{{ testi.role }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ DEMANDE DE DÉMO ═══════════════════════════════ -->
        <section id="demo" class="px-6 py-24" style="background:#f1f5f9">
            <div class="mx-auto max-w-2xl text-center">
                <span class="rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#e8f0fe;color:#0062CC">Démonstration</span>
                <h2 class="mt-4 text-3xl font-extrabold text-gray-900">{{ lang === 'fr' ? 'Voir IBIG FactPro en action' : 'See IBIG FactPro in action' }}</h2>
                <p class="mt-3 text-gray-500">{{ lang === 'fr' ? 'Un expert vous présente le logiciel en direct, adapté à votre secteur d\'activité.' : 'An expert shows you the software live, tailored to your industry.' }}</p>
                <form class="mt-10 rounded-2xl bg-white p-8 shadow-xl text-left space-y-4" @submit.prevent>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ lang === 'fr' ? 'Prénom & Nom' : 'Full name' }}</label>
                            <input type="text" :placeholder="lang==='fr' ? 'Jean Dupont' : 'John Doe'" class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ lang === 'fr' ? 'Email professionnel' : 'Business email' }}</label>
                            <input type="email" :placeholder="lang==='fr' ? 'vous@societe.com' : 'you@company.com'" class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ lang === 'fr' ? 'Téléphone (WhatsApp)' : 'Phone (WhatsApp)' }}</label>
                            <input type="tel" placeholder="+225 07 00 00 00 00" class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">{{ lang === 'fr' ? 'Secteur d\'activité' : 'Industry' }}</label>
                            <select class="w-full rounded-lg border border-gray-200 px-4 py-2.5 text-sm text-gray-600 focus:border-blue-500 focus:outline-none">
                                <option value="">{{ lang === 'fr' ? '-- Choisir --' : '-- Select --' }}</option>
                                <option>Commerce</option>
                                <option>{{ lang === 'fr' ? 'Restauration' : 'Food & Restaurant' }}</option>
                                <option>IT & Tech</option>
                                <option>{{ lang === 'fr' ? 'Santé' : 'Health' }}</option>
                                <option>BTP</option>
                                <option>{{ lang === 'fr' ? 'Autre' : 'Other' }}</option>
                            </select>
                        </div>
                    </div>
                    <a href="https://wa.me/2250555059901?text=Bonjour%2C%20je%20souhaite%20une%20d%C3%A9monstration%20d%27IBIG%20FactPro." target="_blank" rel="noopener"
                       class="block w-full rounded-xl py-3.5 text-center text-sm font-bold shadow-lg transition hover:scale-105 hover:shadow-xl"
                       style="background:linear-gradient(90deg,#001d3d,#0062CC);color:#fff">
                        {{ lang === 'fr' ? '📅 Demander une démo gratuite via WhatsApp' : '📅 Request a free demo via WhatsApp' }}
                    </a>
                    <p class="text-center text-xs text-gray-400">{{ lang === 'fr' ? 'Réponse sous 24h · Démo personnalisée · Gratuit et sans engagement' : 'Reply within 24h · Personalised demo · Free & no commitment' }}</p>
                </form>
            </div>
        </section>

        <!-- ═══════════════════════════════ CTA FINAL ═══════════════════════════════ -->
        <section class="relative overflow-hidden px-6 py-24 text-center" style="background:linear-gradient(135deg,#001d3d,#0062CC)">
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -right-20 top-0 h-64 w-64 rounded-full opacity-20" style="background:radial-gradient(circle,#F0C040,transparent)"></div>
                <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full opacity-10" style="background:radial-gradient(circle,#ffffff,transparent)"></div>
            </div>
            <div class="relative">
                <h2 class="text-3xl font-extrabold text-white sm:text-4xl">{{ t.ctaTitle }}</h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-white/80">{{ t.ctaSub }}</p>
                <div class="mt-10 flex flex-wrap justify-center gap-4">
                    <a v-if="props.canRegister" href="/register"
                       class="rounded-xl px-10 py-4 text-base font-bold shadow-xl transition hover:scale-105 active:scale-95"
                       style="background:#F0C040;color:#001d3d">
                        {{ t.ctaBtn1 }}
                    </a>
                    <a v-if="props.canLogin" href="/login"
                       class="rounded-xl border px-10 py-4 text-base font-semibold text-white transition hover:bg-white/10"
                       style="border-color:rgba(255,255,255,.3)">
                        {{ t.ctaBtn2 }}
                    </a>
                </div>
                <div class="mt-8 flex flex-wrap justify-center gap-6 text-sm text-white/60">
                    <span>✓ {{ lang === 'fr' ? 'Sans carte bancaire' : 'No credit card' }}</span>
                    <span>✓ {{ lang === 'fr' ? 'Résiliable à tout moment' : 'Cancel anytime' }}</span>
                    <span>✓ {{ lang === 'fr' ? '7 jours d\'essai complet' : '7-day full trial' }}</span>
                </div>
            </div>
        </section>

        <PublicFooter />
    </div>

    <!-- SARA chatbot -->
    <Sara />

    <!-- Cookie consent (§8 cahier) -->
    <CookieBanner />

    <!-- WhatsApp flottant (§8 cahier) -->
    <WhatsAppButton />

    <!-- Analytics (GA4 + Meta Pixel) — chargé seulement si consentement cookie -->
    <Analytics />
</template>

<style scoped>
@keyframes float1 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(-20px,30px) scale(1.08)} }
@keyframes float2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(30px,-20px)} }

/* FAQ accordion */
.faq-slide-enter-active,.faq-slide-leave-active{transition:all .25s ease}
.faq-slide-enter-from,.faq-slide-leave-to{opacity:0;transform:translateY(-8px)}

/* Hero background crossfade */
.bg-fade-enter-active,.bg-fade-leave-active{transition:opacity .8s ease}
.bg-fade-enter-from,.bg-fade-leave-to{opacity:0}

/* Hero content slide */
.slide-content-enter-active{animation:slideIn .55s cubic-bezier(.34,1.26,.64,1)}
.slide-content-leave-active{animation:slideOut .35s ease-in forwards}
@keyframes slideIn {
    from { opacity:0; transform:translateX(28px) scale(.97); }
    to   { opacity:1; transform:translateX(0) scale(1); }
}
@keyframes slideOut {
    from { opacity:1; transform:translateX(0); }
    to   { opacity:0; transform:translateX(-20px); }
}
</style>
