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
import IbigSoftSolutions from '@/Components/IbigSoftSolutions.vue';

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
        bg: 'linear-gradient(135deg,#001120 0%,#002D5B 60%,#0062CC 100%)',
        accent: '#1e3a5f',
        accentLight: '#3b82f6',
        fr: { tag: '✦ Facturation conforme OHADA', h1: 'Votre première facture\nen 90 secondes', sub: 'QR anti-falsification, Mobile Money intégré, email & WhatsApp en un clic. La facturation professionnelle enfin accessible à tous.', cta1: 'Créer ma première facture →', cta2: 'Voir la démo' },
        en: { tag: '✦ OHADA-compliant invoicing', h1: 'Your first invoice\nin 90 seconds', sub: 'Anti-fraud QR code, integrated Mobile Money, email & WhatsApp in one click. Professional invoicing finally accessible to everyone.', cta1: 'Create my first invoice →', cta2: 'Watch demo' },
        doc: {
            type: 'FACTURE', num: 'FAC-2026-0842',
            emetteur: 'IBIG TECH SOLUTIONS SARL',
            emetteurSub: 'RC CI-ABJ-2022-B-15234 · NIF 2248761A · Capital 5 000 000 FCFA',
            emetteurAddr: '12 Rue du Commerce, Zone Industrielle · Abidjan, Côte d\'Ivoire',
            emetteurContact: 'Tél : +225 07 00 00 00 00 · contact@ibigtechci.com',
            client: 'NEXATEC INDUSTRIES CI',
            clientSub: 'M. KOUASSI Rodrigue · Direction SI',
            clientAddr: 'Plateau, Abidjan · NIF 1122334455A',
            date: '22 juillet 2026', echeance: '21 août 2026',
            statusLabel: '✓ PAYÉE', statusBg: '#d1fae5', statusFg: '#065f46',
            rows: [
                { desc: 'Intégration ERP & migration données (lot 1)', qty: '1', unite: 'Forfait', pu: '480 000', tva: '18%', total: '480 000' },
                { desc: 'Développement API REST sur mesure', qty: '3', unite: 'Module', pu: '95 000', tva: '18%', total: '285 000' },
                { desc: 'Formation équipe IT (6 techniciens × 2j)', qty: '12', unite: 'Jour', pu: '22 000', tva: '18%', total: '264 000' },
                { desc: 'Maintenance & support prioritaire 12 mois', qty: '1', unite: 'An', pu: '216 000', tva: '18%', total: '216 000' },
            ],
            ht: '1 245 000', tva: '224 100', ttc: '1 469 100', devise: 'FCFA',
            equiv: '≈ 2 239 € · 2 440 $',
            payModes: ['Wave CI — +225 07 01 00 00 00', 'Orange Money CI — +225 07 00 00 00 00'],
            qrLabel: 'Vérifiez sur factpro.ibigsoft.com',
            footer: 'IBIG TECH SOLUTIONS SARL · RC CI-ABJ-2022-B-15234 · Généré par IBIG FactPro — factpro.ibigsoft.com',
        },
    },
    {
        bg: 'linear-gradient(135deg,#071a0d 0%,#0d3320 60%,#166534 100%)',
        accent: '#166534',
        accentLight: '#22c55e',
        fr: { tag: '✦ Devis signable en ligne', h1: 'Devis accepté,\nsigné, facturé — sans papier', sub: 'Lien de signature unique par SMS ou email. Votre client accepte en un tap depuis son téléphone. Conversion en facture automatique.', cta1: 'Créer mon premier devis →', cta2: 'Voir la démo' },
        en: { tag: '✦ Online signable quotes', h1: 'Quoted, signed,\nbilled — paperless', sub: 'Unique signature link via SMS or email. Your client accepts with one tap from their phone. Automatic conversion to invoice.', cta1: 'Create my first quote →', cta2: 'Watch demo' },
        doc: {
            type: 'DEVIS', num: 'DEV-2026-0317',
            emetteur: 'BATIMEX CONSTRUCTION SARL',
            emetteurSub: 'RCCM CI-ABJ-2021-B-8811 · NIF 0089241B · NINEA 00789241',
            emetteurAddr: 'Zone Industrielle de Yopougon · Abidjan, CI',
            emetteurContact: 'Tél : +225 07 00 00 00 00 · devis@batimex.ci',
            client: 'AKIBA PROMOTIONS IMMOBILIÈRES SA',
            clientSub: 'Mme DIALLO Aminata · Direction Travaux',
            clientAddr: 'Cocody Riviera, Abidjan · NIF 9988776655B',
            date: '18 juillet 2026', echeance: 'Valable 30 jours — jusqu\'au 17/08/2026',
            statusLabel: '✍ ACCEPTÉ', statusBg: '#fef3c7', statusFg: '#92400e',
            rows: [
                { desc: 'Étude géotechnique & plan de masse', qty: '1', unite: 'Forfait', pu: '750 000', tva: '18%', total: '750 000' },
                { desc: 'Fourniture & pose béton armé', qty: '120', unite: 'm³', pu: '18 500', tva: '18%', total: '2 220 000' },
                { desc: 'Main-d\'œuvre qualifiée — maçonnerie', qty: '45', unite: 'Jour', pu: '25 000', tva: '18%', total: '1 125 000' },
                { desc: 'Location engins & équipements lourds', qty: '10', unite: 'Jour', pu: '95 000', tva: '18%', total: '950 000' },
            ],
            ht: '5 045 000', tva: '908 100', ttc: '5 953 100', devise: 'FCFA',
            equiv: '≈ 9 074 € · 9 886 $',
            payModes: ['Virement UBA CI — IBAN : CI42 0000 0000 0000 0000 00', 'Chèque à l\'ordre de BATIMEX CONSTRUCTION SARL'],
            qrLabel: '✍ Signé électroniquement le 19/07/2026',
            footer: 'BATIMEX CONSTRUCTION SARL · RCCM CI-ABJ-2021-B-8811 · Généré par IBIG FactPro — factpro.ibigsoft.com',
        },
    },
    {
        bg: 'linear-gradient(135deg,#1a0800 0%,#3d1200 60%,#c2410c 100%)',
        accent: '#c2410c',
        accentLight: '#f97316',
        fr: { tag: '✦ Caisse POS & bons de livraison', h1: 'Votre caisse tactile,\npartout en Afrique', sub: 'Ventes, tickets thermiques 58/80mm, gestion de stock en temps réel, rapport de caisse journalier. Fonctionne sans connexion internet.', cta1: 'Ouvrir ma caisse →', cta2: 'Voir la démo' },
        en: { tag: '✦ POS & delivery notes', h1: 'Your touchscreen POS,\nanywhere in Africa', sub: 'Sales, 58/80mm thermal receipts, real-time stock management, daily cash report. Works without internet connection.', cta1: 'Open my cash register →', cta2: 'Watch demo' },
        doc: {
            type: 'BON DE LIVRAISON', num: 'BL-2026-1124',
            emetteur: 'IBIG DISTRIBUTION SARL',
            emetteurSub: 'RC CI-ABJ-2020-B-3344 · NIF P012345678 · Capital 2 000 000 FCFA',
            emetteurAddr: 'Zone Industrielle d\'Adjamé, Abidjan, CI',
            emetteurContact: 'Tél : +225 07 00 00 00 00 · livraison@ibigdist.ci',
            client: 'MARCHÉ GRAND SURFACE KORHOGO',
            clientSub: 'M. COULIBALY Issouf · Service Appro.',
            clientAddr: 'Korhogo, Côte d\'Ivoire · NIF 5544332211C',
            date: '22 juillet 2026', echeance: 'Livré le 22/07/2026',
            statusLabel: '✓ LIVRÉ', statusBg: '#d1fae5', statusFg: '#065f46',
            rows: [
                { desc: 'Huile végétale raffinée 5L', qty: '120', unite: 'Carton', pu: '3 500', tva: 'Ex.', total: '420 000' },
                { desc: 'Riz étuvé longue grain 25kg', qty: '40', unite: 'Sac', pu: '8 200', tva: 'Ex.', total: '328 000' },
                { desc: 'Farine de blé premium 50kg', qty: '30', unite: 'Sac', pu: '7 500', tva: 'Ex.', total: '225 000' },
                { desc: 'Sucre en poudre 50kg', qty: '25', unite: 'Sac', pu: '9 000', tva: 'Ex.', total: '225 000' },
            ],
            ht: '1 198 000', tva: 'Exonéré', ttc: '1 198 000', devise: 'FCFA',
            equiv: '≈ 1 826 € · 1 990 $',
            payModes: ['Orange Money CI — +225 07 00 00 00 00', 'Espèces à la livraison'],
            qrLabel: '📍 Livraison confirmée GPS · 22/07/2026',
            footer: 'IBIG DISTRIBUTION SARL · RC CI-ABJ-2020-B-3344 · Généré par IBIG FactPro — factpro.ibigsoft.com',
        },
    },
    {
        bg: 'linear-gradient(135deg,#0d0020 0%,#2d006a 60%,#7c3aed 100%)',
        accent: '#7c3aed',
        accentLight: '#a78bfa',
        fr: { tag: '✦ Factures récurrentes & abonnements', h1: 'Automatisez vos\nfactures mensuelles', sub: 'CA mensuel, taux de recouvrement, top clients, alertes stock — votre activité visualisée en temps réel. Rapports exportables en PDF et Excel.', cta1: 'Accéder au dashboard →', cta2: 'Voir la démo' },
        en: { tag: '✦ Recurring invoices & subscriptions', h1: 'Automate your\nmonthly invoices', sub: 'Monthly revenue, collection rate, top clients, stock alerts — your business visualized in real time. Reports exportable to PDF and Excel.', cta1: 'Go to dashboard →', cta2: 'Watch demo' },
        doc: {
            type: 'FACTURE', num: 'FAC-2026-0961',
            emetteur: 'IBIG SOFT SARL',
            emetteurSub: 'RC CI-ABJ-2018-B-00421 · NIF 1874532C · Capital 10 000 000 FCFA',
            emetteurAddr: 'Marcory Zone 4, Abidjan, Côte d\'Ivoire',
            emetteurContact: 'Tél : +225 07 00 00 00 00 · facturation@ibigsoft.com',
            client: 'AURIFEX MINING & RESOURCES SARL',
            clientSub: 'Mme TRAORÉ Fatoumata · Dir. Financière',
            clientAddr: 'Zone Industrielle, Abidjan · NIF 7766554433D',
            date: '01 juillet 2026', echeance: '31 juillet 2026',
            statusLabel: '◉ EN ATTENTE', statusBg: '#ede9fe', statusFg: '#5b21b6',
            rows: [
                { desc: 'Abonnement FactPro Business — juil. 2026', qty: '1', unite: 'Mois', pu: '49 000', tva: '18%', total: '49 000' },
                { desc: 'Module RH & Paie — 12 bulletins de salaire', qty: '12', unite: 'Bulletin', pu: '2 500', tva: '18%', total: '30 000' },
                { desc: 'Hébergement VPS haute disponibilité', qty: '1', unite: 'Mois', pu: '35 000', tva: '18%', total: '35 000' },
                { desc: 'Support prioritaire 24h/24 — juillet', qty: '1', unite: 'Mois', pu: '15 000', tva: '18%', total: '15 000' },
            ],
            ht: '129 000', tva: '23 220', ttc: '152 220', devise: 'FCFA',
            equiv: '≈ 232 € · 253 $',
            payModes: ['Orange Money CI — +225 07 00 00 00 00', 'Wave CI — +225 07 01 00 00 00'],
            qrLabel: '🔄 Facture récurrente mensuelle · IBIG FactPro',
            footer: 'IBIG SOFT SARL · RC CI-ABJ-2018-B-00421 · Généré par IBIG FactPro — factpro.ibigsoft.com',
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
const targets  = { clients: 200, docs: 9500, pays: 15, uptime: 99 };
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
        { icon: '📂', title: '498+ modèles de documents', text: 'Catalogue sectoriel couvrant 24 secteurs d\'activité : BTP, commerce, santé, restauration, services, ONG et plus encore.' },
        { icon: '🎨', title: 'Templates PDF intelligents', text: 'Sélection automatique du style visuel selon votre secteur. Vos documents reflètent votre métier dès la première impression.' },
        { icon: '📊', title: 'Dashboard BI avancé', text: 'KPIs en temps réel, graphique CA sur 12 mois, accès rapides à tous les modules, alertes intelligentes.' },
        { icon: '⚙️', title: 'Paramètres complets', text: 'Identité société, facturation, apparence, signature électronique, modes de paiement — tout configurable en quelques clics.' },
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
        { q: 'Quels moyens de paiement acceptez-vous ?', a: 'Mobile Money (Orange Money, Wave, MTN MoMo, Moov Money), espèces, chèque, virement bancaire national et international, et paiement en ligne via CinetPay / FedaPay / Flutterwave.' },
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
        { icon: '📂', title: '498+ document templates', text: 'Sector catalog covering 24 industries: construction, retail, healthcare, restaurants, services, NGOs and more.' },
        { icon: '🎨', title: 'Smart PDF templates', text: 'Automatic visual style selection based on your industry. Your documents reflect your trade from the very first print.' },
        { icon: '📊', title: 'Advanced BI dashboard', text: 'Real-time KPIs, 12-month revenue chart, quick access to all modules, smart alerts.' },
        { icon: '⚙️', title: 'Full settings', text: 'Company identity, billing, appearance, e-signature, payment methods — all configurable in a few clicks.' },
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
        { q: 'What payment methods do you accept?', a: 'Mobile Money (Orange Money, Wave, MTN MoMo, Moov Money), cash, cheque, bank transfer, and online payment via CinetPay / FedaPay / Flutterwave.' },
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

/* ── Zone 7.13 — Publics concernés ── */
const audiences = [
    {
        colorBg: '#eff6ff', colorIcon: '#0062CC',
        title_fr: 'PME & Startups', title_en: 'SMEs & Startups',
        desc_fr: 'Gérez vos finances et clients dès le premier jour',
        desc_en: 'Manage your finances and clients from day one',
        path: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    },
    {
        colorBg: '#fef3c7', colorIcon: '#d97706',
        title_fr: 'Grossistes & Distributeurs', title_en: 'Wholesalers & Distributors',
        desc_fr: 'Stock multi-entrepôt, commandes, livraisons',
        desc_en: 'Multi-warehouse stock, orders, deliveries',
        path: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
    },
    {
        colorBg: '#f0fdf4', colorIcon: '#16a34a',
        title_fr: 'Prestataires de services', title_en: 'Service Providers',
        desc_fr: 'Devis, facturation, suivi projets et RH',
        desc_en: 'Quotes, invoicing, project & HR tracking',
        path: 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
    },
    {
        colorBg: '#faf5ff', colorIcon: '#7c3aed',
        title_fr: 'Cabinets & Consultants', title_en: 'Firms & Consultants',
        desc_fr: 'Honoraires, contrats, relances automatiques',
        desc_en: 'Fees, contracts, automatic reminders',
        path: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
        colorBg: '#fff7ed', colorIcon: '#ea580c',
        title_fr: 'Associations & ONG', title_en: 'Associations & NGOs',
        desc_fr: 'Budget, dons, rapports OHADA conformes',
        desc_en: 'Budget, donations, OHADA-compliant reports',
        path: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
    },
    {
        colorBg: '#f0f9ff', colorIcon: '#0284c7',
        title_fr: 'Multi-sites & Groupes', title_en: 'Multi-site & Groups',
        desc_fr: 'Consolidation, tableaux de bord centralisés',
        desc_en: 'Consolidation, centralized dashboards',
        path: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    },
];

/* ── Zone 7.14 — Comment ça marche ── */
const howSteps = [
    {
        num: '1',
        path: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        title_fr: 'Créez votre compte',        title_en: 'Create your account',
        desc_fr: 'Inscription gratuite en 1 minute, sans carte bancaire',
        desc_en: 'Free sign-up in 1 minute, no credit card required',
    },
    {
        num: '2',
        path: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        title_fr: 'Configurez votre société',  title_en: 'Set up your company',
        desc_fr: 'Logo, coordonnées, devise, modules activés',
        desc_en: 'Logo, details, currency, enabled modules',
    },
    {
        num: '3',
        path: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        title_fr: 'Invitez votre équipe',      title_en: 'Invite your team',
        desc_fr: 'Rôles et droits personnalisables par collaborateur',
        desc_en: 'Customizable roles and permissions per user',
    },
    {
        num: '4',
        path: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        title_fr: 'Gérez tout depuis un seul écran', title_en: 'Manage everything from one screen',
        desc_fr: 'Facturation, stocks, rapports en temps réel',
        desc_en: 'Invoicing, stock, real-time reports',
    },
];
</script>

<template>
    <Head title="IBIG FactPro — Logiciel de facturation professionnel pour l'Afrique">
        <meta name="description" content="IBIG FactPro est la solution de facturation SaaS conçue pour les PME africaines. Factures, devis, paiements Mobile Money, Multi-devises, conforme OHADA. Essai gratuit 7 jours." />
        <meta name="keywords" content="facturation Afrique, logiciel facturation Côte d'Ivoire, OHADA, Mobile Money, PME Afrique, devis, factures, SaaS Afrique" />
        <meta property="og:title" content="IBIG FactPro — Facturation professionnelle pour l'Afrique" />
        <meta property="og:description" content="Gérez vos factures, devis et paiements Mobile Money depuis n'importe quel appareil. Conforme OHADA. Essai gratuit 7 jours." />
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://factpro.ibigsoft.com" />
        <meta property="og:image" content="https://factpro.ibigsoft.com/og-image.jpg" />
        <meta property="og:site_name" content="IBIG FactPro">
        <meta property="og:locale" content="fr_FR">
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="IBIG FactPro — Facturation professionnelle pour l'Afrique" />
        <meta name="twitter:description" content="Gérez vos factures, devis et paiements Mobile Money depuis n'importe quel appareil. Conforme OHADA. Essai gratuit 7 jours." />
        <link rel="canonical" href="https://factpro.ibigsoft.com" />
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
            "offers": { "@type": "Offer", "price": "0", "priceCurrency": "XOF", "description": "Essai gratuit 7 jours" },
            "aggregateRating": { "@type": "AggregateRating", "ratingValue": "4.9", "reviewCount": "487" }
        }
        </script>
    </Head>

    <div class="min-h-screen bg-white text-gray-800">
        <!-- ═══════════════════════════════ ZONE 7.1 — ANNOUNCEMENT BAR ═══════════════════════════════ -->
        <div style="background:#002D5B;height:38px" class="sticky top-0 z-50 flex items-center justify-center px-4 text-white text-xs">
            <!-- Desktop -->
            <span class="hidden sm:flex items-center gap-0">
                {{ lang === 'fr' ? 'Essayez IBIG FactPro gratuitement' : 'Try IBIG FactPro for free' }}
                <span class="mx-3 opacity-30">|</span>
                {{ lang === 'fr' ? 'Assistance :' : 'Support:' }}
                <a href="/contact" class="mx-1 font-semibold underline hover:text-blue-200 transition">+225 27 22 27 60 14</a>
                <span class="mx-3 opacity-30">|</span>
                <button @click="toggleLang" class="font-bold hover:text-blue-200 transition">{{ lang === 'fr' ? 'FR / EN' : 'EN / FR' }}</button>
            </span>
            <!-- Mobile condensé -->
            <span class="flex sm:hidden items-center gap-2">
                <a href="/register" class="font-semibold">{{ lang === 'fr' ? 'Essai gratuit' : 'Free trial' }}</a>
                <span class="opacity-30">·</span>
                <a href="/contact" class="font-semibold">Support</a>
                <span class="opacity-30">·</span>
                <button @click="toggleLang" class="font-bold">{{ lang === 'fr' ? 'FR' : 'EN' }}</button>
            </span>
        </div>

        <!-- NAV avec toggle langue -->
        <nav class="sticky top-[38px] z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
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

                            <!-- Document PDF simulé — fidèle au vrai rendu IBIG FactPro -->
                            <div class="relative w-full max-w-sm rounded-xl overflow-hidden shadow-2xl bg-white"
                                 style="font-size:9.5px;line-height:1.4;transform:rotateY(-2deg) rotateX(1deg);box-shadow:0 32px 80px rgba(0,0,0,.45),0 0 0 1px rgba(255,255,255,.08)">

                                <!-- ═ EN-TÊTE : Logo gauche + Titre droit (layout réel PDF) ═ -->
                                <div class="flex items-start justify-between px-4 pt-4 pb-3 bg-white">
                                    <!-- Colonne gauche : logo + société émettrice -->
                                    <div style="width:55%">
                                        <div class="flex items-center gap-2 mb-1.5">
                                            <div class="h-8 w-8 rounded flex items-center justify-center font-black text-xs text-white flex-shrink-0"
                                                 :style="`background:${slide.accent}`">FP</div>
                                            <div>
                                                <div class="font-extrabold text-xs" :style="`color:${slide.accent}`">{{ slide.doc.emetteur }}</div>
                                                <div style="color:#6b7280;font-size:7.5px">{{ slide.doc.emetteurSub }}</div>
                                            </div>
                                        </div>
                                        <div style="color:#374151;font-size:7.5px;line-height:1.5">{{ slide.doc.emetteurAddr }}</div>
                                        <div style="color:#374151;font-size:7.5px">{{ slide.doc.emetteurContact }}</div>
                                    </div>
                                    <!-- Colonne droite : boîte type + numéro (layout réel) -->
                                    <div class="text-right" style="width:43%">
                                        <div class="inline-block rounded px-3 py-2 text-white mb-1.5" :style="`background:${slide.accent}`">
                                            <div class="font-black uppercase tracking-wider" style="font-size:12px">{{ slide.doc.type }}</div>
                                            <div style="font-size:8px;opacity:.85">{{ slide.doc.num }}</div>
                                        </div>
                                        <div class="flex flex-col items-end gap-0.5">
                                            <div style="color:#374151;font-size:7.5px"><span style="color:#9ca3af">Émission :</span> {{ slide.doc.date }}</div>
                                            <div style="color:#374151;font-size:7.5px"><span style="color:#9ca3af">Échéance :</span> {{ slide.doc.echeance }}</div>
                                            <div class="mt-1 rounded px-2 py-0.5 font-bold" style="font-size:8px" :style="`background:${slide.doc.statusBg};color:${slide.doc.statusFg}`">{{ slide.doc.statusLabel }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ligne séparatrice primaryColor (layout réel) -->
                                <div class="mx-4" style="height:2px;border-radius:1px" :style="`background:${slide.accent}`"></div>

                                <!-- ═ BLOC CLIENT (layout réel : border-left 3px primaryColor) ═ -->
                                <div class="mx-4 my-2 px-3 py-2 rounded" :style="`background:#f9fafb;border:1px solid #e5e7eb;border-left:3px solid ${slide.accent}`">
                                    <div class="font-black uppercase mb-0.5" style="font-size:7px;color:#9ca3af;letter-spacing:.08em">Facturé à</div>
                                    <div class="font-extrabold" style="font-size:10px;color:#111827">{{ slide.doc.client }}</div>
                                    <div style="color:#374151;font-size:7.5px">{{ slide.doc.clientSub }}</div>
                                    <div style="color:#9ca3af;font-size:7px">{{ slide.doc.clientAddr }}</div>
                                </div>

                                <!-- ═ TABLEAU LIGNES (layout réel : en-tête coloré) ═ -->
                                <div class="mx-4 mb-2 rounded overflow-hidden" style="border:1px solid #e5e7eb">
                                    <!-- En-tête tableau -->
                                    <div class="grid text-white font-black uppercase px-2 py-1.5"
                                         :style="`background:${slide.accent};grid-template-columns:1fr 2.2rem 2.8rem 2.2rem 3.2rem;font-size:7px;letter-spacing:.06em`">
                                        <span>Description</span>
                                        <span class="text-center">Qté</span>
                                        <span class="text-center">Unité</span>
                                        <span class="text-right">TVA</span>
                                        <span class="text-right">Total HT</span>
                                    </div>
                                    <!-- Lignes alternées -->
                                    <div v-for="(row, i) in slide.doc.rows" :key="i"
                                         class="grid px-2 py-1.5"
                                         :style="`grid-template-columns:1fr 2.2rem 2.8rem 2.2rem 3.2rem;background:${i%2===0?'#ffffff':'#f9fafb'};border-top:1px solid #e5e7eb`">
                                        <span class="font-semibold pr-1" style="color:#111827;font-size:8px;line-height:1.3">{{ row.desc }}</span>
                                        <span class="text-center" style="color:#6b7280;font-size:8px">{{ row.qty }}</span>
                                        <span class="text-center" style="color:#6b7280;font-size:8px">{{ row.unite }}</span>
                                        <span class="text-right" style="color:#6b7280;font-size:8px">{{ row.tva }}</span>
                                        <span class="text-right font-bold" style="color:#111827;font-size:8px;font-family:monospace">{{ row.total }}</span>
                                    </div>
                                </div>

                                <!-- ═ TOTAUX + QR (layout réel : 52% QR / 48% tableau totaux) ═ -->
                                <div class="flex gap-3 mx-4 mb-2">
                                    <!-- QR code (gauche) -->
                                    <div class="flex-shrink-0 flex flex-col items-center" style="width:52px">
                                        <div class="rounded-lg flex items-center justify-center" style="border:1px solid #e5e7eb;background:#fafafa;width:44px;height:44px">
                                            <svg viewBox="0 0 32 32" fill="none" style="width:36px;height:36px">
                                                <rect x="1" y="1" width="4" height="4" :fill="slide.accent" rx=".5"/>
                                                <rect x="6" y="1" width="1" height="4" fill="#111"/>
                                                <rect x="8" y="1" width="1" height="2" fill="#111"/><rect x="10" y="2" width="2" height="1" fill="#111"/>
                                                <rect x="13" y="1" width="4" height="4" :fill="slide.accent" rx=".5"/>
                                                <rect x="1" y="6" width="4" height="1" fill="#111"/><rect x="13" y="6" width="4" height="1" fill="#111"/>
                                                <rect x="1" y="8" width="4" height="4" :fill="slide.accent" rx=".5"/>
                                                <rect x="6" y="9" width="2" height="2" fill="#111"/>
                                                <rect x="9" y="8" width="3" height="2" fill="#111"/><rect x="9" y="11" width="2" height="3" fill="#111"/>
                                                <rect x="13" y="8" width="4" height="4" :fill="slide.accent" rx=".5"/>
                                                <rect x="1" y="14" width="6" height="1" fill="#111"/><rect x="8" y="14" width="4" height="1" fill="#111"/>
                                                <rect x="1" y="16" width="3" height="3" fill="#111"/><rect x="5" y="17" width="2" height="2" fill="#111"/>
                                                <rect x="9" y="16" width="2" height="4" fill="#111"/><rect x="12" y="15" width="2" height="3" fill="#111"/>
                                                <rect x="15" y="16" width="2" height="2" fill="#111"/><rect x="15" y="19" width="2" height="2" fill="#111"/>
                                            </svg>
                                        </div>
                                        <div class="text-center mt-1" style="color:#9ca3af;font-size:6px;line-height:1.3">Anti-falsification<br>Certifié IBIG FactPro</div>
                                    </div>
                                    <!-- Tableau totaux (droite) -->
                                    <div class="flex-1 rounded" style="border:1px solid #e5e7eb;overflow:hidden">
                                        <div class="flex justify-between px-2 py-1" style="border-bottom:1px solid #e5e7eb">
                                            <span style="color:#374151;font-size:8px">Sous-total HT</span>
                                            <span class="font-semibold" style="color:#374151;font-size:8px;font-family:monospace">{{ slide.doc.ht }} {{ slide.doc.devise }}</span>
                                        </div>
                                        <div class="flex justify-between px-2 py-1" style="border-bottom:1px solid #e5e7eb">
                                            <span style="color:#374151;font-size:8px">TVA</span>
                                            <span class="font-semibold" style="color:#374151;font-size:8px;font-family:monospace">{{ slide.doc.tva }} {{ slide.doc.devise }}</span>
                                        </div>
                                        <!-- Ligne TOTAL TTC (fond primaryColor, blanc) -->
                                        <div class="flex items-center justify-between px-2 py-1.5 text-white" :style="`background:${slide.accent}`">
                                            <span class="font-black uppercase" style="font-size:7.5px;letter-spacing:.06em">TOTAL TTC</span>
                                            <div class="text-right">
                                                <div class="font-black" style="font-size:10px;font-family:monospace">{{ slide.doc.ttc }} {{ slide.doc.devise }}</div>
                                                <div style="font-size:6.5px;opacity:.75">{{ slide.doc.equiv }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ═ MODES DE PAIEMENT ═ -->
                                <div class="mx-4 mb-2 px-3 py-1.5 rounded" :style="`background:${slide.accent}0d;border:1px solid ${slide.accent}30`">
                                    <div class="font-black uppercase mb-0.5" :style="`color:${slide.accent};font-size:7px;letter-spacing:.06em`">Moyens de paiement acceptés</div>
                                    <div v-for="(pm, i) in slide.doc.payModes" :key="i" style="color:#374151;font-size:7.5px">· {{ pm }}</div>
                                </div>

                                <!-- ═ FOOTER LÉGAL (layout réel) ═ -->
                                <div class="mx-4 mb-3" :style="`border-top:2px solid ${slide.accent};padding-top:4px`">
                                    <div style="color:#9ca3af;font-size:6.5px;line-height:1.5;text-align:center">
                                        {{ slide.doc.qrLabel }}<br>
                                        <span :style="`color:${slide.accent}`">■</span> {{ slide.doc.footer }}
                                    </div>
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
                    { icon: '⭐', label_fr: 'Note 4.8/5*',                label_en: 'Rated 4.8/5*' },
                    { icon: '🌍', label_fr: '9 Pays Afrique',             label_en: '9 African Countries' },
                    { icon: '📋', label_fr: 'Conforme OHADA',             label_en: 'OHADA Compliant' },
                    { icon: '🔄', label_fr: 'Synchronisation temps réel', label_en: 'Real-time Sync' },
                ]" :key="badge.label_fr"
                    class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-600 shadow-sm">
                    <span>{{ badge.icon }}</span>
                    <span>{{ lang === 'fr' ? badge.label_fr : badge.label_en }}</span>
                </span>
            </div>
            <p class="text-center text-xs text-gray-400 mt-3">* {{ lang === 'fr' ? 'Chiffre indicatif basé sur les retours de nos bêta-testeurs.' : 'Indicative figure based on our beta-testers\' feedback.' }}</p>
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

        <!-- ═══════════════════════════════ IBIG SOFT PRODUCTS ═══════════════════════════════ -->
        <IbigSoftSolutions />

        <!-- ═══════════════════════════════ ZONE 7.13 — PUBLICS CONCERNÉS ═══════════════════════════════ -->
        <section class="bg-white px-6 py-24">
            <div class="mx-auto max-w-7xl">
                <div class="mb-14 text-center">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#eff6ff;color:#0062CC">{{ lang === 'fr' ? 'Pour qui ?' : 'Who is it for?' }}</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">
                        {{ lang === 'fr' ? "IBIG FactPro s'adapte à toutes les organisations" : 'IBIG FactPro adapts to all organizations' }}
                    </h2>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="audience in audiences"
                        :key="audience.title_fr"
                        class="group flex flex-col rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg"
                        style="border-radius:12px"
                    >
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full" :style="`background:${audience.colorBg}`">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" :style="`color:${audience.colorIcon}`">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="audience.path" />
                            </svg>
                        </div>
                        <h3 class="mb-2 font-bold text-brand-900">{{ lang === 'fr' ? audience.title_fr : audience.title_en }}</h3>
                        <p class="text-sm leading-relaxed text-gray-500">{{ lang === 'fr' ? audience.desc_fr : audience.desc_en }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ ZONE 7.14 — COMMENT ÇA MARCHE ═══════════════════════════════ -->
        <section class="px-6 py-24" style="background:#f8faff">
            <div class="mx-auto max-w-7xl">
                <div class="mb-14 text-center">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#fef9ee;color:#b45309">{{ lang === 'fr' ? 'Démarrage' : 'Getting started' }}</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">
                        {{ lang === 'fr' ? 'Comment démarrer en 4 étapes' : 'How to get started in 4 steps' }}
                    </h2>
                </div>

                <!-- Étapes -->
                <div class="relative grid grid-cols-1 gap-8 lg:grid-cols-4">
                    <!-- Ligne de connexion desktop -->
                    <div class="absolute top-10 left-0 right-0 hidden h-px lg:block" style="background:linear-gradient(90deg,transparent,#D4A01733,#D4A01766,#D4A01733,transparent)"></div>

                    <div
                        v-for="(step, i) in howSteps"
                        :key="i"
                        class="relative flex flex-col items-center text-center lg:items-start lg:text-left"
                    >
                        <!-- Numéro cerclé or -->
                        <div class="relative z-10 mb-5 flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-full shadow-lg"
                             style="background:linear-gradient(135deg,#D4A017,#f0c040);box-shadow:0 8px 24px rgba(212,160,23,.35)">
                            <span class="text-2xl font-black" style="color:#001d3d">{{ step.num }}</span>
                        </div>
                        <!-- Icône -->
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl" style="background:#eff6ff">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:#0062CC">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="step.path" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-bold text-brand-900">{{ lang === 'fr' ? step.title_fr : step.title_en }}</h3>
                        <p class="text-sm leading-relaxed text-gray-500">{{ lang === 'fr' ? step.desc_fr : step.desc_en }}</p>
                    </div>
                </div>

                <!-- CTA -->
                <div class="mt-14 flex flex-wrap justify-center gap-4">
                    <a v-if="props.canRegister" href="/register"
                       class="inline-flex items-center gap-2 rounded-xl px-8 py-3.5 text-sm font-extrabold shadow-lg transition hover:scale-105 active:scale-95"
                       style="background:linear-gradient(135deg,#D4A017,#f0c040);color:#001d3d;box-shadow:0 8px 24px rgba(212,160,23,.3)">
                        {{ lang === 'fr' ? 'Commencer gratuitement' : 'Get started free' }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="/demo-login"
                       class="inline-flex items-center gap-2 rounded-xl border px-8 py-3.5 text-sm font-semibold transition hover:bg-brand-50"
                       style="border-color:#0062CC;color:#0062CC">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full text-xs" style="background:#eff6ff">▶</span>
                        {{ lang === 'fr' ? 'Voir une démo' : 'Watch a demo' }}
                    </a>
                </div>
            </div>
        </section>

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

        <!-- ═══════════════════════════════ §7.10 MODULES ═══════════════════════════════ -->
        <section class="px-6 py-24" style="background:#f8faff">
            <div class="mx-auto max-w-6xl">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-extrabold mb-3" style="color:#001d3d">
                        {{ lang === 'fr' ? 'Tous vos modules de gestion, dans une seule application' : 'All your management modules, in one application' }}
                    </h2>
                    <p class="text-gray-500 text-lg">{{ lang === 'fr' ? 'Adaptés à votre formule · Activables selon vos besoins' : 'Tailored to your plan · Activate what you need' }}</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-6">
                    <div v-for="mod in [
                        { icon: '📄', nameFr: 'Facturation', nameEn: 'Invoicing', descFr: 'Devis, factures, avoirs, bons de commande', descEn: 'Quotes, invoices, credits, purchase orders', badge: 'Starter+' },
                        { icon: '👥', nameFr: 'Clients & CRM', nameEn: 'Clients & CRM', descFr: 'Fiche client, pipeline, relances auto', descEn: 'Client profile, pipeline, auto reminders', badge: 'Starter+' },
                        { icon: '📦', nameFr: 'Stock & Produits', nameEn: 'Stock & Products', descFr: 'Inventaire, alertes, codes-barres', descEn: 'Inventory, alerts, barcodes', badge: 'Pro+' },
                        { icon: '💰', nameFr: 'Trésorerie', nameEn: 'Treasury', descFr: 'Encaissements, dépenses, solde temps réel', descEn: 'Collections, expenses, real-time balance', badge: 'Pro+' },
                        { icon: '🏪', nameFr: 'Caisse POS', nameEn: 'POS Register', descFr: 'Point de vente tactile, ticket thermique', descEn: 'Touch point of sale, thermal receipt', badge: 'Pro+' },
                        { icon: '📊', nameFr: 'Rapports', nameEn: 'Reports', descFr: 'KPIs, graphiques, exports PDF/Excel', descEn: 'KPIs, charts, PDF/Excel exports', badge: 'Starter+' },
                        { icon: '🤖', nameFr: 'Assistant IA SARA', nameEn: 'AI Assistant SARA', descFr: 'Aide, recherche, suggestions', descEn: 'Help, search, suggestions', badge: 'Tous plans' },
                        { icon: '🔗', nameFr: 'API & Webhooks', nameEn: 'API & Webhooks', descFr: 'Intégrations Zapier, Make, REST', descEn: 'Zapier, Make, REST integrations', badge: 'Business+' },
                    ]" :key="mod.nameFr"
                         class="bg-white rounded-2xl shadow-sm hover:shadow-md transition p-5 border border-gray-100 flex flex-col" style="border-left:4px solid #001d3d">
                        <div class="text-4xl mb-3">{{ mod.icon }}</div>
                        <div class="font-bold text-gray-900 mb-1">{{ lang === 'fr' ? mod.nameFr : mod.nameEn }}</div>
                        <div class="text-sm text-gray-500 flex-1 mb-3">{{ lang === 'fr' ? mod.descFr : mod.descEn }}</div>
                        <span class="self-start text-xs font-semibold px-2 py-1 rounded-full" style="background:#e8f0fe;color:#0062CC">
                            {{ lang === 'fr' ? 'Inclus dans ' : 'Included in ' }}{{ mod.badge }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ §7.11 GALERIE ═══════════════════════════════ -->
        <section class="px-6 py-24 bg-white">
            <div class="mx-auto max-w-6xl">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-extrabold mb-3" style="color:#001d3d">
                        {{ lang === 'fr' ? "Découvrez l'interface IBIG FactPro" : 'Discover the IBIG FactPro interface' }}
                    </h2>
                    <p class="text-gray-500 text-lg">{{ lang === 'fr' ? 'Conçue pour être rapide, claire et professionnelle sur tous les appareils.' : 'Designed to be fast, clear and professional on all devices.' }}</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div v-for="screen in [
                        { icon: '📊', titleFr: 'Tableau de bord', titleEn: 'Dashboard', bulletsFr: ['KPIs temps réel et CA du mois', 'Graphiques chiffre d\'affaires', 'Alertes et dernières factures'], bulletsEn: ['Real-time KPIs & monthly revenue', 'Revenue charts', 'Alerts & recent invoices'] },
                        { icon: '📄', titleFr: 'Créer une facture', titleEn: 'Create an invoice', bulletsFr: ['Sélectionnez client et produits', 'Calcul TVA automatique', 'Prêt en 30 secondes'], bulletsEn: ['Select client and products', 'Automatic VAT calculation', 'Ready in 30 seconds'] },
                        { icon: '📱', titleFr: 'Version mobile', titleEn: 'Mobile version', bulletsFr: ['Toutes les fonctions sur smartphone', 'Installable en PWA', 'Interface tactile optimisée'], bulletsEn: ['All features on smartphone', 'Installable as PWA', 'Optimised touch interface'] },
                        { icon: '🎯', titleFr: 'Rapports', titleEn: 'Reports', bulletsFr: ['Export PDF/Excel', 'Filtres par période', 'Comparaison N-1'], bulletsEn: ['PDF/Excel export', 'Period filters', 'Year-over-year comparison'] },
                    ]" :key="screen.titleFr"
                         class="rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition border border-gray-100 flex flex-col">
                        <div class="px-4 py-3 font-bold text-white text-sm flex items-center gap-2" style="background:#001d3d">
                            <span class="text-xl">{{ screen.icon }}</span>
                            {{ lang === 'fr' ? screen.titleFr : screen.titleEn }}
                        </div>
                        <div class="p-4 flex-1">
                            <ul class="space-y-2">
                                <li v-for="b in (lang === 'fr' ? screen.bulletsFr : screen.bulletsEn)" :key="b" class="text-sm text-gray-600 flex items-start gap-2">
                                    <span style="color:#0062CC;margin-top:2px">▸</span> {{ b }}
                                </li>
                            </ul>
                        </div>
                        <div class="px-4 pb-4">
                            <a href="/demo-login" class="inline-block text-xs font-semibold px-3 py-1 rounded-full" style="background:#0062CC;color:#fff">
                                {{ lang === 'fr' ? 'Voir en démo' : 'View demo' }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <a href="/#demo" class="inline-block font-bold px-8 py-4 rounded-xl text-white shadow-md hover:opacity-90 transition" style="background:#0062CC">
                        {{ lang === 'fr' ? 'Demander une démonstration personnalisée' : 'Request a personalised demo' }}
                    </a>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ §7.12 VIDÉO ═══════════════════════════════ -->
        <section class="px-6 py-24" style="background:#f8faff">
            <div class="mx-auto max-w-6xl">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-extrabold mb-3" style="color:#001d3d">
                        {{ lang === 'fr' ? 'IBIG FactPro en 2 minutes' : 'IBIG FactPro in 2 minutes' }}
                    </h2>
                </div>
                <div class="mx-auto max-w-3xl">
                    <div class="rounded-2xl shadow-xl overflow-hidden flex flex-col items-center justify-center" style="background:linear-gradient(135deg,#001d3d 0%,#002D5B 60%,#0062CC 100%);aspect-ratio:16/9">
                        <div class="flex flex-col items-center gap-5 p-8 text-center">
                            <div class="rounded-full flex items-center justify-center" style="width:80px;height:80px;background:rgba(255,255,255,0.15);border:3px solid rgba(255,255,255,0.4)">
                                <svg viewBox="0 0 24 24" fill="white" style="width:36px;height:36px;margin-left:4px"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <p class="text-white font-semibold text-lg">{{ lang === 'fr' ? 'Présentation officielle disponible prochainement' : 'Official presentation coming soon' }}</p>
                            <a href="/#demo" class="text-sm font-semibold underline" style="color:#F0C040">
                                {{ lang === 'fr' ? 'En attendant, demandez une démo en direct →' : 'Meanwhile, request a live demo →' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ §7.15 TABLEAUX DE BORD ═══════════════════════════════ -->
        <section class="px-6 py-24 bg-white">
            <div class="mx-auto max-w-6xl">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-extrabold mb-3" style="color:#001d3d">
                        {{ lang === 'fr' ? 'Des tableaux de bord adaptés à votre rôle' : 'Dashboards tailored to your role' }}
                    </h2>
                    <p class="text-gray-500 text-lg">{{ lang === 'fr' ? 'Chaque profil voit les informations dont il a besoin, en temps réel.' : 'Each profile sees the information it needs, in real time.' }}</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8">
                    <div v-for="profile in [
                        { icon: '👔', roleFr: 'Dirigeant', roleEn: 'Manager', colorFrom: '#001d3d', colorTo: '#002D5B', kpis: ['CA : 4 280 000 FCFA', 'Marge nette : 31 %', 'Top clients : 8 actifs', 'Factures en attente : 12', 'Trésorerie : +890 000 FCFA'] },
                        { icon: '💼', roleFr: 'Comptable', roleEn: 'Accountant', colorFrom: '#0062CC', colorTo: '#004fa3', kpis: ['Encaissements : 2 140 000 FCFA', 'Dépenses : 650 000 FCFA', 'Exports comptables : 3 ce mois', 'TVA collectée : 384 000 FCFA', 'Solde final : +1 490 000 FCFA'] },
                        { icon: '🏪', roleFr: 'Caissier', roleEn: 'Cashier', colorFrom: '#1a7c3e', colorTo: '#145c2e', kpis: ['Ventes du jour : 340 000 FCFA', 'Ticket moyen : 8 500 FCFA', 'Caisse ouverte depuis 08h00', 'Reçus émis : 40', 'Solde caisse : 340 000 FCFA'] },
                    ]" :key="profile.roleFr"
                         class="rounded-2xl overflow-hidden shadow-md hover:shadow-lg transition">
                        <div class="px-6 py-5 flex items-center gap-4" :style="`background:linear-gradient(135deg,${profile.colorFrom},${profile.colorTo})`">
                            <span class="text-4xl">{{ profile.icon }}</span>
                            <span class="text-xl font-extrabold text-white">{{ lang === 'fr' ? profile.roleFr : profile.roleEn }}</span>
                        </div>
                        <div class="bg-white p-6">
                            <ul class="space-y-3">
                                <li v-for="kpi in profile.kpis" :key="kpi" class="flex items-start gap-2 text-sm text-gray-700">
                                    <span style="color:#0062CC;margin-top:2px">▸</span> {{ kpi }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ §7.16 DOCUMENTS ═══════════════════════════════ -->
        <section class="px-6 py-24" style="background:#f8faff">
            <div class="mx-auto max-w-6xl">
                <div class="text-center mb-14">
                    <h2 class="text-3xl font-extrabold mb-3" style="color:#001d3d">
                        {{ lang === 'fr' ? 'Des documents professionnels à votre image' : 'Professional documents that reflect your brand' }}
                    </h2>
                    <p class="text-gray-500 text-lg">{{ lang === 'fr' ? 'Factures, devis, reçus, rapports — avec votre logo, vos couleurs, un QR code anti-falsification.' : 'Invoices, quotes, receipts, reports — with your logo, colours, and anti-fraud QR code.' }}</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-5 mb-10">
                    <div v-for="doc in [
                        { icon: '📄', titleFr: 'Facture PDF', titleEn: 'PDF Invoice', descFr: 'Logo, OHADA, QR code, mentions légales', descEn: 'Logo, OHADA, QR code, legal notices' },
                        { icon: '📋', titleFr: 'Devis signable', titleEn: 'Signable Quote', descFr: 'Lien public, signature en ligne, validité', descEn: 'Public link, online signature, validity' },
                        { icon: '🧾', titleFr: 'Ticket de caisse', titleEn: 'Receipt', descFr: 'Thermique 58/80mm, header personnalisé', descEn: '58/80mm thermal, custom header' },
                        { icon: '📊', titleFr: 'Rapport mensuel', titleEn: 'Monthly Report', descFr: 'CA, charges, marges, graphiques', descEn: 'Revenue, costs, margins, charts' },
                        { icon: '📦', titleFr: 'Bon de livraison', titleEn: 'Delivery Note', descFr: 'Avec suivi et signature client', descEn: 'With tracking and client signature' },
                        { icon: '💳', titleFr: 'Reçu de paiement', titleEn: 'Payment Receipt', descFr: 'Mobile Money, virement, espèces', descEn: 'Mobile Money, bank transfer, cash' },
                    ]" :key="doc.titleFr"
                         class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition p-5 flex flex-col gap-2">
                        <span class="text-3xl">{{ doc.icon }}</span>
                        <div class="font-bold text-gray-900">{{ lang === 'fr' ? doc.titleFr : doc.titleEn }}</div>
                        <div class="text-sm text-gray-500">{{ lang === 'fr' ? doc.descFr : doc.descEn }}</div>
                    </div>
                </div>
                <div class="text-center">
                    <span class="inline-block text-sm font-semibold px-5 py-2 rounded-full" style="background:#001d3d;color:#F0C040">
                        {{ lang === 'fr' ? '100+ modèles de documents disponibles' : '100+ document templates available' }}
                    </span>
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
                    <a href="https://wa.me/2250778882592?text=Bonjour%2C%20je%20souhaite%20une%20d%C3%A9monstration%20d%27IBIG%20FactPro." target="_blank" rel="noopener"
                       class="block w-full rounded-xl py-3.5 text-center text-sm font-bold shadow-lg transition hover:scale-105 hover:shadow-xl"
                       style="background:linear-gradient(90deg,#001d3d,#0062CC);color:#fff">
                        {{ lang === 'fr' ? '📅 Demander une démo gratuite via WhatsApp' : '📅 Request a free demo via WhatsApp' }}
                    </a>
                    <p class="text-center text-xs text-gray-400">{{ lang === 'fr' ? 'Réponse sous 24h · Démo personnalisée · Gratuit et sans engagement' : 'Reply within 24h · Personalised demo · Free & no commitment' }}</p>
                </form>
            </div>
        </section>

        <!-- ═══════════════════════════════ §7.20 — INSTALLER L'APPLICATION PWA ═══════════════════════════════ -->
        <section class="px-6 py-20" style="background:#f8faff">
            <div class="mx-auto max-w-5xl text-center">
                <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#eff6ff;color:#0062CC">PWA</span>
                <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">
                    {{ lang === 'fr' ? 'Installez IBIG FactPro sur votre appareil' : 'Install IBIG FactPro on your device' }}
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-gray-500">
                    {{ lang === 'fr'
                        ? 'Accédez plus rapidement à votre espace, depuis votre ordinateur, votre tablette ou votre smartphone.'
                        : 'Access your workspace faster, from your computer, tablet or smartphone.' }}
                </p>

                <!-- 6 avantages PWA -->
                <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                    <div v-for="perk in (lang === 'fr' ? [
                        { icon: '📱', label: 'Icône sur l\'écran d\'accueil' },
                        { icon: '⚡', label: 'Ouverture instantanée' },
                        { icon: '🔄', label: 'Mises à jour automatiques' },
                        { icon: '📡', label: 'Mode hors ligne disponible' },
                        { icon: '🖥️', label: 'Compatible tous appareils' },
                        { icon: '🔒', label: 'Sécurisé et chiffré' },
                    ] : [
                        { icon: '📱', label: 'Home screen icon' },
                        { icon: '⚡', label: 'Instant launch' },
                        { icon: '🔄', label: 'Automatic updates' },
                        { icon: '📡', label: 'Offline mode available' },
                        { icon: '🖥️', label: 'All devices compatible' },
                        { icon: '🔒', label: 'Secured & encrypted' },
                    ])" :key="perk.label"
                         class="flex flex-col items-center gap-2 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                        <span class="text-3xl">{{ perk.icon }}</span>
                        <span class="text-xs font-semibold text-gray-600 text-center leading-snug">{{ perk.label }}</span>
                    </div>
                </div>

                <!-- CTA -->
                <div class="mt-10 flex flex-col items-center gap-3">
                    <a href="/register"
                       class="inline-flex items-center gap-2 rounded-xl px-8 py-3.5 text-sm font-extrabold shadow-lg transition hover:scale-105 active:scale-95"
                       style="background:linear-gradient(135deg,#0062CC,#0099ff);color:#fff">
                        📲 {{ lang === 'fr' ? 'Installer l\'application' : 'Install the app' }}
                    </a>
                    <p class="text-xs text-gray-400">
                        {{ lang === 'fr'
                            ? 'Disponible sur Android, iOS et ordinateur · Installation sans boutique d\'applications'
                            : 'Available on Android, iOS and desktop · No app store needed' }}
                    </p>
                    <p class="text-xs text-gray-400 italic">
                        {{ lang === 'fr'
                            ? 'Sur iPhone : Appuyez sur Partager → Ajouter à l\'écran d\'accueil'
                            : 'On iPhone: tap Share → Add to Home Screen' }}
                    </p>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ §7.29 — CENTRE D'AIDE ═══════════════════════════════ -->
        <section class="px-6 py-20 bg-white">
            <div class="mx-auto max-w-5xl">
                <div class="text-center mb-12">
                    <span class="inline-block rounded-full px-4 py-1 text-xs font-bold uppercase tracking-widest" style="background:#eff6ff;color:#0062CC">{{ lang === 'fr' ? 'Support' : 'Help Center' }}</span>
                    <h2 class="mt-4 text-3xl font-extrabold text-brand-900 sm:text-4xl">
                        {{ lang === 'fr' ? 'Besoin d\'aide ? Nous sommes là.' : 'Need help? We\'re here.' }}
                    </h2>
                    <p class="mx-auto mt-3 max-w-2xl text-gray-500">
                        {{ lang === 'fr'
                            ? 'Guide utilisateur, FAQ, vidéos, tickets et assistance humaine — tout au même endroit.'
                            : 'User guide, FAQ, videos, tickets and human support — all in one place.' }}
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-3">
                    <!-- Card 1 : Guide utilisateur -->
                    <div class="flex flex-col items-center rounded-2xl border border-gray-100 bg-white p-8 shadow-sm text-center transition hover:-translate-y-1 hover:shadow-md">
                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl text-3xl" style="background:#eff6ff">📖</div>
                        <h3 class="font-bold text-brand-900 text-lg">{{ lang === 'fr' ? 'Guide utilisateur' : 'User guide' }}</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ lang === 'fr' ? 'Procédures pas à pas, captures, cas pratiques.' : 'Step-by-step procedures, screenshots, practical cases.' }}</p>
                        <a href="/help/guide" class="mt-6 inline-block rounded-xl px-5 py-2.5 text-sm font-bold transition hover:scale-105" style="background:#eff6ff;color:#0062CC">
                            {{ lang === 'fr' ? 'Consulter le guide →' : 'Read the guide →' }}
                        </a>
                    </div>

                    <!-- Card 2 : Ouvrir un ticket -->
                    <div class="flex flex-col items-center rounded-2xl border border-gray-100 bg-white p-8 shadow-sm text-center transition hover:-translate-y-1 hover:shadow-md">
                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl text-3xl" style="background:#fef3c7">🎫</div>
                        <h3 class="font-bold text-brand-900 text-lg">{{ lang === 'fr' ? 'Ouvrir un ticket' : 'Open a ticket' }}</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ lang === 'fr' ? 'Notre équipe répond sous 24h ouvrables.' : 'Our team responds within 24 business hours.' }}</p>
                        <a href="/contact" class="mt-6 inline-block rounded-xl px-5 py-2.5 text-sm font-bold transition hover:scale-105" style="background:#fef3c7;color:#b45309">
                            {{ lang === 'fr' ? 'Envoyer un ticket →' : 'Send a ticket →' }}
                        </a>
                    </div>

                    <!-- Card 3 : Parler à SARA -->
                    <div class="flex flex-col items-center rounded-2xl border border-gray-100 bg-white p-8 shadow-sm text-center transition hover:-translate-y-1 hover:shadow-md">
                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl text-3xl" style="background:#f0fdf4">💬</div>
                        <h3 class="font-bold text-brand-900 text-lg">{{ lang === 'fr' ? 'Parler à SARA' : 'Chat with SARA' }}</h3>
                        <p class="mt-2 text-sm text-gray-500 leading-relaxed">{{ lang === 'fr' ? 'L\'assistante IA répond instantanément 24h/24.' : 'The AI assistant responds instantly 24/7.' }}</p>
                        <button @click="window.openSara?.()" class="mt-6 inline-block rounded-xl px-5 py-2.5 text-sm font-bold transition hover:scale-105 cursor-pointer" style="background:#f0fdf4;color:#16a34a">
                            {{ lang === 'fr' ? 'Démarrer la conversation →' : 'Start chatting →' }}
                        </button>
                    </div>
                </div>
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
