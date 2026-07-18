# Architecture — IBIG FactPro

Ce document décrit le modèle de données, les cycles de vie métier et les composants applicatifs, tels qu'implémentés dans le code (Laravel 12, PHP 8.2).

## 1. Schéma des données

### 1.1 Cœur facturation

| Table | Colonnes principales | Relations |
|---|---|---|
| `users` | name, email, phone, country (déf. `CI`), locale (déf. `fr`), is_superadmin, current_company_id | ∞→1 companies (société courante), ∞↔∞ companies via `company_user`, 1→∞ licenses |
| `companies` | owner_id, name, legal_name, email, phone, address, city, country, currency (déf. `XOF`), tax_id, trade_register (RCCM), logo_path, invoice_footer, default_template, default_tax_rate (déf. 18.00), settings JSON, soft delete | ∞→1 users (owner), 1→∞ customers/products/documents |
| `company_user` (pivot) | company_id, user_id, role (`owner` \| `admin` \| `member` \| `cashier`) | unique (company_id, user_id) |
| `customers` | company_id, type (`individual` \| `company`), name, contact_name, email, phone, address, city, country, tax_id, currency, notes, soft delete | ∞→1 companies |
| `products` | company_id, type (`product` \| `service`), name, sku, barcode, description, unit, price, cost, tax_rate, stock_quantity, stock_alert_threshold, track_stock, is_active, image_path, soft delete | ∞→1 companies |
| `documents` | **uuid** (unique, identifiant public QR), company_id, customer_id, parent_id (conversion), **type** (12 types actifs), **number** (unique par société+type), reference, **status**, issue_date, due_date, currency, exchange_rate, subtotal, discount_type (`percent`\|`fixed`), discount_value, discount_amount, tax_amount, total, amount_paid, notes, terms, template_key, **integrity_hash** (SHA-256), **finalized_at**, trial_watermark, sent_at, created_by, soft delete | ∞→1 companies/customers, ∞→1 documents (parent), 1→∞ lines/payments |
| `document_lines` | document_id, product_id, description, quantity, unit, unit_price, discount_percent, tax_rate, line_total (HT après remise ligne), sort_order | ∞→1 documents/products |
| `document_payments` | company_id, document_id, amount, currency, method (`cash`\|`mobile_money`\|`card`\|`bank_transfer`\|`cheque`\|`credit`), reference, paid_at, notes, created_by | ∞→1 documents |
| `document_sequences` | company_id, document_type, prefix, next_number, padding (déf. 4), year — unique (company_id, document_type, year) | compteur atomique par société/type/année |

**Types de documents** (`Document::TYPES`) : `quote` (DEV), `proforma` (PRO), `sales_order` (BC), `purchase_order` (BCF), `delivery_note` (BL), `invoice` (FAC), `credit_note` (AV), `payment_receipt` (REC), `deposit_invoice` (FA), `balance_invoice` (FS), `work_order` (BT), `pos_ticket` (TK).

**Statuts de document** (`Document::STATUSES`) : `draft`, `sent`, `viewed`, `accepted`, `rejected`, `partial`, `paid`, `overdue`, `cancelled`, `converted`.

### 1.2 Module licences & paiements

| Table | Colonnes principales | Relations |
|---|---|---|
| `plans` | code (unique : `starter`/`pro`/`business`/`enterprise`), name, price_monthly, promo_price, currency (XOF), trial_days (7), **features JSON**, **limits JSON** (documents_per_month, users, companies, customers, products, templates, storage_mb — valeur `unlimited` = illimité), is_active, sort_order | 1→∞ orders/licenses |
| `orders` (PK UUID) | order_number (`FP-{YYYY}-{XXXXXX}`), user_id, plan_id, duration_months (1/3/6/12), amount, discount_amount, tax_amount, total_amount, currency, country, payment_method, **status**, expires_at (72 h), paid_at, metadata | ∞→1 users/plans, 1→∞ payment_transactions |
| `payment_transactions` (PK UUID) | order_id, user_id, payment_provider (`orange_money`, `mtn_momo`, `wave`, `moov`, `bank_transfer_*`, `moneroo`…), provider_reference, **internal_reference** (`FP-{YYYYMMDD}-{RANDOM6}`, unique), amount_expected/declared/received, currency, **status**, sender_name, sender_number, initiated_at, paid_at, confirmed_at, validated_by, rejection_reason, metadata | ∞→1 orders, 1→∞ payment_proofs |
| `payment_proofs` (PK UUID) | transaction_id, original_filename, stored_filename, **file_path** (stockage privé), mime_type, file_size, **file_hash** (SHA-256 anti-doublon), uploaded_by, verified_by, verification_status (`pending`\|`approved`\|`rejected`\|`complement_requested`), internal_comment | ∞→1 payment_transactions |
| `licenses` (PK UUID) | user_id, plan_id, order_id, **transaction_id** (clé d'idempotence), **license_key** (`FP-XXXX-XXXX-XXXX-XXXX`, unique), type (`trial`\|`paid`\|`provisional`\|`legacy`), **status**, starts_at, ends_at, grace_period_ends_at, trial_ends_at, **limits JSON** (copie du plan à l'activation), activation_source (`trial`\|`payment`\|`manual`\|`provisional`\|`api`\|`legacy`), activated_by | ∞→1 users/plans/orders/transactions |
| `webhook_events` (PK UUID) | provider, event_type, event_id (unique par provider), payload JSON, signature_header, signature_valid, processed, processed_at, order_id, transaction_id, error_message, retry_count | traçabilité webhooks Moneroo |
| `payment_audit_logs` | user_id, admin_id, action (`order_created`, `proof_submitted`, `payment_validated`, `license_activated`, `proof_viewed`…), entity_type, entity_id, old_values/new_values JSON, ip_address, user_agent, reason | journal d'audit immuable |
| `payment_method_configs` | type (`mobile_money`\|`bank_national`\|`bank_international`\|`transfer_service`), country, label, operator, account_number, account_holder, iban, swift_bic, bank_name, currency, instructions, min/max_amount, is_active, sort_order | moyens de paiement manuels affichés au checkout |

### 1.3 Diagramme relationnel simplifié

```
users ──1:N── licenses ──N:1── plans
  │               │
  │               └──N:1── payment_transactions ──N:1── orders ──N:1── plans
  │                              │
  │                              └──1:N── payment_proofs
  │
  ├──N:M── companies (company_user)
  └──1:N── companies (owner)
                │
                ├──1:N── customers ──1:N── documents (customer_id)
                ├──1:N── products
                └──1:N── documents ──1:N── document_lines
                              │            (N:1 products)
                              ├──1:N── document_payments
                              └──self── parent_id (conversions)
```

## 2. Cycle de vie d'un document

```
        create (draft)
             │
     [modifiable : update, delete]
             │
      finalize / seal  ◄── aussi déclenché automatiquement au 1er export PDF
             │
   integrity_hash = SHA-256(payload canonique)
   finalized_at = now()          status: draft → sent (sent_at)
             │
     [IMMUABLE : update et delete refusés par DocumentController]
             │
   registerPayment ──► partial (paiement partiel) ──► paid (total atteint)
```

1. **Création** (`DocumentService::create`) : transaction DB — numéro atomique (`DocumentNumberService::next`, `lockForUpdate` sur `document_sequences`), lignes synchronisées, totaux recalculés (remise globale répartie au prorata pour la TVA ligne à ligne), flag `trial_watermark` posé si l'utilisateur est en essai.
2. **Finalisation** (`DocumentService::finalize` → `DocumentIntegrityService::seal`) : calcul du **hash SHA-256** du **payload canonique** (société, type, numéro, dates, client, devise, montants, lignes — sérialisé en JSON `JSON_UNESCAPED_UNICODE|SLASHES`) stocké dans `integrity_hash`, horodatage `finalized_at`. `DocumentController::finalize` passe ensuite le statut `draft → sent`.
3. **Vérification publique** : le QR imprimé (généré par `QrCodeService`, PNG base64) encode `config('factpro.verify_base_url')/{uuid}`. La route publique `GET /verify/{uuid}` (`VerifyController`) recalcule le hash et compare avec `hash_equals` : document trouvé + finalisé + hash identique → **AUTHENTIQUE** ; contenu modifié depuis le scellement → **FALSIFIÉ**. La page révèle uniquement : émetteur, type, numéro, date, total, statut, horodatage de scellement, indicateur essai.
4. **Conversion** (`DocumentService::convert`) : réplique le document (nouveau uuid/numéro/statut `draft`, `parent_id` = source) selon la carte des cibles (devis → facture/proforma/BC/acompte ; proforma → facture ; BC → BL/facture ; BL → facture ; acompte → solde ; facture → avoir/reçu). Un devis converti passe en statut `converted`.
5. **Paiements** (`DocumentService::registerPayment`) : somme des encaissements → statut `partial` ou `paid`.

## 3. Cycle de vie d'une licence

Statuts (`License::STATUSES`) : `trial`, `pending`, `provisional`, `active`, `grace_period`, `suspended`, `expired`, `terminated`, `revoked`.

```
inscription ──► trial (7 jours, limites du plan PRO, filigrane sur les documents)
                  │
                  ├── paiement validé ──► active (type paid, ends_at = +N mois)
                  │        │
                  │        ├── renouvellement même plan ──► prolongation ends_at (+N mois)
                  │        └── fin de période ──► grace_period ──► suspended / expired
                  │
                  └── essai écoulé sans paiement ──► inutilisable
                       (middleware `license` → redirection /billing/plans,
                        données conservées)
```

- **Licence utilisable** (`License::isUsable`) : statut ∈ {trial, provisional, active, grace_period} **et** `effectiveEndsAt()` (grace_period_ends_at ?? ends_at) dans le futur.
- **Essai** (`LicenseService::startTrial`) : idempotent (une seule licence `trial` par utilisateur), plan PRO, durée `TRIAL_DURATION_DAYS` (7 j). Appelé à l'inscription (`RegisteredUserController`) et par le seeder démo.
- **Activation** (`LicenseService::activateFromOrder`) : **idempotente par transaction** — si une licence référence déjà `transaction_id`, elle est retournée telle quelle (une transaction ne peut jamais activer/prolonger deux fois). Sinon :
  - licence active de même plan non expirée → **prolongation** (`ends_at += N mois`, remise à `active`, purge du délai de grâce) ;
  - sinon la licence d'essai est terminée (`terminated`) et une nouvelle licence `paid`/`active` est créée (clé `FP-XXXX-XXXX-XXXX-XXXX`, copie des `limits` du plan), la commande passe à `paid`.
  - chaque étape est tracée dans `payment_audit_logs`.
- **Filigrane essai** (`LicenseService::needsTrialWatermark`) : appliqué si aucune licence ou licence en essai — mémorisé par document (`trial_watermark`), donc conservé même après souscription (le document scellé ne change pas).
- **Limites** (`LicenseService::limitReached`) : lecture du JSON `limits` de la licence (`null`/`unlimited` = illimité). Exemple appliqué : `documents_per_month` vérifié dans `DocumentController::store`.

## 4. Flux de paiement manuel (Mobile Money / virement)

```
1. /billing/plans        L'abonné choisit un plan + durée (1/3/6/12 mois)
2. POST /billing/subscribe
      PaymentService::createOrder → orders (status pending_payment, expires_at +72h)
      (anti double-clic : réutilise une commande identique encore payable)
3. /billing/checkout/{order}
      Récapitulatif + cartes des moyens de paiement actifs (payment_method_configs,
      filtrés par pays) + option Moneroo si clé configurée
4. L'abonné paie hors application (Orange Money, Wave, MTN, Moov, virement)
5. POST /billing/checkout/{order}/proof   (PaymentService::submitManualPayment)
      → payment_transactions (status under_review, internal_reference FP-YYYYMMDD-XXXXXX)
      → payment_proofs : fichier renommé aléatoirement, stocké sur le disque privé
        storage/app/private/proofs (jamais public), hash SHA-256 anti-réutilisation
      → order.status = proof_submitted
6. Console superadmin /admin/payments (middleware auth + superadmin)
      File d'attente triée par ancienneté + score de risque (PaymentService::riskLevel) :
        • référence opérateur déjà utilisée (+2)   • preuve au hash identique ailleurs (+2)
        • montant déclaré hors tolérance ±5 % (+1) • compte créé < 24 h (+1)
        → LOW / MEDIUM / HIGH / CRITICAL
      Consultation de la preuve via /admin/proofs/{proof} (flux privé, accès audité)
7a. Validation (montant reçu obligatoire)
      transaction → manually_validated, preuve → approved,
      LicenseService::activateFromOrder(…, adminId) → licence active
7b. Rejet (motif obligatoire)
      transaction → rejected, preuve → rejected, order → rejected
```

**Voie Moneroo (électronique)** : `POST /billing/checkout/{order}/moneroo` initialise le paiement et redirige vers le checkout Moneroo. **Le retour navigateur ne confirme jamais un paiement** : seule la réception du webhook signé HMAC (`POST /webhooks/moneroo`, sans session/CSRF, journalisé dans `webhook_events` avec déduplication par `event_id`) déclenche l'activation.

## 5. Rôle des services

| Service | Responsabilité |
|---|---|
| `DocumentService` | Orchestration documents : création/mise à jour transactionnelle, calcul des totaux (remises, TVA au prorata), conversion inter-types, finalisation, encaissements. |
| `DocumentNumberService` | Numérotation `{PREFIX}-{YYYY}-{NNNN}` atomique (`lockForUpdate` sur `document_sequences`), compteur par société/type/année. |
| `DocumentIntegrityService` | Anti-falsification : payload canonique, hash SHA-256, scellement (`seal`), vérification (`verify` avec `hash_equals`). |
| `QrCodeService` | QR PNG en data-URI (chillerlan/php-qrcode, ECC M) encodant l'URL de vérification — intégrable directement dans le PDF DomPDF. |
| `LicenseService` | Clés de licence, essai 7 j idempotent, licence courante, activation/prolongation idempotente par transaction, filigrane essai, contrôle des limites de plan. |
| `PaymentService` | Références internes uniques, création de commandes (72 h, anti double-clic), déclaration manuelle + stockage privé de preuve, validation/rejet admin (avec activation de licence), scoring anti-fraude. |
| `MonerooService` | Intégration API Moneroo (initialisation de paiement, vérification) — voie électronique optionnelle. |

## 6. Middleware et props Inertia partagées

### Middleware (alias déclarés dans `bootstrap/app.php`)

| Alias | Classe | Rôle |
|---|---|---|
| `license` | `EnsureLicenseActive` | Bloque les fonctionnalités métier (dashboard, clients, produits, documents) si aucune licence utilisable — sauf superadmin. Redirection vers `billing.plans` avec message « vos données sont conservées ». Les routes `/billing/*` et `/profile` restent accessibles pour permettre le paiement. |
| `superadmin` | `EnsureSuperadmin` | `abort 403` si `!user.is_superadmin` — protège le préfixe `/admin`. |
| — | `HandleInertiaRequests` | Partage global des props Inertia (voir ci-dessous). |

### Props Inertia partagées (`HandleInertiaRequests::share`)

```js
{
  auth: { user: { id, name, email, is_superadmin } },
  license: {            // null si aucune licence courante
    plan, plan_code, type, status,
    days_remaining, is_trial, is_usable, ends_at
  },
  company: { id, name, currency, country, logo_path, default_tax_rate }, // société courante
  flash: { success, error }
}
```

Ces props alimentent le bandeau licence (jours restants d'essai), l'entête société et les notifications flash de toutes les pages Vue.
