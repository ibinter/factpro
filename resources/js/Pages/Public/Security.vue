<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

defineProps({
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const pillars = [
    {
        icon: '🔐',
        title: 'Chiffrement de bout en bout',
        items: [
            'Toutes vos données sont chiffrées en AES-256 au repos et en transit (TLS 1.3)',
            'Vos mots de passe sont hachés avec bcrypt (factor 12) — même notre équipe ne peut pas les lire',
        ],
    },
    {
        icon: '🏦',
        title: 'Infrastructure sécurisée',
        items: [
            'Hébergement sur serveurs dédiés en Europe (datacenter certifié ISO 27001)',
            'Pare-feu applicatif (WAF), protection DDoS, isolation réseau stricte',
            'Accès serveur restreint par IP + authentification 2FA pour toute l\'équipe',
        ],
    },
    {
        icon: '💾',
        title: 'Sauvegardes automatiques',
        items: [
            'Sauvegarde complète quotidienne de toutes vos données',
            'Rétention 90 jours — restauration en moins de 4 heures garantie',
            'Sauvegardes chiffrées stockées dans un datacenter géographiquement séparé',
        ],
    },
    {
        icon: '🛡️',
        title: 'Protection des accès',
        items: [
            'Authentification 2 facteurs (TOTP) disponible pour tous les comptes',
            'Détection automatique des connexions suspectes avec alerte email',
            'Sessions sécurisées avec expiration automatique après inactivité',
        ],
    },
    {
        icon: '📋',
        title: 'Conformité légale',
        items: [
            'Conforme au RGPD (Règlement Général sur la Protection des Données)',
            'Droit à la portabilité : exportez toutes vos données en un clic',
            'Droit à l\'effacement : suppression complète sous 30 jours sur demande',
            'DPO désigné : dpo@ibigsoft.com',
        ],
    },
    {
        icon: '🔍',
        title: 'Tests de sécurité réguliers',
        items: [
            'Tests de pénétration (pentest) semestriels par un prestataire indépendant',
            'Veille sur les vulnérabilités (CVE), mises à jour de sécurité sous 24h',
            'Journalisation complète de toutes les actions (audit trail 12 mois)',
        ],
    },
];

const policies = [
    {
        title: 'Gestion des incidents',
        icon: '🚨',
        items: [
            'En cas d\'incident de sécurité affectant vos données, vous êtes notifié dans les 72 heures (conformément au RGPD art. 33)',
            'Canal de signalement : security@ibigsoft.com',
            'Temps de réponse moyen : 2 heures',
        ],
    },
    {
        title: 'Politique de mots de passe',
        icon: '🔑',
        items: [
            'Longueur minimale : 8 caractères',
            'Complexité recommandée : majuscule + chiffre + caractère spécial',
            'Historique : les 5 derniers mots de passe ne peuvent pas être réutilisés',
            'Nous ne vous demanderons JAMAIS votre mot de passe par email ou téléphone',
        ],
    },
    {
        title: 'Partage des données',
        icon: '🔒',
        items: [
            'Vos données ne sont JAMAIS vendues à des tiers',
            'Sous-traitants limités : hébergeur, service email transactionnel (liste disponible sur demande)',
            'Transferts hors UE encadrés par des clauses contractuelles types (CCT)',
        ],
    },
];

const faqs = [
    {
        q: 'Où sont hébergées mes données ?',
        a: 'Vos données sont stockées sur des serveurs dédiés en Europe (France/Allemagne), dans des datacenters certifiés ISO 27001, conformément au RGPD. Elles ne quittent jamais l\'Union Européenne sans encadrement juridique approprié.',
    },
    {
        q: 'Puis-je exporter toutes mes données ?',
        a: 'Oui, depuis Profil > Mes données & RGPD, exportez un ZIP complet (JSON + CSV) de toutes vos données en un clic. Cette fonctionnalité est disponible à tout moment, y compris en cas de résiliation.',
    },
    {
        q: 'Que se passe-t-il si j\'annule mon abonnement ?',
        a: 'Vos données sont conservées 90 jours après résiliation. Vous pouvez les exporter pendant cette période. Au-delà, elles sont supprimées définitivement et de manière irréversible de tous nos systèmes.',
    },
    {
        q: 'Comment signaler une faille de sécurité ?',
        a: 'Envoyez un email détaillé à security@ibigsoft.com. Nous avons un programme de responsible disclosure et nous traitons chaque signalement sérieusement. Merci de ne pas divulguer publiquement la faille avant qu\'elle soit corrigée.',
    },
    {
        q: 'IBIG FactPro est-il conforme à la réglementation africaine ?',
        a: 'Oui, nous respectons les législations locales de protection des données (notamment la loi ivoirienne n°2013-450 sur la protection des données personnelles) en plus du RGPD européen. Notre conformité s\'étend à l\'ensemble des pays où nous opérons.',
    },
];

const openFaq = ref(null);
function toggleFaq(i) {
    openFaq.value = openFaq.value === i ? null : i;
}
</script>

<template>
    <Head title="Sécurité & Confiance — IBIG FactPro">
        <meta name="description" content="Découvrez comment IBIG FactPro protège vos données avec un chiffrement de niveau bancaire, des sauvegardes quotidiennes et une conformité RGPD totale." />
    </Head>

    <PublicNav :can-login="canLogin" :can-register="canRegister" />

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 1 — HERO
    ═══════════════════════════════════════════════════════════ -->
    <section style="background:linear-gradient(135deg,#001d3d 0%,#002D5B 100%)" class="py-24 px-6 text-center text-white">
        <div class="mx-auto max-w-4xl">
            <!-- Badge -->
            <span class="inline-block mb-6 rounded-full px-4 py-1.5 text-xs font-semibold"
                  style="background:rgba(240,192,64,.15);color:#F0C040;border:1px solid rgba(240,192,64,.3)">
                🔒 Sécurité niveau bancaire
            </span>

            <h1 class="text-4xl md:text-5xl font-extrabold mb-6 leading-tight">
                Votre sécurité,<br class="hidden md:block" /> notre priorité absolue
            </h1>

            <p class="text-lg md:text-xl text-white/75 max-w-2xl mx-auto mb-10">
                IBIG FactPro protège les données de 500+ entreprises africaines avec les mêmes standards de sécurité que les institutions bancaires.
            </p>

            <!-- Badges gold -->
            <div class="flex flex-wrap justify-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-full px-5 py-2 text-sm font-semibold"
                      style="background:rgba(240,192,64,.15);color:#F0C040;border:1px solid rgba(240,192,64,.35)">
                    ✓ Chiffrement AES-256
                </span>
                <span class="inline-flex items-center gap-2 rounded-full px-5 py-2 text-sm font-semibold"
                      style="background:rgba(240,192,64,.15);color:#F0C040;border:1px solid rgba(240,192,64,.35)">
                    ✓ Conformité RGPD
                </span>
                <span class="inline-flex items-center gap-2 rounded-full px-5 py-2 text-sm font-semibold"
                      style="background:rgba(240,192,64,.15);color:#F0C040;border:1px solid rgba(240,192,64,.35)">
                    ✓ 99.97% uptime
                </span>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 2 — 6 PILIERS DE SÉCURITÉ
    ═══════════════════════════════════════════════════════════ -->
    <section class="py-20 px-6 bg-white">
        <div class="mx-auto max-w-7xl">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-extrabold mb-4" style="color:#001d3d">Les 6 piliers de notre sécurité</h2>
                <p class="text-gray-500 max-w-xl mx-auto">Une architecture de sécurité multicouche, conçue pour protéger vos données à chaque niveau.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="pillar in pillars"
                    :key="pillar.title"
                    class="rounded-2xl p-7 border transition hover:-translate-y-1 hover:shadow-lg"
                    style="border-color:#e5e7eb"
                >
                    <div class="text-3xl mb-4">{{ pillar.icon }}</div>
                    <h3 class="text-lg font-bold mb-4" style="color:#001d3d">{{ pillar.title }}</h3>
                    <ul class="space-y-2">
                        <li
                            v-for="item in pillar.items"
                            :key="item"
                            class="flex gap-2 text-sm text-gray-600"
                        >
                            <span class="mt-0.5 flex-shrink-0 text-emerald-500 font-bold">✓</span>
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 3 — POLITIQUE DE SÉCURITÉ EN DÉTAIL
    ═══════════════════════════════════════════════════════════ -->
    <section class="py-20 px-6" style="background:#f9fafb">
        <div class="mx-auto max-w-7xl">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-extrabold mb-4" style="color:#001d3d">Notre engagement de sécurité</h2>
                <p class="text-gray-500 max-w-xl mx-auto">Des politiques claires et transparentes pour vous donner le contrôle total sur vos données.</p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <div
                    v-for="policy in policies"
                    :key="policy.title"
                    class="rounded-2xl p-7 bg-white border"
                    style="border-color:#e5e7eb"
                >
                    <div class="text-2xl mb-3">{{ policy.icon }}</div>
                    <h3 class="text-lg font-bold mb-4" style="color:#001d3d">{{ policy.title }}</h3>
                    <ul class="space-y-3">
                        <li
                            v-for="item in policy.items"
                            :key="item"
                            class="flex gap-2 text-sm text-gray-600"
                        >
                            <span class="mt-0.5 flex-shrink-0" style="color:#F0C040">→</span>
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 4 — SLA & DISPONIBILITÉ
    ═══════════════════════════════════════════════════════════ -->
    <section class="py-20 px-6" style="background:linear-gradient(135deg,#001d3d 0%,#002D5B 100%)">
        <div class="mx-auto max-w-7xl text-center">
            <h2 class="text-3xl font-extrabold text-white mb-4">Nos engagements de disponibilité</h2>
            <p class="text-white/65 max-w-xl mx-auto mb-14">Des SLA clairs, mesurables, et remboursés en cas de non-respect.</p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="text-4xl font-extrabold mb-1" style="color:#F0C040">99.97%</div>
                    <div class="text-sm text-white/70">Uptime garanti sur 12 mois</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold mb-1" style="color:#F0C040">&lt; 4h</div>
                    <div class="text-sm text-white/70">Temps de restauration garanti</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold mb-1" style="color:#F0C040">24/7</div>
                    <div class="text-sm text-white/70">Surveillance des serveurs</div>
                </div>
                <div>
                    <div class="text-4xl font-extrabold mb-1" style="color:#F0C040">90 jours</div>
                    <div class="text-sm text-white/70">Rétention des sauvegardes</div>
                </div>
            </div>

            <p class="text-white/65 text-sm max-w-2xl mx-auto mb-6">
                En cas de non-respect du SLA, vous êtes remboursé au prorata. Consultez notre page Status pour l'état en temps réel des services.
            </p>
            <a
                href="/status"
                class="inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-bold transition hover:scale-105"
                style="background:#F0C040;color:#001d3d"
            >
                Voir le statut en temps réel →
            </a>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 5 — FAQ SÉCURITÉ (ACCORDION)
    ═══════════════════════════════════════════════════════════ -->
    <section class="py-20 px-6 bg-white">
        <div class="mx-auto max-w-3xl">
            <div class="text-center mb-14">
                <h2 class="text-3xl font-extrabold mb-4" style="color:#001d3d">Questions fréquentes sur la sécurité</h2>
                <p class="text-gray-500">Tout ce que vous devez savoir sur la protection de vos données.</p>
            </div>

            <div class="space-y-3">
                <div
                    v-for="(faq, i) in faqs"
                    :key="i"
                    class="rounded-2xl border overflow-hidden"
                    style="border-color:#e5e7eb"
                >
                    <button
                        @click="toggleFaq(i)"
                        class="w-full flex items-center justify-between gap-4 px-6 py-5 text-left font-semibold text-sm transition hover:bg-gray-50"
                        style="color:#001d3d"
                    >
                        <span>{{ faq.q }}</span>
                        <span
                            class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-transform"
                            :style="openFaq === i ? 'background:#002D5B;color:#F0C040;transform:rotate(45deg)' : 'background:#f3f4f6;color:#6b7280'"
                        >+</span>
                    </button>
                    <div
                        v-show="openFaq === i"
                        class="px-6 pb-5 text-sm text-gray-600 leading-relaxed border-t"
                        style="border-color:#f3f4f6"
                    >
                        <p class="pt-4">{{ faq.a }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════════════════════════════════════════════
         SECTION 6 — CTA FINAL
    ═══════════════════════════════════════════════════════════ -->
    <section class="py-20 px-6" style="background:#f9fafb">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-extrabold mb-4" style="color:#001d3d">
                Des questions sur la sécurité de vos données ?
            </h2>
            <p class="text-gray-500 mb-10">
                Notre équipe sécurité est disponible pour répondre à toutes vos questions.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a
                    href="mailto:security@ibigsoft.com"
                    class="inline-flex items-center justify-center gap-2 rounded-xl px-7 py-3.5 text-sm font-bold text-white transition hover:opacity-90"
                    style="background:#002D5B"
                >
                    Contacter la sécurité
                </a>
                <Link
                    href="/register"
                    class="inline-flex items-center justify-center gap-2 rounded-xl px-7 py-3.5 text-sm font-bold transition hover:scale-105"
                    style="background:#F0C040;color:#001d3d"
                >
                    Commencer gratuitement
                </Link>
            </div>
        </div>
    </section>

    <PublicFooter />
</template>
