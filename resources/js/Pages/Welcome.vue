<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';
import Sara from '@/Components/Sara.vue';

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
        <section class="relative overflow-hidden" style="background:linear-gradient(135deg,#001d3d 0%,#0062CC 60%,#0099ff 100%)">
            <!-- Animated blobs -->
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full opacity-20" style="background:radial-gradient(circle,#F0C040,transparent);animation:float1 8s ease-in-out infinite"></div>
                <div class="absolute -bottom-24 -left-24 h-80 w-80 rounded-full opacity-10" style="background:radial-gradient(circle,#ffffff,transparent);animation:float2 10s ease-in-out infinite"></div>
            </div>

            <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-6 py-24 lg:grid-cols-2 lg:py-32">
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs font-bold" style="background:rgba(255,255,255,.15);color:#F0C040;border:1px solid rgba(240,192,64,.3)">
                        ✨ {{ t.hero.badge }}
                    </span>
                    <h1 class="mt-6 text-4xl font-extrabold leading-tight text-white sm:text-5xl lg:text-6xl">
                        {{ t.hero.h1a }}<br />
                        <span style="color:#F0C040">{{ t.hero.h1b }}</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg text-white/80 leading-relaxed">
                        {{ t.hero.sub }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a v-if="props.canRegister" href="/register"
                           class="inline-flex items-center rounded-xl px-8 py-3.5 text-base font-bold shadow-xl transition hover:scale-105 active:scale-95"
                           style="background:#F0C040;color:#001d3d">
                            {{ t.hero.cta1 }}
                        </a>
                        <a href="/demo-login"
                           class="inline-flex items-center gap-2 rounded-xl border px-8 py-3.5 text-base font-semibold text-white transition hover:bg-white/10"
                           style="border-color:rgba(255,255,255,.3)">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ t.hero.cta2 }}
                        </a>
                        <a href="/pricing"
                           class="inline-flex items-center rounded-xl border px-8 py-3.5 text-base font-semibold text-white/70 transition hover:bg-white/10 hover:text-white"
                           style="border-color:rgba(255,255,255,.15)">
                            {{ t.hero.cta3 }}
                        </a>
                    </div>
                    <p class="mt-4 text-xs text-white/50">{{ t.hero.note }}</p>
                </div>

                <!-- Mockup facture -->
                <div class="relative hidden lg:block">
                    <div class="rotate-1 rounded-2xl bg-white p-6 shadow-2xl">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                            <div>
                                <div class="text-xs font-bold uppercase tracking-widest text-gray-400">IBIG FactPro</div>
                                <div class="text-lg font-extrabold text-brand-900">Facture #2024-0842</div>
                            </div>
                            <div class="rounded-lg px-3 py-1.5 text-xs font-bold" style="background:#dcfce7;color:#166534">✓ Payée</div>
                        </div>
                        <div class="py-4 space-y-2">
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Consulting IT × 3</span><span class="font-semibold">450 000 FCFA</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Maintenance annuelle</span><span class="font-semibold">180 000 FCFA</span></div>
                            <div class="flex justify-between text-sm"><span class="text-gray-500">Formation équipe</span><span class="font-semibold">120 000 FCFA</span></div>
                        </div>
                        <div class="flex items-end justify-between border-t border-gray-100 pt-4">
                            <div class="grid h-14 w-14 place-items-center rounded-lg text-[9px] font-bold text-white" style="background:#001d3d">
                                <svg viewBox="0 0 40 40" class="h-10 w-10" fill="none">
                                    <rect x="0" y="0" width="4" height="4" fill="white"/><rect x="6" y="0" width="2" height="4" fill="white"/><rect x="10" y="0" width="4" height="4" fill="white"/>
                                    <rect x="0" y="6" width="4" height="2" fill="white"/><rect x="10" y="6" width="4" height="2" fill="white"/>
                                    <rect x="0" y="10" width="4" height="4" fill="white"/><rect x="6" y="10" width="2" height="2" fill="white"/><rect x="10" y="10" width="4" height="4" fill="white"/>
                                    <rect x="16" y="0" width="2" height="2" fill="white"/><rect x="20" y="0" width="4" height="2" fill="white"/>
                                    <rect x="16" y="4" width="4" height="2" fill="white"/><rect x="22" y="4" width="2" height="2" fill="white"/>
                                    <rect x="16" y="8" width="2" height="6" fill="white"/><rect x="20" y="6" width="4" height="4" fill="white"/>
                                </svg>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-400">Total TTC</div>
                                <div class="text-2xl font-extrabold text-brand-900">885 000 FCFA</div>
                                <div class="text-xs text-gray-400">≈ 1 350 € · 1 490 $</div>
                            </div>
                        </div>
                    </div>
                    <!-- Badge QR -->
                    <div class="absolute -bottom-4 -left-4 -rotate-2 rounded-xl px-4 py-2.5 text-sm font-bold shadow-xl" style="background:#F0C040;color:#001d3d">
                        ✓ QR authentifié · infalsifiable
                    </div>
                    <!-- Badge Mobile Money -->
                    <div class="absolute -right-4 top-8 rotate-2 rounded-xl px-3 py-2 text-xs font-bold shadow-lg" style="background:#dcfce7;color:#166534;border:1px solid #bbf7d0">
                        📱 Payé via Wave
                    </div>
                </div>
            </div>

            <!-- Trust strip -->
            <div class="border-t" style="border-color:rgba(255,255,255,.1);background:rgba(0,0,0,.2)">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-8 gap-y-2 px-6 py-4">
                    <span v-for="badge in t.trustBadges" :key="badge" class="flex items-center gap-2 text-sm font-semibold" style="color:rgba(255,255,255,.7)">
                        <span style="color:#F0C040">◆</span> {{ badge }}
                    </span>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════ STATS ═══════════════════════════════ -->
        <section class="border-b border-gray-100 bg-white px-6 py-14">
            <div class="mx-auto grid max-w-5xl grid-cols-2 gap-8 lg:grid-cols-4">
                <div v-for="stat in t.stats" :key="stat.label" class="text-center">
                    <div class="text-4xl font-extrabold" style="color:#0062CC">
                        {{ stat.value === 'clients' ? counters.clients.toLocaleString('fr-FR')
                         : stat.value === 'docs'    ? counters.docs.toLocaleString('fr-FR')
                         : stat.value === 'pays'    ? counters.pays
                         :                            counters.uptime }}{{ stat.suffix }}
                    </div>
                    <div class="mt-1 text-sm font-medium text-gray-500">{{ stat.label }}</div>
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
</template>

<style scoped>
@keyframes float1 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(-20px,30px) scale(1.1)} }
@keyframes float2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(30px,-20px)} }
.faq-slide-enter-active,.faq-slide-leave-active{transition:all .25s ease}
.faq-slide-enter-from,.faq-slide-leave-to{opacity:0;transform:translateY(-8px)}
</style>
