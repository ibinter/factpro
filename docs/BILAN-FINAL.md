# IBIG FactPro — Rapport Final v2.0 — 2026-07-18

> Plateforme SaaS de facturation multi-pays, multi-devises et multi-langues basée sur Laravel 12 + Vue 3 + Inertia.

---

## Tableau des Phases

| Phase | Statut | Modules livrés |
|-------|--------|----------------|
| Phase 1 | ✅ | Bootstrap Laravel 12, authentification, multi-tenant, gestion entreprises |
| Phase 2 | ✅ | Facturation (devis, factures, avoirs), PDF multi-templates, TVA, remises |
| Phase 3 | ✅ | Paiements, acomptes, relances automatiques, portal client |
| Phase 4 | ✅ | CRM (contacts, prospects, pipeline), commandes, bons de livraison |
| Phase 5 | ✅ | Stocks & inventaire, mouvements, alertes seuil, ABC analysis |
| Phase 6 | ✅ | Caisse POS (tactile, hors-ligne), rapports Z/X, fonds de caisse |
| Phase 7 | ✅ | RH & Paie (CNSS CI/SN/CM, URSSAF France), bulletins de salaire |
| Phase 8 | ✅ | Facturation récurrente, abonnements, plans de paiement échelonné |
| Phase 9 | ✅ | API REST v1 complète + SDK PHP & JS + webhooks entrants/sortants |
| Phase 10 | ✅ | Multi-langues (FR/EN/ES/PT/AR), i18n étendu, RTL arabe |
| Phase 11 | ✅ | Fiscalité avancée : OHADA, Maroc, Sénégal, DZ, France Factur-X, OSS/UE |
| Phase 12 | ✅ | Intégrations tierces (Zapier, Make, email tracking, notifications push) |
| Phase 13 | ✅ | PWA, mode hors-ligne (IndexedDB), synchronisation différée |
| Phase 14 | ✅ | Exports comptables (Sage 100, QuickBooks, Pennylane, FEC France) |
| Phase 15 | ✅ | Rapports Z/X POS, TVA OSS/UE, boutique publique, étiquettes thermiques 110 mm |
| Phase 16 | ✅ | Scan caméra POS mobile, codes-barres 1D/2D, bon de commande automatique |
| Phase 17 | ✅ | Mobile Money direct (Wave/OM/MTN/Moov), audit final, rapport de clôture |

---

## Statistiques Techniques

| Indicateur | Valeur |
|------------|--------|
| Tests Pest verts | ~787 |
| Routes nommées | > 150 |
| Modèles Eloquent | 63 |
| Services | 49 |
| Controllers HTTP | 77 |
| Migrations | 58 |
| Templates PDF | 81 |
| Pages Vue 3 | 96 |
| Fichiers de routes | 59 |
| Langues supportées | 5 (FR, EN, ES, PT, AR) |

---

## Stack Technologique

| Couche | Technologie |
|--------|-------------|
| Backend | Laravel 12, PHP 8.3 |
| Frontend | Vue 3 + Inertia.js 2, Tailwind CSS 3 |
| Base de données | MySQL 8 (multi-tenant via `company_id`) |
| Queue | Laravel Horizon (Redis) |
| Cache | Redis |
| PDF | DomPDF / custom Blade templates |
| Tests | PestPHP 3 |
| CI/CD | GitHub Actions |
| Monitoring | Sentry, UptimeRobot |
| PWA | Workbox + Service Worker |

---

## Modules Fonctionnels

### Facturation & Documents (15 types)
- Facture standard, facture d'acompte, facture de solde
- Facture proforma, avoir, bon de commande
- Bon de livraison, devis, bon d'achat
- Facture récurrente, facture d'abonnement
- Factur-X (NF-EN 16931), e-facture OHADA
- Document de fidélité, reçu de paiement

### Gestion Commerciale
- CRM : contacts, entreprises, prospects, pipeline Kanban
- Devis avec lien public sécurisé (JWT)
- Commandes fournisseurs & bons de réception
- Gestion des acomptes et plans de paiement
- Commissions commerciaux & agents

### Finances
- Paiements multi-modes (espèces, chèque, virement, CB)
- Acomptes, échéanciers, paiements partiels
- Factures récurrentes (abonnements auto-générés)
- Avoirs et remboursements
- Plan de paiement échelonné

### POS & Stocks
- Caisse tactile hors-ligne (IndexedDB)
- Tickets thermiques 58 mm & 80 mm
- Rapport Z (clôture journalière) & rapport X (intermédiaire)
- Fonds de caisse (ouverture/fermeture)
- Gestion des stocks : alertes seuil, mouvements, valorisation FIFO
- Analyse ABC (classe A/B/C par chiffre d'affaires)
- Bon de commande automatique (BOC) au seuil de réapprovisionnement

### RH & Paie
- Fiches employés, contrats, congés, absences
- Bulletins de salaire multi-pays
- CNSS Côte d'Ivoire, Sénégal, Cameroun
- URSSAF France (DSN simplifié)
- Approbation multi-niveaux (workflow)

### Intégrations
- API REST v1 complète (Sanctum, rate-limiting)
- SDK PHP (`ibig/factpro-php`) et SDK JavaScript
- Webhooks entrants (Moneroo, CinetPay, Stripe, Zapier)
- Webhooks sortants signés HMAC-SHA256
- Zapier & Make.com (triggers + actions)
- Email tracking (pixel 1×1, opens + clicks)

### Passerelles de Paiement
- **Stripe** (cartes internationales)
- **Moneroo** (agrégateur Afrique)
- **CinetPay** (CI, SN, CM, BF, TG, BJ, MG, GN)
- **FedaPay** (Bénin & UEMOA)
- **Flutterwave** (Afrique sub-saharienne)
- **Wave** (Mobile Money CI/SN direct)
- **Orange Money** (CI/SN/ML)
- **MTN Mobile Money** (CI/GH/CM/RW)
- **Moov Africa Money** (CI/BJ/TG/BF)

### Fiscalité Multi-pays
- **OHADA** : format standard, numérotation conforme
- **Maroc** : TVA 20%/14%/10%/7%, ICE, RC, IF
- **Sénégal** : NINEA, TVA 18%, retenue à la source
- **Côte d'Ivoire** : DGI e-facture, TVA 18%, DU
- **Algérie** : NIF, TVA 19%, timbre fiscal
- **France** : Factur-X (NF-EN 16931 niveau MINIMUM et EN16931)
- **OSS/UE** : TVA intracommunautaire, déclaration trimestrielle
- **Multi-pays** : 40+ taux de TVA configurables

### Infrastructure & DevOps
- PWA (Progressive Web App) avec manifest.json
- Mode hors-ligne complet (Service Worker + Workbox)
- Synchronisation différée (queue Laravel)
- CI/CD GitHub Actions (lint, tests, déploiement)
- Sentry (error tracking production)
- UptimeRobot (monitoring disponibilité)
- Déploiement LWS Mutualisé + VPS OVH

### IA & Analytics
- **Claude AI** (Anthropic) : lecture de PDF entrants, suggestion de lignes, OCR intelligent
- **Forecasting** : prévision chiffre d'affaires (régression linéaire + saisonnalité)
- **ABC Analysis** : classification automatique des stocks
- **Email tracking** : taux d'ouverture, taux de clic, relances intelligentes
- **Tableau de bord analytique** : KPIs en temps réel, graphiques Recharts

---

## Architecture Technique

```
┌──────────────────────────────────────────────────────┐
│                   Client (Vue 3 SPA)                  │
│   Inertia.js ←→ Tailwind CSS ←→ Recharts / DayJS     │
└─────────────────────────┬────────────────────────────┘
                          │ HTTP / Inertia
┌─────────────────────────▼────────────────────────────┐
│              Laravel 12 (PHP 8.3)                     │
│   Routes → Middleware → Controllers → Services       │
│   Eloquent ORM → MySQL 8 (multi-tenant)              │
│   Queues (Redis/Horizon) → Jobs → Events             │
└──────┬───────────────┬──────────────────┬────────────┘
       │               │                  │
    MySQL 8         Redis              Storage
  (données)       (cache/queue)     (PDF, images)
```

**Multi-tenancy** : isolation par `company_id` sur toutes les tables. Chaque requête est scoped via `HasCompany` trait.

---

## Sécurité

| Mécanisme | Détail |
|-----------|--------|
| Authentification | Laravel Sanctum + 2FA TOTP (Google Authenticator) |
| Autorisation | Spatie Permission (rôles + permissions granulaires) |
| Webhooks | Signature HMAC-SHA256 sur chaque payload |
| Archives légales | Chiffrement RSA-2048 des archives ZIP horodatées |
| RGPD | Export données (DSAR), droit à l'effacement, logs d'accès |
| API | Rate limiting (60 req/min), token Sanctum par scope |
| XSS/CSRF | Protection Laravel native, Content-Security-Policy |

---

## Déploiement

### LWS Mutualisé (version starter/pro)
- PHP 8.3 via `.htaccess` FastCGI
- MySQL partagé
- Déploiement via FTP ou GitHub Actions → SSH

### VPS OVH (version business/enterprise)
- Ubuntu 22.04 LTS + Nginx + PHP-FPM 8.3
- MySQL 8 + Redis 7
- Laravel Horizon (queue workers)
- Let's Encrypt (SSL auto-renouvellement)
- GitHub Actions CI/CD (tests → build → deploy)

### Environnements
- `local` : XAMPP / Laravel Valet
- `staging` : sous-domaine dédié + base séparée
- `production` : domaine principal + `.env` sécurisé

---

## Comptes de Démonstration

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Super Admin | admin@ibig.ci | password |
| Manager | manager@demo.ci | password |
| Commercial | commercial@demo.ci | password |
| Comptable | comptable@demo.ci | password |

---

## Licence & Copyright

© 2024-2026 IBIG — Tous droits réservés.
Développé avec Laravel 12, Vue 3, PestPHP et l'écosystème IBIG.

Ce rapport a été généré automatiquement le **2026-07-18**.
