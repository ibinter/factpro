<script setup>
import { Head } from '@inertiajs/vue3';
import PublicNav from '@/Pages/Public/Partials/PublicNav.vue';
import PublicFooter from '@/Pages/Public/Partials/PublicFooter.vue';

defineProps({
    canLogin:    { type: Boolean, default: true },
    canRegister: { type: Boolean, default: true },
});

const parcours = [
    {
        icon: '🚀',
        titre: 'Démarrage rapide',
        description: 'Créez votre compte, configurez votre société et émettez votre première facture en moins de 10 minutes.',
        niveau: 'Débutant',
        duree: '10 min',
        lecons: 4,
        progress: 0,
        slug: 'demarrage-rapide',
    },
    {
        icon: '📄',
        titre: 'Facturation complète',
        description: 'Devis, factures, avoirs, récurrences, modèles, QR code.',
        niveau: 'Intermédiaire',
        duree: '45 min',
        lecons: 8,
        progress: 0,
        slug: 'facturation-complete',
    },
    {
        icon: '👥',
        titre: 'Clients & CRM',
        description: 'Importer, segmenter, relancer et fidéliser vos clients.',
        niveau: 'Intermédiaire',
        duree: '30 min',
        lecons: 6,
        progress: 0,
        slug: 'clients-crm',
    },
    {
        icon: '💰',
        titre: 'Paiements & Trésorerie',
        description: 'Mobile Money, encaissements, rapprochements, trésorerie.',
        niveau: 'Intermédiaire',
        duree: '40 min',
        lecons: 7,
        progress: 0,
        slug: 'paiements-tresorerie',
    },
    {
        icon: '📊',
        titre: 'Rapports & Décisions',
        description: 'KPIs, graphiques, exports, tableau de bord dirigeant.',
        niveau: 'Avancé',
        duree: '35 min',
        lecons: 6,
        progress: 0,
        slug: 'rapports-decisions',
    },
    {
        icon: '👔',
        titre: 'Administration',
        description: 'Utilisateurs, rôles, permissions, sauvegardes, paramètres.',
        niveau: 'Avancé',
        duree: '50 min',
        lecons: 9,
        progress: 0,
        slug: 'administration',
    },
    {
        icon: '📱',
        titre: 'Mobile & PWA',
        description: 'Installer FactPro, travailler hors ligne, synchroniser.',
        niveau: 'Débutant',
        duree: '15 min',
        lecons: 3,
        progress: 0,
        slug: 'mobile-pwa',
    },
    {
        icon: '🔒',
        titre: 'Sécurité & RGPD',
        description: '2FA, journal d\'audit, RGPD, protection des données.',
        niveau: 'Avancé',
        duree: '25 min',
        lecons: 5,
        progress: 0,
        slug: 'securite-rgpd',
    },
];

const ressources = [
    {
        icon: '📥',
        titre: 'Guide de démarrage PDF (FR)',
        description: 'Prise en main en 10 étapes',
        taille: '2.4 Mo',
        slug: 'guide-demarrage-fr',
    },
    {
        icon: '📥',
        titre: 'Guide de démarrage PDF (EN)',
        description: '10-step getting started guide',
        taille: '2.1 Mo',
        slug: 'guide-demarrage-en',
    },
    {
        icon: '📥',
        titre: 'Modèle import clients CSV',
        description: 'Format officiel FactPro',
        taille: '12 Ko',
        slug: 'modele-import-clients',
    },
    {
        icon: '📥',
        titre: 'Modèle import produits CSV',
        description: 'Format officiel avec codes-barres',
        taille: '15 Ko',
        slug: 'modele-import-produits',
    },
    {
        icon: '📥',
        titre: 'Checklist démarrage',
        description: 'Liste de vérification imprimable',
        taille: '380 Ko',
        slug: 'checklist-demarrage',
    },
    {
        icon: '📥',
        titre: 'Glossaire OHADA & facturation',
        description: 'Définitions clés',
        taille: '560 Ko',
        slug: 'glossaire-ohada',
    },
];

const videos = [
    { titre: 'Créer votre première facture', duree: '~8 min' },
    { titre: 'Configurer les paiements Mobile Money', duree: '~12 min' },
    { titre: 'Gérer votre équipe et les permissions', duree: '~10 min' },
    { titre: 'Analyser vos performances avec les rapports', duree: '~15 min' },
];

function niveauClass(niveau) {
    if (niveau === 'Débutant')      return 'badge-debutant';
    if (niveau === 'Intermédiaire') return 'badge-intermediaire';
    return 'badge-avance';
}
</script>

<template>
    <Head title="Académie IBIG FactPro — Formations et ressources">
        <meta name="description" content="Maîtrisez FactPro grâce à nos parcours de formation, guides PDF et ressources téléchargeables." />
    </Head>

    <PublicNav :can-login="canLogin" :can-register="canRegister" />

    <!-- ── Hero ── -->
    <section class="hero-section">
        <div class="hero-inner">
            <div class="hero-badge">🎓 Académie IBIG FactPro</div>
            <h1 class="hero-title">
                Académie IBIG FactPro<br />
                <span class="hero-title-accent">Formations et ressources</span>
            </h1>
            <p class="hero-subtitle">
                Maîtrisez FactPro à votre rythme grâce à nos guides, vidéos et exercices.
            </p>
            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-number">8</span>
                    <span class="stat-label">Parcours</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-number">48</span>
                    <span class="stat-label">Leçons</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-number">6</span>
                    <span class="stat-label">Ressources</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-number">100%</span>
                    <span class="stat-label">Gratuit</span>
                </div>
            </div>
        </div>
    </section>

    <main class="academy-main">

        <!-- ── Parcours de formation ── -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Parcours de formation</h2>
                <p class="section-subtitle">8 parcours structurés pour tous les niveaux</p>
            </div>

            <div class="parcours-grid">
                <div
                    v-for="p in parcours"
                    :key="p.slug"
                    class="parcours-card"
                >
                    <!-- Badge "Vidéos bientôt" -->
                    <div class="video-badge">🎬 Vidéos disponibles prochainement</div>

                    <div class="card-body">
                        <div class="card-icon">{{ p.icon }}</div>

                        <div class="card-meta-top">
                            <span :class="['niveau-badge', niveauClass(p.niveau)]">{{ p.niveau }}</span>
                        </div>

                        <h3 class="card-title">{{ p.titre }}</h3>
                        <p class="card-desc">{{ p.description }}</p>

                        <div class="card-info">
                            <span class="info-chip">⏱ {{ p.duree }}</span>
                            <span class="info-chip">📚 {{ p.lecons }} leçons</span>
                        </div>

                        <!-- Progress bar fictive -->
                        <div class="progress-wrap">
                            <div class="progress-bar">
                                <div class="progress-fill" :style="{ width: p.progress + '%' }"></div>
                            </div>
                            <span class="progress-label">{{ p.progress }}% complété</span>
                        </div>
                    </div>

                    <div class="card-footer">
                        <a
                            :href="`/academy/parcours/${p.slug}`"
                            class="btn-start"
                        >
                            Commencer →
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Bibliothèque de ressources ── -->
        <section class="section section-alt">
            <div class="section-header">
                <h2 class="section-title">Bibliothèque de ressources</h2>
                <p class="section-subtitle">Guides, modèles et checklists à télécharger gratuitement</p>
            </div>

            <div class="ressources-grid">
                <div
                    v-for="r in ressources"
                    :key="r.slug"
                    class="ressource-card"
                >
                    <div class="ressource-icon">{{ r.icon }}</div>
                    <div class="ressource-info">
                        <h4 class="ressource-titre">{{ r.titre }}</h4>
                        <p class="ressource-desc">{{ r.description }}</p>
                        <span class="ressource-taille">{{ r.taille }}</span>
                    </div>
                    <a
                        :href="`/academy/download/${r.slug}`"
                        class="btn-download"
                    >
                        Télécharger
                    </a>
                </div>
            </div>
        </section>

        <!-- ── Vidéos à venir ── -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Vidéos de formation à venir</h2>
                <p class="section-subtitle">Notre équipe prépare des tutoriels vidéo pour vous accompagner</p>
            </div>

            <div class="videos-grid">
                <div
                    v-for="(v, i) in videos"
                    :key="i"
                    class="video-card"
                >
                    <div class="video-thumb">
                        <div class="play-btn">▶</div>
                    </div>
                    <div class="video-meta">
                        <h4 class="video-titre">{{ v.titre }}</h4>
                        <div class="video-footer">
                            <span class="video-duree">{{ v.duree }}</span>
                            <span class="badge-soon">En préparation</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── CTA contact ── -->
        <section class="section cta-section">
            <div class="cta-box">
                <div class="cta-icon">💬</div>
                <h3 class="cta-title">Besoin d'une formation personnalisée ?</h3>
                <p class="cta-desc">
                    Nos experts IBIG Soft organisent des sessions de formation en ligne ou en présentiel pour votre équipe.
                </p>
                <div class="cta-actions">
                    <a href="mailto:support@ibigsoft.com" class="btn-cta-primary">
                        Demander une formation
                    </a>
                    <a href="/help" class="btn-cta-secondary">
                        Centre d'aide
                    </a>
                </div>
            </div>
        </section>

    </main>

    <PublicFooter />
</template>

<style scoped>
/* ── Layout globals ── */
.academy-main {
    background: #f8fafc;
}

/* ── Hero ── */
.hero-section {
    background: linear-gradient(135deg, #002D5B 0%, #0062CC 60%, #1a56db 100%);
    padding: 4rem 1.5rem 5rem;
    text-align: center;
}
.hero-inner {
    max-width: 780px;
    margin: 0 auto;
}
.hero-badge {
    display: inline-block;
    background: rgba(240, 192, 64, 0.18);
    color: #F0C040;
    border: 1px solid rgba(240, 192, 64, 0.35);
    padding: 0.35rem 1rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    margin-bottom: 1.25rem;
}
.hero-title {
    font-size: clamp(1.75rem, 4vw, 2.75rem);
    font-weight: 900;
    color: #fff;
    line-height: 1.2;
    margin-bottom: 1rem;
}
.hero-title-accent {
    color: #F0C040;
}
.hero-subtitle {
    font-size: 1.05rem;
    color: rgba(255,255,255,.75);
    margin-bottom: 2rem;
}
.hero-stats {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0;
    flex-wrap: wrap;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.15);
    border-radius: 1rem;
    padding: 1rem 1.5rem;
    display: inline-flex;
}
.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0 1.5rem;
}
.stat-number {
    font-size: 1.5rem;
    font-weight: 900;
    color: #F0C040;
    line-height: 1;
}
.stat-label {
    font-size: 0.7rem;
    color: rgba(255,255,255,.6);
    margin-top: 0.2rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.stat-divider {
    width: 1px;
    height: 2rem;
    background: rgba(255,255,255,.2);
}

/* ── Sections ── */
.section {
    padding: 4rem 1.5rem;
    max-width: 1280px;
    margin: 0 auto;
}
.section-alt {
    background: #fff;
    max-width: 100%;
    padding: 4rem 0;
}
.section-alt > * {
    max-width: 1280px;
    margin-left: auto;
    margin-right: auto;
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}
.section-header {
    text-align: center;
    margin-bottom: 2.5rem;
}
.section-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #002D5B;
    margin-bottom: 0.5rem;
}
.section-subtitle {
    color: #64748b;
    font-size: 0.95rem;
}

/* ── Parcours grid ── */
.parcours-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
}
@media (min-width: 640px) {
    .parcours-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (min-width: 1024px) {
    .parcours-grid { grid-template-columns: repeat(4, 1fr); }
}

.parcours-card {
    position: relative;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 1rem;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.2s, transform 0.2s;
}
.parcours-card:hover {
    box-shadow: 0 8px 32px rgba(0, 45, 91, 0.12);
    transform: translateY(-2px);
}

.video-badge {
    position: absolute;
    top: 0.6rem;
    right: 0.6rem;
    background: rgba(0, 45, 91, 0.07);
    color: #002D5B;
    font-size: 0.62rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    border: 1px solid rgba(0,45,91,0.1);
    max-width: 130px;
    text-align: center;
    line-height: 1.3;
    z-index: 1;
}

.card-body {
    padding: 1.25rem 1rem 0.75rem;
    flex: 1;
}
.card-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
}
.card-meta-top {
    margin-bottom: 0.5rem;
}
.niveau-badge {
    display: inline-block;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.badge-debutant      { background: #dcfce7; color: #166534; }
.badge-intermediaire { background: #fef3c7; color: #92400e; }
.badge-avance        { background: #fee2e2; color: #991b1b; }

.card-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #002D5B;
    margin-bottom: 0.35rem;
    line-height: 1.3;
}
.card-desc {
    font-size: 0.75rem;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 0.75rem;
}
.card-info {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem;
    margin-bottom: 0.75rem;
}
.info-chip {
    font-size: 0.68rem;
    background: #f1f5f9;
    color: #475569;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    font-weight: 600;
}
.progress-wrap {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.progress-bar {
    flex: 1;
    height: 5px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #0062CC, #F0C040);
    border-radius: 999px;
    transition: width 0.5s ease;
}
.progress-label {
    font-size: 0.65rem;
    color: #94a3b8;
    white-space: nowrap;
}

.card-footer {
    padding: 0.75rem 1rem 1rem;
    border-top: 1px solid #f1f5f9;
}
.btn-start {
    display: block;
    text-align: center;
    background: #002D5B;
    color: #fff;
    padding: 0.5rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s;
}
.btn-start:hover {
    background: #0062CC;
}

/* ── Ressources ── */
.ressources-grid {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-width: 860px;
    margin: 0 auto;
}
.ressource-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #fff;
    border: 1.5px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 1rem 1.25rem;
    transition: box-shadow 0.15s;
}
.ressource-card:hover {
    box-shadow: 0 4px 16px rgba(0,45,91,0.08);
}
.ressource-icon {
    font-size: 1.75rem;
    flex-shrink: 0;
}
.ressource-info {
    flex: 1;
    min-width: 0;
}
.ressource-titre {
    font-size: 0.875rem;
    font-weight: 700;
    color: #002D5B;
    margin-bottom: 0.15rem;
}
.ressource-desc {
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 0.2rem;
}
.ressource-taille {
    font-size: 0.68rem;
    color: #94a3b8;
    font-weight: 600;
}
.btn-download {
    flex-shrink: 0;
    background: #f1f5f9;
    color: #0062CC;
    border: 1.5px solid #bfdbfe;
    padding: 0.45rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.78rem;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s, color 0.15s;
    white-space: nowrap;
}
.btn-download:hover {
    background: #0062CC;
    color: #fff;
    border-color: #0062CC;
}

/* ── Videos ── */
.videos-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}
@media (min-width: 768px) {
    .videos-grid { grid-template-columns: repeat(4, 1fr); }
}
.video-card {
    border-radius: 0.875rem;
    overflow: hidden;
    background: #fff;
    border: 1.5px solid #e2e8f0;
}
.video-thumb {
    background: linear-gradient(135deg, #002D5B 0%, #0062CC 100%);
    height: 110px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.play-btn {
    width: 44px;
    height: 44px;
    background: rgba(255,255,255,.15);
    border: 2px solid rgba(255,255,255,.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 1rem;
    padding-left: 3px;
}
.video-meta {
    padding: 0.75rem;
}
.video-titre {
    font-size: 0.8rem;
    font-weight: 700;
    color: #002D5B;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}
.video-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.video-duree {
    font-size: 0.7rem;
    color: #94a3b8;
}
.badge-soon {
    font-size: 0.62rem;
    font-weight: 700;
    background: #fef3c7;
    color: #92400e;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
}

/* ── CTA ── */
.cta-section {
    padding-bottom: 5rem;
}
.cta-box {
    background: linear-gradient(135deg, #002D5B 0%, #0062CC 100%);
    border-radius: 1.5rem;
    padding: 3rem 2rem;
    text-align: center;
}
.cta-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
}
.cta-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 0.75rem;
}
.cta-desc {
    color: rgba(255,255,255,.75);
    max-width: 480px;
    margin: 0 auto 1.75rem;
    font-size: 0.9rem;
}
.cta-actions {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.btn-cta-primary {
    background: #F0C040;
    color: #001d3d;
    padding: 0.75rem 1.75rem;
    border-radius: 0.75rem;
    font-weight: 800;
    text-decoration: none;
    font-size: 0.9rem;
    transition: opacity 0.15s;
}
.btn-cta-primary:hover { opacity: 0.9; }
.btn-cta-secondary {
    background: rgba(255,255,255,.12);
    color: #fff;
    border: 1.5px solid rgba(255,255,255,.3);
    padding: 0.75rem 1.75rem;
    border-radius: 0.75rem;
    font-weight: 700;
    text-decoration: none;
    font-size: 0.9rem;
    transition: background 0.15s;
}
.btn-cta-secondary:hover { background: rgba(255,255,255,.2); }
</style>
