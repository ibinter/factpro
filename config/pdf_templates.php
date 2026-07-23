<?php

/*
|--------------------------------------------------------------------------
| Registre des modèles visuels PDF (cahier des charges IBIG §16)
|--------------------------------------------------------------------------
| Tableau ORDONNÉ : la limite « templates » du forfait (starter 5, pro 30,
| business 100, enterprise illimité) s'applique par array_slice sur cet
| ordre. Chaque clé correspond à une vue resources/views/pdf/templates/{key}.blade.php.
| Couleurs : primary / secondary / accent (hex) — utilisées aussi pour les
| pastilles de prévisualisation dans l'éditeur de document.
*/

return [

    // ══════════════════════════════════════════════════════════════════════
    // ── Famille LAYOUTS (structures radicalement différentes) ─────────────
    // ══════════════════════════════════════════════════════════════════════
    'layout-band' => [
        'name'        => 'Bande Latérale',
        'family'      => 'Layouts',
        'description' => 'Bande colorée latérale gauche — structure radicalement différente avec coordonnées société dans la bande.',
        'plan_min'    => 'starter',
        'primary'     => '#0062cc',
        'secondary'   => '#e8f0fe',
        'accent'      => '#f0c040',
    ],
    'layout-hero' => [
        'name'        => 'Hero Pleine Largeur',
        'family'      => 'Layouts',
        'description' => 'Bandeau héro pleine largeur avec logo centré, grille émetteur/client en cards et totaux colorés.',
        'plan_min'    => 'starter',
        'primary'     => '#7c3aed',
        'secondary'   => '#5b21b6',
        'accent'      => '#fbbf24',
    ],
    'layout-minimal' => [
        'name'        => 'Ultra Minimaliste',
        'family'      => 'Layouts',
        'description' => 'Zéro fond coloré — que du texte et des filets fins, typographie aérée et QR minuscule.',
        'plan_min'    => 'starter',
        'primary'     => '#374151',
        'secondary'   => '#9ca3af',
        'accent'      => '#6366f1',
    ],
    'layout-dark' => [
        'name'        => 'En-tête Sombre',
        'family'      => 'Layouts',
        'description' => 'Header #1a1a2e très sombre, type document en accent néon, totaux dans boîte sombre.',
        'plan_min'    => 'pro',
        'primary'     => '#0062cc',
        'secondary'   => '#1a1a2e',
        'accent'      => '#00e5ff',
    ],
    'layout-luxury' => [
        'name'        => 'Luxe Premium',
        'family'      => 'Layouts',
        'description' => 'Titre centré avec lignes décoratives flanquantes, double bordure accent, logo filigrane en fond.',
        'plan_min'    => 'pro',
        'primary'     => '#111111',
        'secondary'   => '#f9f9f9',
        'accent'      => '#c9a227',
    ],
    'layout-diagonal' => [
        'name'        => 'Diagonale Moderne',
        'family'      => 'Layouts',
        'description' => 'Header coupé en diagonale, bande accent inverse, labels de section en pastilles colorées.',
        'plan_min'    => 'pro',
        'primary'     => '#0d9488',
        'secondary'   => '#ccfbf1',
        'accent'      => '#f59e0b',
    ],
    'layout-card' => [
        'name'        => 'Cartes Rondes',
        'family'      => 'Layouts',
        'description' => 'Chaque section dans une card avec border-radius et ombre douce, totaux sur fond primaryColor.',
        'plan_min'    => 'business',
        'primary'     => '#1d4ed8',
        'secondary'   => '#eff6ff',
        'accent'      => '#f97316',
    ],
    'layout-official' => [
        'name'        => 'Officiel / Gouvernemental',
        'family'      => 'Layouts',
        'description' => 'Logo et nom société centrés en haut, titre document avec décoration ══, style notarial officiel.',
        'plan_min'    => 'business',
        'primary'     => '#1e3a5f',
        'secondary'   => '#f0f4ff',
        'accent'      => '#b45309',
    ],

    // ── Famille CORPORATE B2B ────────────────────────────────────────────
    'corporate-01' => [
        'name' => 'Corporate Marine',
        'family' => 'Corporate B2B',
        'description' => 'Le classique IBIG : marine profond, bleu institutionnel et filet or. Sérieux et intemporel.',
        'primary' => '#002D5B',
        'secondary' => '#0062CC',
        'accent' => '#F0C040',
    ],
    'corporate-02' => [
        'name' => 'Acier',
        'family' => 'Corporate B2B',
        'description' => 'Gris acier et bleu-gris industriels, structure nette pour les échanges B2B exigeants.',
        'primary' => '#37474F',
        'secondary' => '#607D8B',
        'accent' => '#B0BEC5',
    ],

    // ── Famille FUTURISTE / TECH ─────────────────────────────────────────
    'futuristic-01' => [
        'name' => 'Cyber Néon',
        'family' => 'Futuriste & Tech',
        'description' => 'En-tête nuit profonde, accents cyan néon et touches violettes : l\'esprit cyberpunk maîtrisé.',
        'primary' => '#0A0F1E',
        'secondary' => '#00E5FF',
        'accent' => '#7C4DFF',
    ],

    // ── Famille MINIMALISTE PREMIUM ──────────────────────────────────────
    'minimal-01' => [
        'name' => 'Blanc Couture',
        'family' => 'Minimaliste Premium',
        'description' => 'Blanc, filets fins, typographie légère et beaucoup d\'air : l\'élégance par la retenue.',
        'primary' => '#212121',
        'secondary' => '#9E9E9E',
        'accent' => '#E0E0E0',
    ],

    // ── Famille LUXE ─────────────────────────────────────────────────────
    'luxury-01' => [
        'name' => 'Or & Noir',
        'family' => 'Luxe',
        'description' => 'Noir intense, or patiné et titres en serif : pour les maisons haut de gamme.',
        'primary' => '#111111',
        'secondary' => '#C9A227',
        'accent' => '#EDE3C8',
    ],

    // ── Famille CRÉATIF ──────────────────────────────────────────────────
    'creative-01' => [
        'name' => 'Studio Vif',
        'family' => 'Créatif',
        'description' => 'Violet électrique et corail, en-tête asymétrique : l\'énergie des studios et agences.',
        'primary' => '#7C3AED',
        'secondary' => '#FF6B6B',
        'accent' => '#FDE68A',
    ],

    // ── Famille NATUREL / BIO ────────────────────────────────────────────
    'nature-01' => [
        'name' => 'Kraft Bio',
        'family' => 'Naturel & Bio',
        'description' => 'Fond crème kraft et verts végétaux : idéal produits bio, cosmétique naturelle, agriculture.',
        'primary' => '#2E7D32',
        'secondary' => '#81C784',
        'accent' => '#FAF6EF',
    ],

    // ── Famille ARTISAN ──────────────────────────────────────────────────
    'artisan-01' => [
        'name' => 'Atelier Bois',
        'family' => 'Artisan',
        'description' => 'Brun chaleureux et ocre doré : l\'authenticité des métiers manuels et de l\'atelier.',
        'primary' => '#6D4C41',
        'secondary' => '#D7A86E',
        'accent' => '#F3E5D8',
    ],

    // ── Famille MÉDICAL ──────────────────────────────────────────────────
    'medical-01' => [
        'name' => 'Clinique',
        'family' => 'Médical',
        'description' => 'Blanc clinique, bleu médical et vert santé discret : propreté et confiance.',
        'primary' => '#0288D1',
        'secondary' => '#4CAF50',
        'accent' => '#E1F5FE',
    ],

    // ── Famille RESTAURATION / HÔTELLERIE ────────────────────────────────
    'resto-01' => [
        'name' => 'Gastronome',
        'family' => 'Restauration & Hôtellerie',
        'description' => 'Noir & or à la carte : l\'élégance des grandes tables et maisons d\'hôtes.',
        'primary' => '#1A1A1A',
        'secondary' => '#B8860B',
        'accent' => '#F5EFD9',
    ],

    // ── Famille AFRIQUE / EXPORT ─────────────────────────────────────────
    'africa-01' => [
        'name' => 'Panafricain',
        'family' => 'Afrique & Export',
        'description' => 'Bandeau tricolore vert-or-rouge et libellés bilingues FR/EN : pensé pour l\'export.',
        'primary' => '#1B5E20',
        'secondary' => '#FBC02D',
        'accent' => '#C62828',
    ],

    // ── Famille FUTURISTE / TECH (bis) ───────────────────────────────────
    'tech-02' => [
        'name' => 'Startup Grid',
        'family' => 'Futuriste & Tech',
        'description' => 'Indigo startup et quadrillage léger : la rigueur produit des équipes tech.',
        'primary' => '#3949AB',
        'secondary' => '#5C6BC0',
        'accent' => '#E8EAF6',
    ],

    // ── Famille CORPORATE B2B (suite) ────────────────────────────────────
    'corporate-03' => [
        'name' => 'Slate Premium',
        'family' => 'Corporate B2B',
        'description' => 'Ardoise foncé et turquoise : sérieux contemporain pour les entreprises tech et conseil.',
        'primary' => '#2D3748',
        'secondary' => '#38B2AC',
        'accent' => '#FFFFFF',
    ],
    'corporate-04' => [
        'name' => 'Bordeaux Executive',
        'family' => 'Corporate B2B',
        'description' => 'Bordeaux profond, crème et or : le prestige des directions générales.',
        'primary' => '#6B2737',
        'secondary' => '#FDF6EC',
        'accent' => '#C9A227',
    ],
    'corporate-05' => [
        'name' => 'Vert Institution',
        'family' => 'Corporate B2B',
        'description' => 'Vert forêt institutionnel et or champagne : autorité et durabilité.',
        'primary' => '#1B4332',
        'secondary' => '#FFFFFF',
        'accent' => '#D4AF37',
    ],

    // ── Famille MINIMALISTE PREMIUM (suite) ──────────────────────────────
    'minimal-02' => [
        'name' => 'Gris Perle',
        'family' => 'Minimaliste Premium',
        'description' => 'Fond très clair, texte ardoise et lignes subtiles : minimalisme aéré et professionnel.',
        'primary' => '#F7FAFC',
        'secondary' => '#2D3748',
        'accent' => '#CBD5E0',
    ],
    'minimal-03' => [
        'name' => 'Noir Éditorial',
        'family' => 'Minimaliste Premium',
        'description' => 'Fond blanc, titres noirs puissants et filet rouge bordeaux : style presse haut de gamme.',
        'primary' => '#111111',
        'secondary' => '#9B1C1C',
        'accent' => '#FFFFFF',
    ],
    'minimal-04' => [
        'name' => 'Sable Pâle',
        'family' => 'Minimaliste Premium',
        'description' => 'Fond sable chaleureux, texte brun et accent brun doré : élégance naturelle et sobre.',
        'primary' => '#FEFCE8',
        'secondary' => '#78350F',
        'accent' => '#B45309',
    ],
    'minimal-05' => [
        'name' => 'Graphite Ligne',
        'family' => 'Minimaliste Premium',
        'description' => 'Gris anthracite, fond blanc et accent indigo discret : précision et modernité.',
        'primary' => '#374151',
        'secondary' => '#FFFFFF',
        'accent' => '#4338CA',
    ],

    // ── Famille LUXE (suite) ──────────────────────────────────────────────
    'luxury-02' => [
        'name' => 'Champagne',
        'family' => 'Luxe',
        'description' => 'Ivoire, or rosé et brun profond en serif : la douceur opulente des grandes maisons.',
        'primary' => '#FFFBF0',
        'secondary' => '#C9935E',
        'accent' => '#3B1F08',
    ],
    'luxury-03' => [
        'name' => 'Platine',
        'family' => 'Luxe',
        'description' => 'Blanc pur, argent et double filet : raffinement absolu, style maison de haute couture.',
        'primary' => '#FFFFFF',
        'secondary' => '#9CA3AF',
        'accent' => '#111111',
    ],
    'luxury-04' => [
        'name' => 'Velours Nuit',
        'family' => 'Luxe',
        'description' => 'Bleu nuit intense, or mat et blanc cassé : opulence sombre et mystérieuse.',
        'primary' => '#0F172A',
        'secondary' => '#FBBF24',
        'accent' => '#F1F5F9',
    ],

    // ── Famille CRÉATIF & AGENCE (suite) ─────────────────────────────────
    'creative-02' => [
        'name' => 'Dégradé Pop',
        'family' => 'Créatif',
        'description' => 'Rose fuchsia et violet en en-tête, fond blanc : énergie pop pour les agences créatives.',
        'primary' => '#EC4899',
        'secondary' => '#8B5CF6',
        'accent' => '#FFFFFF',
    ],
    'creative-03' => [
        'name' => 'Orange Studio',
        'family' => 'Créatif',
        'description' => 'Orange ardent et noir sur fond blanc cassé : la signature visuelle des studios audacieux.',
        'primary' => '#EA580C',
        'secondary' => '#111111',
        'accent' => '#FFF7ED',
    ],
    'creative-04' => [
        'name' => 'Teal Moderne',
        'family' => 'Créatif',
        'description' => 'Teal profond, blanc et ambre : fraîcheur et dynamisme pour les agences digitales.',
        'primary' => '#0D9488',
        'secondary' => '#FFFFFF',
        'accent' => '#F59E0B',
    ],

    // ── Famille RESTAURATION & HÔTELLERIE (suite) ────────────────────────
    'resto-02' => [
        'name' => 'Bistrot Rouge',
        'family' => 'Restauration & Hôtellerie',
        'description' => 'Rouge vin, crème et brun : l\'atmosphère chaleureuse des bistrots parisiens.',
        'primary' => '#8B1A1A',
        'secondary' => '#FEF9EC',
        'accent' => '#3D1F00',
    ],
    'resto-03' => [
        'name' => 'Brasserie',
        'family' => 'Restauration & Hôtellerie',
        'description' => 'Vert bouteille et doré sur fond lin blanc : l\'élégance rustique des brasseries.',
        'primary' => '#14532D',
        'secondary' => '#CA8A04',
        'accent' => '#FAFAF5',
    ],
    'resto-04' => [
        'name' => 'Suite Hôtel',
        'family' => 'Restauration & Hôtellerie',
        'description' => 'Taupe élégant, blanc et or rosé : le raffinement discret des grandes suites.',
        'primary' => '#78716C',
        'secondary' => '#FFFFFF',
        'accent' => '#D4A574',
    ],
    'resto-05' => [
        'name' => 'Street Food',
        'family' => 'Restauration & Hôtellerie',
        'description' => 'Jaune vif, noir et blanc : l\'énergie visuelle des concepts street food et food trucks.',
        'primary' => '#EAB308',
        'secondary' => '#111111',
        'accent' => '#FFFFFF',
    ],

    // ── Famille AFRIQUE & EXPORT (suite) ─────────────────────────────────
    'africa-02' => [
        'name' => 'Sahel Terre',
        'family' => 'Afrique & Export',
        'description' => 'Ocre terre, sable et vert savane : les couleurs authentiques de l\'Afrique sahélienne.',
        'primary' => '#92400E',
        'secondary' => '#FEF3C7',
        'accent' => '#166534',
    ],
    'africa-03' => [
        'name' => 'Bleu Atlantique',
        'family' => 'Afrique & Export',
        'description' => 'Bleu océan, blanc et or — libellés bilingues FR/EN : pensé pour le commerce atlantique.',
        'primary' => '#1E3A5F',
        'secondary' => '#FFFFFF',
        'accent' => '#F0C040',
    ],
    'africa-04' => [
        'name' => 'Ubuntu',
        'family' => 'Afrique & Export',
        'description' => 'Orange ubuntu, noir et blanc — devise ubuntu en en-tête : solidarité et authenticité africaine.',
        'primary' => '#EA580C',
        'secondary' => '#111111',
        'accent' => '#FFFFFF',
    ],

    // ── Famille JURIDIQUE & NOTARIAL ─────────────────────────────────────────
    'legal-01' => [
        'name' => 'Cabinet d\'Avocats',
        'family' => 'Juridique & Notarial',
        'description' => 'Style notarial sobre : bordeaux profond, crème et filets dorés. Honoraires et diligences.',
        'primary' => '#7C2D12',
        'secondary' => '#B45309',
        'accent' => '#FEF7EC',
    ],
    'legal-02' => [
        'name' => 'Étude Notariale',
        'family' => 'Juridique & Notarial',
        'description' => 'Bleu nuit et or chamois avec double filet en-tête et pied de page. Actes et émoluments.',
        'primary' => '#1E3A5F',
        'secondary' => '#C9A227',
        'accent' => '#FFFFFF',
    ],
    'legal-03' => [
        'name' => 'Huissier',
        'family' => 'Juridique & Notarial',
        'description' => 'Gris acier, rouge vif et filigrane CONFIDENTIEL discret. Actes d\'huissier et frais de justice.',
        'primary' => '#4B5563',
        'secondary' => '#DC2626',
        'accent' => '#FFFFFF',
    ],
    'legal-04' => [
        'name' => 'Justice Verte',
        'family' => 'Juridique & Notarial',
        'description' => 'Vert bouteille et or pâle : cabinet spécialisé en droit de l\'environnement.',
        'primary' => '#14532D',
        'secondary' => '#4ADE80',
        'accent' => '#FEF3C7',
    ],

    // ── Famille IMMOBILIER ───────────────────────────────────────────────────
    'immo-01' => [
        'name' => 'Agence Prestige',
        'family' => 'Immobilier',
        'description' => 'Bleu roi et or chaud avec bandeau accent premium : agence immobilière haut de gamme.',
        'primary' => '#1E40AF',
        'secondary' => '#F59E0B',
        'accent' => '#EFF6FF',
    ],
    'immo-02' => [
        'name' => 'Promoteur',
        'family' => 'Immobilier',
        'description' => 'Gris ardoise et orange brique avec bordure latérale : promotion immobilière et BTP.',
        'primary' => '#374151',
        'secondary' => '#EA580C',
        'accent' => '#FFFFFF',
    ],
    'immo-03' => [
        'name' => 'Location & Gérance',
        'family' => 'Immobilier',
        'description' => 'Vert sauge et ambre doré : gestion locative résidentielle et commerciale.',
        'primary' => '#16A34A',
        'secondary' => '#D97706',
        'accent' => '#F0FFF4',
    ],
    'immo-04' => [
        'name' => 'Syndic & Copro',
        'family' => 'Immobilier',
        'description' => 'Bleu gris et indigo sur fond blanc cassé : tableaux de charges et appels de fonds copropriété.',
        'primary' => '#475569',
        'secondary' => '#4F46E5',
        'accent' => '#F8FAFC',
    ],

    // ── Famille FINANCE & BANQUE ─────────────────────────────────────────────
    'finance-01' => [
        'name' => 'Banque Classique',
        'family' => 'Finance & Banque',
        'description' => 'Bleu marine institutionnel et or : en-tête bancaire formel avec watermark sécurité.',
        'primary' => '#1E3A5F',
        'secondary' => '#C9A227',
        'accent' => '#FEFCE8',
    ],
    'finance-02' => [
        'name' => 'Fintech',
        'family' => 'Finance & Banque',
        'description' => 'Fond noir, violet et néon vert : style application financière moderne et digitale.',
        'primary' => '#7C3AED',
        'secondary' => '#10B981',
        'accent' => '#111111',
    ],
    'finance-03' => [
        'name' => 'Microfinance UEMOA',
        'family' => 'Finance & Banque',
        'description' => 'Vert BCEAO, or et beige : adapté aux IMF et institutions de microfinance Afrique de l\'Ouest.',
        'primary' => '#15803D',
        'secondary' => '#FCD34D',
        'accent' => '#FFFBF0',
    ],
    'finance-04' => [
        'name' => 'Assurance',
        'family' => 'Finance & Banque',
        'description' => 'Rouge carmin et gris : polices d\'assurance, quittances de prime et attestations.',
        'primary' => '#9B1C1C',
        'secondary' => '#6B7280',
        'accent' => '#FEF2F2',
    ],

    // ── Famille SPORT & FITNESS ──────────────────────────────────────────────
    'sport-01' => [
        'name' => 'Salle de Sport',
        'family' => 'Sport & Fitness',
        'description' => 'Noir intense, rouge feu et typo bold : énergie brute des salles de fitness et musculation.',
        'primary' => '#DC2626',
        'secondary' => '#111111',
        'accent' => '#1F2937',
    ],
    'sport-02' => [
        'name' => 'Club Sportif',
        'family' => 'Sport & Fitness',
        'description' => 'Bleu ciel et jaune vif : adhésions et licences sportives pour clubs et associations.',
        'primary' => '#0369A1',
        'secondary' => '#EAB308',
        'accent' => '#F0F9FF',
    ],
    'sport-03' => [
        'name' => 'Coaching Personnel',
        'family' => 'Sport & Fitness',
        'description' => 'Orange vif sur fond blanc : style motivational pour coachs sportifs et préparateurs.',
        'primary' => '#EA580C',
        'secondary' => '#111111',
        'accent' => '#FFF7ED',
    ],
    'sport-04' => [
        'name' => 'Yoga & Bien-être',
        'family' => 'Sport & Fitness',
        'description' => 'Lilas doux, beige chaud et motif zen : sérénité pour studios yoga et centres bien-être.',
        'primary' => '#8B5CF6',
        'secondary' => '#DDD6FE',
        'accent' => '#FEF3C7',
    ],

    // ── Famille MODE & BEAUTÉ ────────────────────────────────────────────────
    'beauty-01' => [
        'name' => 'Salon de Beauté',
        'family' => 'Mode & Beauté',
        'description' => 'Rose pastel et or rosé : coiffure, soins esthétiques et onglerie.',
        'primary' => '#EC4899',
        'secondary' => '#F9A8D4',
        'accent' => '#FFF0F6',
    ],
    'beauty-02' => [
        'name' => 'Haute Couture',
        'family' => 'Mode & Beauté',
        'description' => 'Noir intense, blanc et or pur en serif : atelier mode et maison de couture premium.',
        'primary' => '#0A0A0A',
        'secondary' => '#D4AF37',
        'accent' => '#1A1A1A',
    ],
    'beauty-03' => [
        'name' => 'Cosmétiques Bio',
        'family' => 'Mode & Beauté',
        'description' => 'Vert menthe, blanc cassé et rose nude : beauté naturelle, bio et cruelty-free.',
        'primary' => '#065F46',
        'secondary' => '#6EE7B7',
        'accent' => '#FFFBEB',
    ],
    'beauty-04' => [
        'name' => 'Barbershop',
        'family' => 'Mode & Beauté',
        'description' => 'Bleu marine, blanc et rouge : style vintage barbier américain, coupe et rasage.',
        'primary' => '#1E3A5F',
        'secondary' => '#DC2626',
        'accent' => '#EFF6FF',
    ],

    // ── Famille TRANSPORT & LOGISTIQUE ───────────────────────────────────────
    'transport-01' => [
        'name' => 'Transporteur Routier',
        'family' => 'Transport & Logistique',
        'description' => 'En-tête orange vif, gris ardoise et blanc : lettre de voiture et factures transport routier.',
        'primary' => '#EA580C',
        'secondary' => '#374151',
        'accent' => '#FFFFFF',
    ],
    'transport-02' => [
        'name' => 'Coursier Express',
        'family' => 'Transport & Logistique',
        'description' => 'Jaune vif et noir style livraison rapide avec code suivi : coursier et messagerie express.',
        'primary' => '#EAB308',
        'secondary' => '#111111',
        'accent' => '#FFFFFF',
    ],
    'transport-03' => [
        'name' => 'Shipping Maritime',
        'family' => 'Transport & Logistique',
        'description' => 'Bleu océan et turquoise : connaissement maritime et frêt international.',
        'primary' => '#0369A1',
        'secondary' => '#FFFFFF',
        'accent' => '#0891B2',
    ],

    // ── Famille ÉDUCATION & FORMATION ────────────────────────────────────────
    'education-01' => [
        'name' => 'École Privée',
        'family' => 'Éducation & Formation',
        'description' => 'Bleu roi et or académique : frais scolaires et cotisations d\'école privée.',
        'primary' => '#1D4ED8',
        'secondary' => '#FFFFFF',
        'accent' => '#B45309',
    ],
    'education-02' => [
        'name' => 'Centre de Formation Pro',
        'family' => 'Éducation & Formation',
        'description' => 'Vert et ambre CPF : facture formation professionnelle continue éligible CPF.',
        'primary' => '#15803D',
        'secondary' => '#FFFFFF',
        'accent' => '#D97706',
    ],
    'education-03' => [
        'name' => 'Université / Supérieur',
        'family' => 'Éducation & Formation',
        'description' => 'Bordeaux et crème : frais universitaires et inscriptions en enseignement supérieur.',
        'primary' => '#7C2D12',
        'secondary' => '#FEF7EC',
        'accent' => '#B45309',
    ],

    // ── Famille SANTÉ & MÉDICAL ───────────────────────────────────────────────
    'medical-02' => [
        'name' => 'Pharmacie',
        'family' => 'Santé & Médical',
        'description' => 'Vert pharmacie et croix verte : ordonnances, ventes de médicaments et produits de santé.',
        'primary' => '#16A34A',
        'secondary' => '#FFFFFF',
        'accent' => '#16A34A',
    ],
    'medical-03' => [
        'name' => 'Clinique / Hôpital',
        'family' => 'Santé & Médical',
        'description' => 'Bleu marine et cyan : hospitalisations, soins et actes médicaux en établissement de santé.',
        'primary' => '#1E3A5F',
        'secondary' => '#FFFFFF',
        'accent' => '#0891B2',
    ],

    // ── Famille HÔTELLERIE & TOURISME ─────────────────────────────────────────
    'hotel-01' => [
        'name' => 'Hôtel Luxe',
        'family' => 'Hôtellerie & Tourisme',
        'description' => 'Or chamois et noir profond : style palace, nuitées et prestations hôtelières haut de gamme.',
        'primary' => '#C9A227',
        'secondary' => '#0A0A0A',
        'accent' => '#FFFFF0',
    ],
    'hotel-02' => [
        'name' => 'Agence de Voyages',
        'family' => 'Hôtellerie & Tourisme',
        'description' => 'Bleu ciel et orange soleil : forfaits voyage, billets et séjours touristiques.',
        'primary' => '#0284C7',
        'secondary' => '#F97316',
        'accent' => '#FFFFFF',
    ],
    'hotel-03' => [
        'name' => 'Auberge / Guest House',
        'family' => 'Hôtellerie & Tourisme',
        'description' => 'Vert olive, beige et brun : séjours conviviaux en auberge, gîte et guest house.',
        'primary' => '#4D7C0F',
        'secondary' => '#FEF3C7',
        'accent' => '#92400E',
    ],

    // ── Famille ONG & ASSOCIATIONS ────────────────────────────────────────────
    'ong-01' => [
        'name' => 'Association Caritative',
        'family' => 'ONG & Associations',
        'description' => 'Rouge humanitaire sur fond crème : reçus de dons et CERFA simplifié pour associations caritatives.',
        'primary' => '#DC2626',
        'secondary' => '#FFFFFF',
        'accent' => '#FEF2F2',
    ],
    'ong-02' => [
        'name' => 'ONG Développement',
        'family' => 'ONG & Associations',
        'description' => 'Vert solidarité et jaune espoir : projets terrain et rapports financiers d\'ONG de développement.',
        'primary' => '#16A34A',
        'secondary' => '#EAB308',
        'accent' => '#FFFFFF',
    ],
    'ong-03' => [
        'name' => 'Fondation',
        'family' => 'ONG & Associations',
        'description' => 'Bleu institutionnel et or discret : mécénat, subventions et reçus de fondations.',
        'primary' => '#1E3A5F',
        'secondary' => '#B45309',
        'accent' => '#FFFFFF',
    ],

    // ── Famille AGRICULTURE & AGROBUSINESS ───────────────────────────────────
    'agri-01' => [
        'name' => 'Exploitation Agricole',
        'family' => 'Agriculture & Agrobusiness',
        'description' => 'Vert terre et marron : vente de récoltes et produits agricoles d\'exploitation.',
        'primary' => '#4D7C0F',
        'secondary' => '#92400E',
        'accent' => '#FFFBEB',
    ],
    'agri-02' => [
        'name' => 'Coopérative Agricole',
        'family' => 'Agriculture & Agrobusiness',
        'description' => 'Vert coop et or blé : cotisations, livraisons et factures de coopérative agricole.',
        'primary' => '#15803D',
        'secondary' => '#D97706',
        'accent' => '#FFFFFF',
    ],
    'agri-03' => [
        'name' => 'Agrofournitures',
        'family' => 'Agriculture & Agrobusiness',
        'description' => 'Orange agri et vert : semences, engrais et intrants agricoles.',
        'primary' => '#EA580C',
        'secondary' => '#16A34A',
        'accent' => '#FFFFFF',
    ],

    // ── Famille BTP & CONSTRUCTION ────────────────────────────────────────────
    'btp-01' => [
        'name' => 'Entreprise BTP',
        'family' => 'BTP & Construction',
        'description' => 'Orange chantier et gris béton : devis travaux, situations d\'avancement et factures BTP.',
        'primary' => '#F97316',
        'secondary' => '#6B7280',
        'accent' => '#FFFFFF',
    ],
    'btp-02' => [
        'name' => 'Architecture',
        'family' => 'BTP & Construction',
        'description' => 'Noir épuré et or : honoraires d\'architecte, missions de conception et maîtrise d\'œuvre.',
        'primary' => '#111111',
        'secondary' => '#FFFFFF',
        'accent' => '#F0C040',
    ],
    'btp-03' => [
        'name' => 'Plomberie / Électricité',
        'family' => 'BTP & Construction',
        'description' => 'Bleu roi et orange : dépannage, installations de plomberie et d\'électricité.',
        'primary' => '#1D4ED8',
        'secondary' => '#F97316',
        'accent' => '#FFFFFF',
    ],

    // ── Famille IT & SAAS ─────────────────────────────────────────────────────
    'tech-saas-01' => [
        'name' => 'Startup SaaS',
        'family' => 'IT & SaaS',
        'description' => 'Violet et néon cyan sur fond sombre : abonnements logiciel et licences pour startups SaaS.',
        'primary' => '#7C3AED',
        'secondary' => '#111111',
        'accent' => '#06B6D4',
    ],
    'tech-saas-02' => [
        'name' => 'ESN / SSII',
        'family' => 'IT & SaaS',
        'description' => 'Bleu corporate et gris : prestations IT, régie et forfait pour ESN et SSII.',
        'primary' => '#0062CC',
        'secondary' => '#FFFFFF',
        'accent' => '#6B7280',
    ],
    'tech-saas-03' => [
        'name' => 'Freelance Dev',
        'family' => 'IT & SaaS',
        'description' => 'Style terminal vert sur fond sombre : TJM et missions pour développeurs freelance.',
        'primary' => '#374151',
        'secondary' => '#22C55E',
        'accent' => '#111111',
    ],

    // ── Famille AUTO & GARAGE ─────────────────────────────────────────────────
    'auto-01' => [
        'name' => 'Garage Auto',
        'family' => 'Auto & Garage',
        'description' => 'Gris métal et rouge : réparations automobiles, pièces détachées et main-d\'œuvre.',
        'primary' => '#4B5563',
        'secondary' => '#DC2626',
        'accent' => '#FFFFFF',
    ],
    'auto-02' => [
        'name' => 'Concessionnaire',
        'family' => 'Auto & Garage',
        'description' => 'Bleu nuit et argent : ventes de véhicules neufs et d\'occasion, contrats et garanties.',
        'primary' => '#1E3A5F',
        'secondary' => '#9CA3AF',
        'accent' => '#DC2626',
    ],

    // ── Templates de la Collection Centenniale ────────────────────────────
    'corporate-diamond' => [
        'name' => 'Corporate Diamond',
        'family' => 'Corporate B2B',
        'description' => 'Ultra-luxe : fond blanc immaculé, bordures or sur en-tête noir. Grands comptes et cabinets premium.',
        'primary' => '#1a1a1a',
        'secondary' => '#F0C040',
        'accent' => '#ffffff',
    ],
    'eco-nature' => [
        'name' => 'Eco Nature',
        'family' => 'Naturel & Bio',
        'description' => 'Verts naturels sur fond ivoire, badge Bio intégré. Produits biologiques, fermes et agriculture durable.',
        'primary' => '#2D6A4F',
        'secondary' => '#40916C',
        'accent' => '#F5F0E0',
    ],
    'digital-neon' => [
        'name' => 'Digital Neon',
        'family' => 'Futuriste & Tech',
        'description' => 'Fond sombre navy, accents cyan et violet néon, montants en monospace. Startups tech, SaaS et agences digitales.',
        'primary' => '#0D0D1A',
        'secondary' => '#00F5FF',
        'accent' => '#B400FF',
    ],
    'africa-kente' => [
        'name' => 'Africa Kente',
        'family' => 'Afrique & Export',
        'description' => 'Motif Kente géométrique or/rouge/vert en SVG, fond blanc. Template flagship de la collection IBIG FactPro.',
        'primary' => '#F0C040',
        'secondary' => '#C0392B',
        'accent' => '#27AE60',
    ],

];
