<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';
import Sara from '@/Components/Sara.vue';
import CookieBanner from '@/Components/CookieBanner.vue';
import WhatsAppButton from '@/Components/WhatsAppButton.vue';

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

/* ── Document carousel ── */
const docIndex = ref(0);
const docPaused = ref(false);
const DOCS = [
    {
        type: 'Facture', num: 'FAC-2024-0842', status: 'Payée', statusColor: '#dcfce7', statusText: '#166534',
        accent: '#0062CC', client: 'KONE & Associates SARL', country: '🇨🇮',
        lines: [
            { label: 'Consulting IT × 3 mois', amount: '450 000' },
            { label: 'Maintenance annuelle', amount: '180 000' },
            { label: 'Formation équipe (5 pers.)', amount: '120 000' },
        ],
        total: '885 000 FCFA', totalEur: '≈ 1 350 €', pay: '📱 Wave CI', badge: '✓ QR infalsifiable',
        bg: 'linear-gradient(145deg,#ffffff,#f0f7ff)',
    },
    {
        type: 'Devis', num: 'DEV-2024-0317', status: 'Accepté', statusColor: '#fef9c3', statusText: '#854d0e',
        accent: '#f59e0b', client: 'SOGEMI Construction', country: '🇸🇳',
        lines: [
            { label: 'Étude & conception BTP', amount: '620 000' },
            { label: 'Matériaux fournis', amount: '380 000' },
            { label: 'Main d\'œuvre qualifiée', amount: '250 000' },
        ],
        total: '1 250 000 FCFA', totalEur: '≈ 1 905 €', pay: '🏦 Virement bancaire', badge: '✍️ Signé électroniquement',
        bg: 'linear-gradient(145deg,#fffbeb,#fff)',
    },
    {
        type: 'Bon de livraison', num: 'BL-2024-1124', status: 'Livré', statusColor: '#d1fae5', statusText: '#065f46',
        accent: '#10b981', client: 'Supermarché PROMO', country: '🇨🇲',
        lines: [
            { label: 'Huile palme 5L × 120', amount: '360 000' },
            { label: 'Riz parfumé 25kg × 40', amount: '280 000' },
            { label: 'Farine blé 50kg × 30', amount: '195 000' },
        ],
        total: '835 000 FCFA', totalEur: '≈ 1 272 €', pay: '💵 MTN MoMo', badge: '📦 Livraison confirmée',
        bg: 'linear-gradient(145deg,#f0fdf4,#fff)',
    },
    {
        type: 'Reçu de paiement', num: 'REC-2024-0589', status: 'Encaissé', statusColor: '#ede9fe', statusText: '#6d28d9',
        accent: '#7c3aed', client: 'Cabinet Dr. TRAORE', country: '🇧🇫',
        lines: [
            { label: 'Consultation médicale × 12', amount: '180 000' },
            { label: 'Actes paramédicaux', amount: '95 000' },
            { label: 'Médicaments délivrés', amount: '47 500' },
        ],
        total: '322 500 FCFA', totalEur: '≈ 491 €', pay: '💳 Orange Money', badge: '🔐 OHADA conforme',
        bg: 'linear-gradient(145deg,#faf5ff,#fff)',
    },
    {
        type: 'Bulletin de paie', num: 'BP-2024-11/KONAN', status: 'Émis', statusColor: '#fee2e2', statusText: '#991b1b',
        accent: '#ef4444', client: 'Employé : KONAN Marc', country: '🇨🇮',
        lines: [
            { label: 'Salaire brut', amount: '350 000' },
            { label: 'Cotisations sociales', amount: '-42 000' },
            { label: 'Prime performance', amount: '+25 000' },
        ],
        total: '333 000 FCFA net', totalEur: '≈ 507 €', pay: '🏦 Virement CNPS', badge: '📋 RH automatisé',
        bg: 'linear-gradient(145deg,#fff5f5,#fff)',
    },
];
let docTimer = null;
onMounted(() => {
    docTimer = setInterval(() => {
        if (!docPaused.value) docIndex.value = (docIndex.value + 1) % DOCS.length;
    }, 3200);
});
const currentDoc = computed(() => DOCS[docIndex.value]);

/* ── Compteur animé ── */
const counters = ref({ clients: 0, docs: 0, pays: 0, uptime: 0 });
const targets  = { clients: 2400, docs: 185000, pays: 12, uptime: 99 };
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
        { value: 'clients', suffix: '+', label: 'Entrepreneurs actifs' },
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
        { value: 'clients', suffix: '+', label: 'Active entrepreneurs' },
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
    <Head :title="lang === 'fr' ? 'IBIG FactPro — Facturation professionnelle pour l\'Afrique et le monde' : 'IBIG FactPro — Professional invoicing for Africa and beyond'" />

    <div class="min-h-screen bg-white text-gray-800">
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

        <!-- ═══════════════════════════════ HERO ═══════════════════════════════ -->
        <section class="relative overflow-hidden" style="background:linear-gradient(135deg,#001120 0%,#001d3d 40%,#0047a3 100%);min-height:92vh">
            <!-- Mesh background -->
            <div class="pointer-events-none absolute inset-0">
                <div style="position:absolute;top:-10%;right:-5%;width:600px;height:600px;border-radius:50%;background:radial-gradient(circle,rgba(0,98,204,.5),transparent 70%);animation:float1 9s ease-in-out infinite"></div>
                <div style="position:absolute;bottom:-15%;left:-5%;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(240,192,64,.12),transparent 70%);animation:float2 12s ease-in-out infinite"></div>
                <div style="position:absolute;top:30%;left:38%;width:2px;height:2px;border-radius:50%;background:#fff;box-shadow:0 0 80px 80px rgba(255,255,255,.02)"></div>
                <!-- Grid lines -->
                <svg class="absolute inset-0 w-full h-full opacity-5" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse"><path d="M 60 0 L 0 0 0 60" fill="none" stroke="white" stroke-width="0.5"/></pattern></defs><rect width="100%" height="100%" fill="url(#grid)"/></svg>
            </div>

            <div class="relative mx-auto grid max-w-7xl items-center gap-10 px-6 py-20 lg:grid-cols-2 lg:py-28" style="min-height:88vh">

                <!-- ── COPY ── -->
                <div class="z-10">
                    <!-- Top badge -->
                    <div class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs font-bold mb-6" style="background:rgba(240,192,64,.15);color:#F0C040;border:1px solid rgba(240,192,64,.35)">
                        <span class="inline-block h-1.5 w-1.5 rounded-full animate-pulse" style="background:#F0C040"></span>
                        {{ t.hero.badge }}
                    </div>

                    <h1 class="text-4xl font-black leading-[1.08] tracking-tight text-white sm:text-5xl xl:text-6xl">
                        {{ t.hero.h1a }}<br/>
                        <span class="relative inline-block mt-1">
                            <span style="color:#F0C040">{{ t.hero.h1b }}</span>
                            <svg class="absolute -bottom-1 left-0 w-full" height="6" viewBox="0 0 300 6" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><path d="M0 5 Q75 0 150 4 Q225 8 300 3" stroke="#F0C040" stroke-width="2.5" fill="none" opacity="0.6"/></svg>
                        </span>
                    </h1>

                    <p class="mt-6 max-w-lg text-lg leading-relaxed" style="color:rgba(255,255,255,.75)">{{ t.hero.sub }}</p>

                    <!-- Social proof mini -->
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex -space-x-2">
                            <div v-for="(c,i) in ['#0062CC','#10b981','#f59e0b','#ef4444','#7c3aed']" :key="i" class="h-8 w-8 rounded-full border-2 border-white/20 flex items-center justify-center text-xs font-bold text-white" :style="`background:${c}`">{{ ['K','A','S','M','T'][i] }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white">2 400+ entrepreneurs</div>
                            <div class="flex items-center gap-1 text-xs" style="color:#F0C040">
                                <span>★★★★★</span><span style="color:rgba(255,255,255,.5)"> 4.9/5</span>
                            </div>
                        </div>
                    </div>

                    <!-- CTAs -->
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a v-if="props.canRegister" href="/register"
                           class="group inline-flex items-center gap-2 rounded-xl px-7 py-3.5 text-base font-extrabold shadow-2xl transition-all duration-200 hover:scale-105 active:scale-95"
                           style="background:linear-gradient(135deg,#F0C040,#e8a800);color:#001d3d;box-shadow:0 8px 32px rgba(240,192,64,.4)">
                            {{ t.hero.cta1 }}
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="/demo-login"
                           class="group inline-flex items-center gap-2 rounded-xl border px-7 py-3.5 text-base font-semibold text-white transition hover:bg-white/10"
                           style="border-color:rgba(255,255,255,.25)">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full" style="background:rgba(255,255,255,.15)">▶</span>
                            {{ t.hero.cta2 }}
                        </a>
                    </div>
                    <p class="mt-3 text-xs" style="color:rgba(255,255,255,.4)">{{ t.hero.note }}</p>

                    <!-- Trust badges inline -->
                    <div class="mt-8 flex flex-wrap gap-3">
                        <span v-for="b in t.trustBadges" :key="b" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.12)">
                            <span style="color:#F0C040">✓</span> {{ b }}
                        </span>
                    </div>
                </div>

                <!-- ── DOCUMENT CAROUSEL ── -->
                <div class="relative flex justify-center lg:justify-end z-10"
                     @mouseenter="docPaused = true" @mouseleave="docPaused = false">

                    <!-- Cards stack (fake depth) -->
                    <div class="absolute top-4 right-4 w-72 h-80 rounded-2xl opacity-20 rotate-6" style="background:rgba(255,255,255,.15);backdrop-filter:blur(4px)"></div>
                    <div class="absolute top-2 right-2 w-72 h-80 rounded-2xl opacity-30 rotate-3" style="background:rgba(255,255,255,.2);backdrop-filter:blur(4px)"></div>

                    <!-- Main document card -->
                    <Transition name="doc-flip" mode="out-in">
                        <div :key="docIndex"
                             class="relative w-80 rounded-2xl shadow-2xl overflow-hidden"
                             :style="`background:${currentDoc.bg};border:1px solid rgba(0,0,0,.06)`">

                            <!-- Document header -->
                            <div class="px-6 pt-5 pb-4" :style="`background:${currentDoc.accent};`">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-bold uppercase tracking-widest text-white/60">IBIG FactPro</div>
                                        <div class="text-sm font-extrabold text-white mt-0.5">{{ currentDoc.num }}</div>
                                    </div>
                                    <div class="rounded-lg px-2.5 py-1 text-xs font-bold" :style="`background:${currentDoc.statusColor};color:${currentDoc.statusText}`">
                                        {{ currentDoc.status }}
                                    </div>
                                </div>
                                <div class="mt-3 text-xs font-semibold text-white/80">
                                    {{ currentDoc.country }} {{ currentDoc.client }}
                                </div>
                            </div>

                            <!-- Document type badge -->
                            <div class="px-6 py-2 flex items-center gap-2" :style="`background:${currentDoc.accent}22`">
                                <span class="text-xs font-black uppercase tracking-widest" :style="`color:${currentDoc.accent}`">{{ currentDoc.type }}</span>
                            </div>

                            <!-- Line items -->
                            <div class="px-6 py-4 space-y-3">
                                <div v-for="(line, i) in currentDoc.lines" :key="i" class="flex items-start justify-between gap-2">
                                    <span class="text-xs text-gray-500 leading-relaxed flex-1">{{ line.label }}</span>
                                    <span class="text-xs font-bold text-gray-800 whitespace-nowrap">{{ line.amount }}</span>
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="mx-6 mb-2 rounded-xl p-3 flex items-center justify-between" :style="`background:${currentDoc.accent}12;border:1px solid ${currentDoc.accent}30`">
                                <div>
                                    <div class="text-xs text-gray-400 uppercase tracking-wider">Total TTC</div>
                                    <div class="text-xl font-black mt-0.5" :style="`color:${currentDoc.accent}`">{{ currentDoc.total }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ currentDoc.totalEur }}</div>
                                </div>
                                <!-- Mini QR -->
                                <div class="h-12 w-12 rounded-lg grid place-items-center" :style="`background:${currentDoc.accent}`">
                                    <svg viewBox="0 0 32 32" class="h-9 w-9" fill="none">
                                        <rect x="0" y="0" width="3" height="3" fill="white"/><rect x="4" y="0" width="2" height="3" fill="white"/><rect x="8" y="0" width="3" height="3" fill="white"/>
                                        <rect x="0" y="4" width="3" height="2" fill="white"/><rect x="8" y="4" width="3" height="2" fill="white"/>
                                        <rect x="0" y="8" width="3" height="3" fill="white"/><rect x="4" y="8" width="2" height="2" fill="white"/><rect x="8" y="8" width="3" height="3" fill="white"/>
                                        <rect x="13" y="0" width="2" height="2" fill="white"/><rect x="16" y="0" width="3" height="2" fill="white"/>
                                        <rect x="13" y="3" width="3" height="2" fill="white"/><rect x="17" y="3" width="2" height="2" fill="white"/>
                                        <rect x="13" y="7" width="2" height="4" fill="white"/><rect x="16" y="5" width="3" height="3" fill="white"/>
                                        <rect x="0" y="13" width="6" height="2" fill="white"/><rect x="8" y="13" width="4" height="2" fill="white"/>
                                        <rect x="0" y="17" width="3" height="3" fill="white"/><rect x="5" y="17" width="3" height="2" fill="white"/>
                                        <rect x="10" y="16" width="2" height="4" fill="white"/><rect x="14" y="13" width="2" height="6" fill="white"/>
                                        <rect x="17" y="14" width="3" height="2" fill="white"/><rect x="17" y="18" width="2" height="2" fill="white"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="mx-6 mb-5 flex items-center justify-between">
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold" :style="`background:${currentDoc.statusColor};color:${currentDoc.statusText}`">{{ currentDoc.badge }}</span>
                                <span class="text-xs text-gray-400">{{ currentDoc.pay }}</span>
                            </div>
                        </div>
                    </Transition>

                    <!-- Dots navigation -->
                    <div class="absolute -bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-2">
                        <button v-for="(doc, i) in DOCS" :key="i"
                                @click="docIndex = i"
                                class="transition-all duration-300 rounded-full"
                                :class="i === docIndex ? 'w-6 h-2' : 'w-2 h-2'"
                                :style="i === docIndex ? `background:${DOCS[i].accent}` : 'background:rgba(255,255,255,.3)'">
                        </button>
                    </div>

                    <!-- Doc type labels (floating) -->
                    <div class="absolute -left-2 top-4 flex flex-col gap-2">
                        <div v-for="(doc, i) in DOCS" :key="i"
                             class="cursor-pointer rounded-lg px-2.5 py-1 text-xs font-bold transition-all duration-300"
                             :class="i === docIndex ? 'opacity-100 scale-100' : 'opacity-30 scale-90 hover:opacity-60'"
                             :style="`background:${doc.accent};color:white`"
                             @click="docIndex = i">
                            {{ ['📄','📝','📦','🧾','👤'][i] }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom wave -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="w-full"><path d="M0 60 L0 30 Q360 0 720 25 Q1080 50 1440 20 L1440 60 Z" fill="white"/></svg>
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
</template>

<style scoped>
@keyframes float1 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(-20px,30px) scale(1.1)} }
@keyframes float2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(30px,-20px)} }
.faq-slide-enter-active,.faq-slide-leave-active{transition:all .25s ease}
.faq-slide-enter-from,.faq-slide-leave-to{opacity:0;transform:translateY(-8px)}

/* Document carousel flip */
.doc-flip-enter-active { animation: docIn .5s cubic-bezier(0.34,1.56,0.64,1); }
.doc-flip-leave-active { animation: docOut .35s ease-in forwards; }
@keyframes docIn  { from { opacity:0; transform: translateY(24px) scale(.94) rotateX(8deg); } to { opacity:1; transform:translateY(0) scale(1) rotateX(0); } }
@keyframes docOut { from { opacity:1; transform:translateY(0) scale(1); } to { opacity:0; transform:translateY(-16px) scale(.96); } }
</style>
