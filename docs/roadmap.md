# Roadmap — IBIG FactPro

Feuille de route en 4 phases, reprise du cahier des charges IBIG. Chaque item comporte une note d'implémentation indiquant les points d'extension déjà présents dans le code.

## Phase 1 — MVP (✔ réalisé)

| Item | Statut | Note d'implémentation |
|---|---|---|
| Authentification (inscription, connexion, profil) | ✔ | Laravel Breeze + Inertia ; l'inscription crée l'utilisateur, sa société et démarre l'essai 7 jours (`RegisteredUserController`). |
| Devis, factures et documents commerciaux — 12 types | ✔ | `Document::TYPES` (quote, proforma, sales_order, purchase_order, delivery_note, invoice, credit_note, payment_receipt, deposit_invoice, balance_invoice, work_order, pos_ticket) ; conversion inter-types (`DocumentService::convert`) ; numérotation atomique par société/type/année. La migration prévoit déjà les 16 types du cahier (`discharge`, `rma`, `recurring_invoice`, `remittance_slip` en commentaire) — il suffit d'ajouter les entrées dans `Document::TYPES`. |
| QR anti-falsification | ✔ | Hash SHA-256 canonique (`DocumentIntegrityService`), QR data-URI (`QrCodeService`), page publique `/verify/{uuid}`. |
| Export PDF | ✔ | DomPDF A4 (`resources/views/pdf/document.blade.php`) avec QR et filigrane essai ; scellement automatique au 1er export. |
| Clients & produits/services | ✔ | CRUD complet, SKU/code-barres, stock basique (`track_stock`, seuil d'alerte). |
| Tableau de bord | ✔ | CA mensuel, encours, graphique 6 mois. |
| Module licences & paiements manuels | ✔ | 4 plans FCFA (Starter 2 500 / Pro 10 000 / Business 15 000 / Enterprise 25 000), essai 7 j, commandes 72 h, preuve privée, console superadmin avec scoring anti-fraude, audit log, activation idempotente. |
| Paiement électronique Moneroo (fondations) | ✔ (sandbox) | `MonerooService`, `MonerooPaymentController`, webhook signé (`routes/webhooks.php`), table `webhook_events` avec déduplication `event_id` — activation en production = renseigner `MONEROO_*` et passer `MONEROO_MODE=live`. |

## Phase 2 — Commerce physique & productivité

| Item | Note d'implémentation |
|---|---|
| Impression thermique ESC/POS 58/80 mm | Le type `pos_ticket` (TK) existe déjà ; ajouter un rendu dédié (vue Blade étroite ou flux ESC/POS via une librairie type mike42/escpos-php) à côté du PDF A4. Le plan BUSINESS l'annonce déjà dans `features`. |
| Gestion des stocks avancée (mouvements, inventaires, valorisation) | Colonnes `stock_quantity`, `stock_alert_threshold`, `track_stock`, `cost` déjà en place sur `products` ; ajouter une table `stock_movements` reliée aux `document_lines` (décrément à la finalisation BL/facture). |
| POS caisse (interface vente rapide) | S'appuyer sur `pos_ticket` + `document_payments` (méthode `cash`/`mobile_money`) ; rôle `cashier` déjà prévu dans le pivot `company_user`. |
| Relances automatiques (email) | Statut `overdue` déjà géré ; ajouter une commande planifiée (cron `schedule:run` déjà documenté pour LWS) qui détecte les factures échues et envoie les relances (limite « 3/mois » du plan PRO à contrôler via `limits`). |
| 60 modèles de documents (templates) | Champs `companies.default_template` et `documents.template_key` déjà présents ; la limite `templates` existe dans le JSON `limits` de chaque plan (5/30/100/illimité). Il reste à créer la bibliothèque de vues PDF. |

## Phase 3 — Ouverture & écosystème

| Item | Note d'implémentation |
|---|---|
| API REST publique | Laravel Sanctum déjà installé (`composer.json`) ; quotas « 1000/h » (BUSINESS) et « illimité » (ENTERPRISE) à brancher sur le rate limiter Laravel + `limits` JSON des plans. |
| Portail client (consultation/acceptation des devis) | L'`uuid` public des documents et la page `/verify` fournissent la base ; ajouter des statuts déjà prévus (`viewed`, `accepted`, `rejected`) pilotés par le client final. |
| PWA / mode hors-ligne | Frontend Vite + Vue 3 : ajouter un service worker (vite-plugin-pwa) ; l'API Phase 3 servira de couche de synchronisation. |
| White-label revendeur | Prévu dans `features` du plan ENTERPRISE ; `companies.logo_path`, `settings` JSON et `metadata` des plans servent de points d'ancrage (thème, domaine, marque). |
| OCR (numérisation de factures fournisseurs) | Le type `purchase_order` (BCF) existe ; stocker les fichiers numérisés sur le disque privé comme les preuves de paiement (`PROOF_STORAGE_DISK`). |
| Multi-devises complet (160+) | `config/factpro.php` liste déjà 12 devises et des taux de repli (`exchange_rates_xof`) ; `documents.exchange_rate` est en base — reste l'API de change et l'UI. |

## Phase 4 — Intelligence & plateforme

| Item | Note d'implémentation |
|---|---|
| IA (assistance à la saisie, prédiction d'impayés, catégorisation) | S'appuyer sur l'historique `documents` / `document_payments` (statuts `overdue`, délais réels de paiement) comme jeu de données. |
| Comptabilité complète (grand livre, FEC, exports) | « Comptabilité simplifiée + FEC » déjà annoncée au plan BUSINESS ; les écritures peuvent être dérivées de `documents` + `document_payments` (montants HT/TVA/TTC déjà décomposés). |
| Marketplace (modules, intégrations tierces) | La table `webhook_events` (multi-provider) et `payment_method_configs` (moyens de paiement configurables sans code, avec `metadata` JSON) montrent le patron à généraliser : configuration en base + `metadata` extensible. |
| Signature électronique certifiée | Le scellement SHA-256 + horodatage (`integrity_hash`, `finalized_at`) constitue le socle ; ajouter un fournisseur de certification qualifiée (plans PRO « signature électronique » / ENTERPRISE « signature certifiée »). |

## Points d'extension transverses déjà en place

- **`plans.limits` / `licenses.limits` (JSON)** : toute nouvelle limite commerciale (SMS/mois, requêtes API/h, Go de stockage…) s'ajoute sans migration — `LicenseService::limitReached()` la contrôle immédiatement.
- **`payment_method_configs`** : ajout d'un nouveau moyen de paiement (nouvel opérateur, nouveau pays) = simple enregistrement en base, affiché automatiquement au checkout.
- **`webhook_events`** : générique multi-fournisseurs (colonne `provider`, déduplication `event_id`, retry_count) — prêt pour d'autres agrégateurs que Moneroo.
- **`payment_audit_logs`** : journalisation généralisable à toute action sensible via `PaymentAuditLog::record()`.
- **`licenses.type = provisional` + `LICENSE_PROVISIONAL_MAX_DAYS`** : activation provisoire (avant validation définitive d'un paiement) déjà modélisée dans les statuts et la config.
- **`documents.template_key`, `companies.settings`, `metadata` JSON partout** : personnalisation sans migration.
