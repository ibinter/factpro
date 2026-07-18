# IBIG FactPro

**SaaS de facturation pensé pour l'Afrique** — devis, factures et documents commerciaux avec QR d'authenticité anti-falsification, tarification en FCFA et paiement Mobile Money.

- **Éditeur** : IBIG SARL — Abidjan, Côte d'Ivoire
- **Produit** : [factpro.ibigsoft.com](https://factpro.ibigsoft.com)
- **Contact** : factpro@ibigsoft.com

---

## Présentation

IBIG FactPro permet aux TPE/PME, commerçants et indépendants de la zone OHADA de créer des documents commerciaux professionnels (devis, factures, bons de livraison, tickets de caisse…) en quelques clics, avec :

- **FCFA natif (XOF)** : devise par défaut, TVA 18 % préconfigurée, grille tarifaire en FCFA.
- **QR anti-falsification** : chaque document finalisé est scellé par un hash **SHA-256** de son contenu canonique. Le QR imprimé sur le PDF pointe vers une page publique `/verify/{uuid}` qui affiche **AUTHENTIQUE** ou **FALSIFIÉ** — sans compte requis.
- **Essai gratuit 7 jours** : à l'inscription, chaque utilisateur reçoit une licence d'essai (fonctionnalités du plan PRO) ; les documents émis pendant l'essai portent le filigrane « VERSION ESSAI FACTPRO ».
- **Paiement Mobile Money manuel** : l'abonné paie via Orange Money, Wave, MTN MoMo, Moov ou virement bancaire, dépose sa **preuve de paiement** (capture/PDF, stockée en privé), puis un **superadmin valide** le paiement depuis une console dédiée — la licence est activée automatiquement et de façon idempotente. Une intégration Moneroo (agrégateur) est prévue en parallèle (webhook signé).

```
┌─────────────┐   finalize    ┌──────────────┐   scan QR   ┌──────────────────────┐
│  FAC-2026-  │ ────────────► │ hash SHA-256 │ ──────────► │  /verify/{uuid}       │
│  0001 (PDF) │   + QR code   │ scellé en BDD│             │  ✔ DOCUMENT AUTHENTIQUE│
└─────────────┘               └──────────────┘             └──────────────────────┘
```

## Stack technique

| Couche | Technologie |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Authentification | Laravel Breeze (Inertia) + Sanctum |
| Frontend | Vue 3 + Inertia.js + Ziggy |
| CSS | Tailwind CSS (charte IBIG : bleu `#0062CC`, marine `#002D5B`, or `#F0C040`) |
| Base de données | MySQL / MariaDB |
| PDF | barryvdh/laravel-dompdf (DomPDF) |
| QR Code | chillerlan/php-qrcode |
| Build | Vite 6, Node 20+ |
| Tests | Pest / PHPUnit |

## Fonctionnalités actuelles (MVP Phase 1 + module licences)

### Facturation
- **12 types de documents** : Devis (DEV), Facture Proforma (PRO), Bon de Commande (BC), Commande Fournisseur (BCF), Bon de Livraison (BL), Facture (FAC), Avoir (AV), Reçu de Paiement (REC), Facture d'Acompte (FA), Facture de Solde (FS), Bon de Travaux (BT), Ticket de Caisse (TK).
- Numérotation automatique atomique par société, type et année : `FAC-2026-0001`.
- Lignes avec produit/service, quantité, unité, remise ligne, TVA par ligne ; remise globale (pourcentage ou fixe) répartie au prorata pour la TVA.
- **Conversion de documents** (devis → facture, commande → BL, facture → avoir, acompte → solde…).
- **Finalisation / scellement** : hash SHA-256 + horodatage ; un document finalisé n'est plus modifiable ni supprimable.
- Export **PDF A4** avec QR d'authenticité et filigrane essai le cas échéant (scellement automatique à la première génération).
- **Encaissements** sur documents (espèces, mobile money, carte, virement, chèque, crédit) avec statuts `partial` / `paid`.
- Page publique de vérification `/verify/{uuid}`.

### Gestion
- Multi-sociétés (table pivot `company_user` avec rôles owner/admin/member/cashier), société courante par utilisateur.
- Clients (particuliers / entreprises) et produits/services (SKU, code-barres, prix, coût, TVA, suivi de stock basique avec seuil d'alerte).
- Tableau de bord : CA du mois, encours, devis en attente, graphique CA 6 mois, derniers documents.

### Licences & abonnements
- 4 plans en FCFA (voir `database/seeders/PlanSeeder.php`) : **STARTER 2 500**, **PRO 10 000**, **BUSINESS 15 000**, **ENTERPRISE 25 000** FCFA/mois — 12 mois payés = 10 (−20 %).
- Essai 7 jours à l'inscription, limites par plan (documents/mois, utilisateurs, clients, produits… stockées en JSON).
- Commandes (1/3/6/12 mois, expiration 72 h), déclaration de paiement manuel avec preuve privée, **console superadmin** de validation/rejet avec **score de risque anti-fraude** (référence dupliquée, preuve réutilisée par hash, montant hors tolérance, compte récent).
- Journal d'audit complet (`payment_audit_logs`) sur toutes les actions sensibles.
- Middleware `license` : les fonctionnalités métier sont bloquées si la licence n'est plus utilisable (les données sont conservées, redirection vers les forfaits).

## Prérequis

- **PHP 8.2 ou supérieur** avec extensions : `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `curl`, `intl`, `bcmath`, `xml`, `dom`, `fileinfo`
- **Composer** 2.x
- **Node.js 20+** et npm
- **MySQL 8** ou **MariaDB 10.6+**

## Installation locale pas à pas

```bash
# 1. Récupérer le code
git clone <url-du-depot> factpro
cd factpro

# 2. Dépendances PHP
composer install

# 3. Configuration
cp .env.example .env
# → renseigner DB_DATABASE=factpro, DB_USERNAME, DB_PASSWORD
php artisan key:generate

# 4. Base de données (créer la base "factpro" au préalable)
php artisan migrate

# 5. Données initiales (plans + comptes de démo + jeu d'essai)
php artisan db:seed

# 6. Frontend
npm install
npm run build        # ou "npm run dev" pendant le développement

# 7. Lancer le serveur
php artisan serve
# → http://localhost:8000
```

## Comptes de démonstration

Créés par `php artisan db:seed` :

| Rôle | Email | Mot de passe |
|---|---|---|
| Superadmin IBIG | `admin@ibigsoft.com` | `Admin@Factpro2026` |
| Utilisateur démo (société « IBIG Démo SARL », essai 7 j, clients/produits/documents d'exemple) | `demo@factpro.test` | `Demo@2026` |

> ⚠️ Changez impérativement ces mots de passe en production.

## Structure du projet

```
factpro/
├── app/
│   ├── Http/
│   │   ├── Controllers/          # Dashboard, Customer, Product, Document, Billing,
│   │   │   │                     # Verify (public), MonerooPayment, Webhook
│   │   │   └── Admin/            # PaymentValidationController (console superadmin)
│   │   └── Middleware/           # EnsureLicenseActive, EnsureSuperadmin, HandleInertiaRequests
│   ├── Models/                   # Company, Customer, Product, Document, DocumentLine,
│   │                             # DocumentPayment, DocumentSequence, Plan, Order,
│   │                             # PaymentTransaction, PaymentProof, License,
│   │                             # WebhookEvent, PaymentAuditLog, PaymentMethodConfig, User
│   └── Services/                 # DocumentService, DocumentNumberService,
│                                 # DocumentIntegrityService, QrCodeService,
│                                 # LicenseService, PaymentService, MonerooService
├── config/factpro.php            # Config applicative (essai, licences, Moneroo, preuves, fraude)
├── database/
│   ├── migrations/               # Schéma complet (voir docs/architecture.md)
│   └── seeders/                  # PlanSeeder (grille FCFA), DatabaseSeeder (démo)
├── docs/                         # Architecture, déploiement LWS, roadmap
├── resources/
│   ├── js/Pages/                 # Pages Inertia/Vue 3 (Dashboard, Documents, Billing, Admin…)
│   └── views/pdf/document.blade.php  # Template PDF (QR + filigrane)
└── routes/
    ├── web.php                   # Routes applicatives (auth, license, superadmin)
    ├── auth.php                  # Breeze
    └── webhooks.php              # Webhook Moneroo (sans CSRF, signature HMAC)
```

## Documentation

- [`docs/architecture.md`](docs/architecture.md) — schéma des données, cycles de vie (document, licence, paiement), services, middleware.
- [`docs/deploiement-lws.md`](docs/deploiement-lws.md) — déploiement sur hébergement mutualisé LWS (SSH).
- [`docs/roadmap.md`](docs/roadmap.md) — feuille de route Phases 1 à 4.

## Licence

Logiciel propriétaire — © IBIG SARL. Tous droits réservés.
Toute reproduction, distribution ou utilisation non autorisée est interdite. Contact : factpro@ibigsoft.com.
