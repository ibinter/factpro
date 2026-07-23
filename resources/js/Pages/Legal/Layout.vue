<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

const props = defineProps({
    title:       { type: String, required: true },
    lastUpdated: { type: String, default: '22 juillet 2026' },
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const LINKS = [
    { label: 'Mentions légales',           href: '/legal/mentions' },
    { label: 'Conditions générales',       href: '/legal/cgu' },
    { label: 'Confidentialité',            href: '/legal/confidentialite' },
    { label: 'Cookies',                    href: '/legal/cookies' },
    { label: 'Propriété intellectuelle',   href: '/legal/pi' },
    { label: 'Résiliation',                href: '/legal/resiliation' },
    { label: 'SLA',                        href: '/legal/sla' },
    { label: 'Politique de sécurité',      href: '/legal/securite' },
    { label: 'Accessibilité',              href: '/legal/accessibilite' },
    { label: 'Remboursement',              href: '/legal/remboursement' },
    { label: 'Anti-spam',                  href: '/legal/anti-spam' },
    { label: 'Conditions API',             href: '/legal/conditions-api' },
    { label: 'Partenaires',                href: '/legal/partenaires' },
    { label: 'Utilisation acceptable',     href: '/legal/utilisation-acceptable' },
    { label: 'RGPD détaillé',             href: '/legal/rgpd-details' },
    { label: 'DPA',                        href: '/legal/dpa' },
    { label: 'Plan de continuité',         href: '/legal/plan-continuite' },
    { label: 'Charte éthique',             href: '/legal/charte-ethique' },
];

const active = computed(() => {
    if (typeof window === 'undefined') return '';
    return window.location.pathname;
});
</script>

<template>
    <Head :title="`${title} — IBIG FactPro`" />

    <div class="min-h-screen" style="background:#f8fafc">
        <PublicNav :can-login="canLogin" :can-register="canRegister" />

        <!-- Hero bandeau -->
        <div style="background:linear-gradient(135deg,#001d3d,#0062CC);padding:48px 24px 36px">
            <div style="max-width:900px;margin:0 auto">
                <a href="/" style="color:rgba(255,255,255,.55);font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;margin-bottom:16px">
                    ← Retour à l'accueil
                </a>
                <h1 style="color:#fff;font-size:28px;font-weight:900;letter-spacing:-.5px;margin:0 0 6px">{{ title }}</h1>
                <p style="color:rgba(255,255,255,.5);font-size:13px;margin:0">
                    Dernière mise à jour : <strong style="color:rgba(255,255,255,.75)">{{ lastUpdated }}</strong>
                    &nbsp;·&nbsp; IBIG SARL – IBIG FactPro
                </p>
            </div>
        </div>

        <!-- Corps -->
        <div style="max-width:900px;margin:0 auto;padding:40px 24px 80px;display:grid;grid-template-columns:220px 1fr;gap:32px;align-items:start">

            <!-- Sidebar navigation -->
            <aside style="position:sticky;top:88px">
                <div style="background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
                    <div style="padding:14px 16px;background:#f1f5f9;border-bottom:1px solid #e2e8f0">
                        <span style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#64748b">Documents légaux</span>
                    </div>
                    <nav>
                        <a v-for="link in LINKS" :key="link.href"
                           :href="link.href"
                           style="display:block;padding:11px 16px;font-size:13px;font-weight:600;text-decoration:none;border-bottom:1px solid #f1f5f9;transition:all .15s"
                           :style="link.href === active
                               ? 'background:#eff6ff;color:#0062CC;border-left:3px solid #0062CC;padding-left:13px'
                               : 'color:#374151'"
                           onmouseover="if(this.style.borderLeft!=='3px solid rgb(0, 98, 204)') this.style.background='#f8fafc'"
                           onmouseout="if(this.style.borderLeft!=='3px solid rgb(0, 98, 204)') this.style.background=''">
                            {{ link.label }}
                        </a>
                    </nav>
                    <div style="padding:14px 16px;background:#fefce8;border-top:1px solid #fef08a">
                        <div style="font-size:11px;color:#92400e;font-weight:600;margin-bottom:6px">Besoin d'aide ?</div>
                        <a href="mailto:factpro@ibigsoft.com" style="font-size:11px;color:#0062CC;text-decoration:none;font-weight:600">factpro@ibigsoft.com</a>
                    </div>
                </div>
            </aside>

            <!-- Contenu principal -->
            <main>
                <div class="legal-content" style="background:#fff;border-radius:14px;border:1px solid #e2e8f0;padding:36px 40px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
                    <slot />
                </div>
            </main>
        </div>

        <PublicFooter />
    </div>
</template>

<style>
/* Styles du contenu légal — non scoped pour atteindre le slot */
.legal-content h2 {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    margin: 28px 0 10px;
    padding: 10px 16px;
    background: linear-gradient(90deg,#eff6ff,transparent);
    border-left: 3px solid #0062CC;
    border-radius: 0 8px 8px 0;
    letter-spacing: -.2px;
}
.legal-content h2:first-child { margin-top: 0; }

.legal-content h3 {
    font-size: 13px;
    font-weight: 700;
    color: #1e40af;
    margin: 18px 0 6px;
    text-transform: uppercase;
    letter-spacing: .06em;
}

.legal-content p {
    font-size: 14px;
    line-height: 1.75;
    color: #374151;
    margin: 0 0 14px;
}

.legal-content ul {
    margin: 0 0 16px 0;
    padding: 0;
    list-style: none;
}

.legal-content ul li {
    font-size: 14px;
    line-height: 1.7;
    color: #374151;
    padding: 6px 0 6px 22px;
    position: relative;
    border-bottom: 1px solid #f1f5f9;
}
.legal-content ul li:last-child { border-bottom: none; }

.legal-content ul li::before {
    content: '›';
    position: absolute;
    left: 6px;
    color: #0062CC;
    font-weight: 900;
    font-size: 16px;
    line-height: 1.4;
}

.legal-content a {
    color: #0062CC;
    font-weight: 600;
    text-decoration: none;
}
.legal-content a:hover { text-decoration: underline; }

.legal-content strong {
    color: #0f172a;
    font-weight: 700;
}

.legal-content em {
    font-style: italic;
    color: #6b7280;
}
</style>
