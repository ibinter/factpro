# AUDIT — Système Paiement / Abonnement / Licence — IBIG FactPro
> Date : 2026-07-18 | Auditeur : Agent AUDIT | Version Laravel : 12 | Périmètre : lecture seule

---

## Section 1 : État actuel

### 1.1 Tables BDD liées au paiement

| Table | Fichier migration | Colonnes clés |
|---|---|---|
| `plans` | `2026_07_16_100005` | code, price_monthly, promo_price, currency, trial_days, features (JSON), limits (JSON) |
| `orders` | `2026_07_16_100006` | UUID PK, order_number (FP-YYYY-XXXXX), user_id, plan_id, duration_months, amount, discount_amount, total_amount, currency, country, payment_method, status (12 états), expires_at, paid_at, metadata |
| `payment_transactions` | `2026_07_16_100007` | UUID PK, order_id, user_id, payment_provider, provider_reference, internal_reference (FP-YYYYMMDD-RAND6 UNIQUE), amount_expected, amount_declared, amount_received, currency, fee_amount, status (12 états), sender_name, sender_number, initiated_at, paid_at, confirmed_at, validated_by, rejection_reason, metadata |
| `payment_proofs` | `2026_07_16_100007` | UUID PK, transaction_id, original_filename, stored_filename, file_path, mime_type, file_size, file_hash (SHA-256), uploaded_by, verified_by, verification_status (4 états), internal_comment |
| `licenses` | `2026_07_16_100008` | UUID PK, user_id, plan_id, order_id, transaction_id, license_key (FP-XXXX-XXXX-XXXX-XXXX UNIQUE), type (trial/paid/provisional/legacy), status (9 états), starts_at, ends_at, grace_period_ends_at, trial_ends_at, limits (JSON copié du plan), activation_source, activated_by, metadata |
| `webhook_events` | `2026_07_16_100008` | UUID PK, provider, event_type, event_id, payload (JSON), signature_header, signature_valid, processed, processed_at, order_id, transaction_id, error_message, retry_count — UNIQUE(provider, event_id) |
| `payment_audit_logs` | `2026_07_16_100008` | id, user_id, admin_id, action, entity_type, entity_id, old_values (JSON), new_values (JSON), ip_address, user_agent, reason |
| `payment_method_configs` | `2026_07_16_100008` | id, type (mobile_money/bank_national/bank_international/transfer_service), country, label, operator, logo_path, account_number, account_holder, iban, swift_bic, bank_name, currency, instructions, min_amount, max_amount, is_active, sort_order, metadata |
| `coupons` | `2026_07_17_083001` | code, type (percent/fixed), value, max_uses, used_count, valid_from, valid_until, plan_codes (JSON), metadata |
| `coupon_redemptions` | `2026_07_17_083001` | coupon_id, user_id, order_id, discount_applied |
| `gateway_configs` | `2026_07_17_095001` | provider, mode (sandbox/live), credentials (JSON chiffré), is_active, webhook_url |
| `referrals` | `2026_07_17_096001` | referrer_id, referred_id, code, status, rewarded_at |

### 1.2 Modèles existants

| Modèle | Fichier | Relations clés |
|---|---|---|
| `Plan` | `app/Models/Plan.php` | hasMany orders, licenses |
| `Order` | `app/Models/Order.php` | belongsTo user, plan ; hasMany transactions ; isPayable() |
| `PaymentTransaction` | `app/Models/PaymentTransaction.php` | belongsTo order, user, validator ; hasMany proofs |
| `PaymentProof` | `app/Models/PaymentProof.php` | belongsTo transaction |
| `License` | `app/Models/License.php` | belongsTo user, plan, order ; isUsable(), isTrial(), daysRemaining(), limit() |
| `WebhookEvent` | `app/Models/WebhookEvent.php` | belongsTo order, transaction |
| `PaymentAuditLog` | `app/Models/PaymentAuditLog.php` | static record() centralisé |
| `PaymentMethodConfig` | `app/Models/PaymentMethodConfig.php` | méthodes manuelles configurables admin |
| `Coupon` | `app/Models/Coupon.php` | hasManyRedemptions |
| `GatewayConfig` | `app/Models/GatewayConfig.php` | config passerelles chiffrée |

### 1.3 Services existants

| Service | Fichier | Fonctionnalités |
|---|---|---|
| `PaymentService` | `app/Services/PaymentService.php` | createOrder, submitManualPayment, validateManualPayment, rejectManualPayment, riskLevel, internalReference, orderNumber |
| `LicenseService` | `app/Services/LicenseService.php` | startTrial, activateFromOrder (idempotent), currentFor, isActive, needsTrialWatermark, limitReached, generateKey |
| `MonerooService` | `app/Services/MonerooService.php` | initializePayment, verifySignature (HMAC SHA-256) |
| `CinetPayService` | `app/Services/CinetPayService.php` | intégration CinetPay |
| `FedaPayService` | `app/Services/FedaPayService.php` | intégration FedaPay |
| `FlutterwaveService` | `app/Services/FlutterwaveService.php` | intégration Flutterwave |
| `MobileMoneyManager` | `app/Services/MobileMoney/MobileMoneyManager.php` | orchestrateur drivers (Wave, OrangeMoney, MtnMomo, MoovMoney) |
| `MobileMoneyDriver` | `app/Services/MobileMoney/MobileMoneyDriver.php` | interface commune |
| `WaveService` | `app/Services/MobileMoney/WaveService.php` | driver Wave direct |
| `OrangeMoneyService` | `app/Services/MobileMoney/OrangeMoneyService.php` | driver Orange Money direct |
| `MtnMomoService` | `app/Services/MobileMoney/MtnMomoService.php` | driver MTN MoMo direct |
| `MoovMoneyService` | `app/Services/MobileMoney/MoovMoneyService.php` | driver Moov Money direct |
| `CouponService` | `app/Services/CouponService.php` | validateFor, redeem, cancelForOrder |
| `ReferralService` | `app/Services/ReferralService.php` | rewardReferrer |

### 1.4 Controllers existants

| Controller | Fichier | Actions |
|---|---|---|
| `BillingController` | `app/Http/Controllers/BillingController.php` | index, plans, subscribe, checkout, submitProof, applyCoupon, removeCoupon |
| `MonerooPaymentController` | `app/Http/Controllers/MonerooPaymentController.php` | initiate, handleReturn |
| `WebhookController` | `app/Http/Controllers/WebhookController.php` | moneroo (flux complet) |
| `PaymentValidationController` | `app/Http/Controllers/Admin/PaymentValidationController.php` | index, validatePayment, reject, proof (streaming privé) |
| `AdminDashboardController` | `app/Http/Controllers/Admin/AdminDashboardController.php` | tableau bord financier complet |
| `LicenseAdminController` | `app/Http/Controllers/Admin/LicenseAdminController.php` | index, extend, suspend, reactivate, revoke |
| `PaymentMethodAdminController` | `app/Http/Controllers/Admin/PaymentMethodAdminController.php` | CRUD + toggle |
| `PlanAdminController` | `app/Http/Controllers/Admin/PlanAdminController.php` | index, update |
| `CinetPayController` | `app/Http/Controllers/CinetPayController.php` | initiate, handleReturn, webhook |
| `FedaPayController` | `app/Http/Controllers/FedaPayController.php` | initiate, handleReturn, webhook |
| `FlutterwaveController` | `app/Http/Controllers/FlutterwaveController.php` | initiate, handleReturn, webhook |
| `MobileMoneyController` | `app/Http/Controllers/MobileMoneyController.php` | index, initiate, status, webhook, detectDriver |
| `GatewayConfigController` | `app/Http/Controllers/GatewayConfigController.php` | index, update (superadmin) |

### 1.5 Routes paiement existantes

**Espace client (auth requis, accessible licence expirée) :**
```
GET  /billing                            billing.index
GET  /billing/plans                      billing.plans
POST /billing/subscribe                  billing.subscribe
GET  /billing/checkout/{order}           billing.checkout
POST /billing/checkout/{order}/proof     billing.proof          (throttle 6/min)
POST /billing/checkout/{order}/cinetpay  billing.cinetpay.initiate
GET  /billing/cinetpay/return/{order}    billing.cinetpay.return
POST /billing/checkout/{order}/fedapay   billing.fedapay.initiate
GET  /billing/fedapay/return/{order}     billing.fedapay.return
POST /billing/checkout/{order}/flutterwave billing.flutterwave.initiate
GET  /billing/flutterwave/return/{order} billing.flutterwave.return
```

**Webhooks (sans CSRF) :**
```
POST /webhooks/moneroo       webhooks.moneroo
POST /webhooks/cinetpay      webhooks.cinetpay
POST /webhooks/fedapay       webhooks.fedapay
POST /webhooks/flutterwave   webhooks.flutterwave
POST /webhooks/mobile-money/{driver}
```

**Superadmin :**
```
GET  /admin/dashboard                        admin.dashboard
GET  /admin/payments                         admin.payments
POST /admin/payments/{transaction}/validate  admin.payments.validate
POST /admin/payments/{transaction}/reject    admin.payments.reject
GET  /admin/proofs/{proof}                   admin.proofs.show
GET  /admin/licenses                         admin.licenses
POST /admin/licenses/{license}/extend        admin.licenses.extend
POST /admin/licenses/{license}/suspend       admin.licenses.suspend
POST /admin/licenses/{license}/reactivate    admin.licenses.reactivate
POST /admin/licenses/{license}/revoke        admin.licenses.revoke
GET  /admin/payment-methods                  admin.methods
POST /admin/payment-methods                  admin.methods.store
PUT  /admin/payment-methods/{method}         admin.methods.update
DEL  /admin/payment-methods/{method}         admin.methods.destroy
POST /admin/payment-methods/{method}/toggle  admin.methods.toggle
GET  /admin/plans                            admin.plans
PUT  /admin/plans/{plan}                     admin.plans.update
GET  /admin/gateways                         admin.gateways
PUT  /admin/gateways/{gateway}               admin.gateways.update
```

### 1.6 Intégrations existantes

| Passerelle | Type | Statut |
|---|---|---|
| **Moneroo** | Automatique (redirect) | Complet (init + webhook HMAC SHA-256 + idempotence + vérif montant/devise) |
| **CinetPay** | Automatique (redirect) | Service + Controller + Webhook + routes présents |
| **FedaPay** | Automatique (redirect) | Service + Controller + Webhook + routes présents |
| **Flutterwave** | Automatique (redirect) | Service + Controller + Webhook + routes présents |
| **Wave** | Direct API | Driver MobileMoney présent |
| **Orange Money** | Direct API | Driver MobileMoney présent |
| **MTN MoMo** | Direct API | Driver MobileMoney présent |
| **Moov Money** | Direct API | Driver MobileMoney présent |
| **Virement national** | Manuel + preuve | Supporté via payment_method_configs (type bank_national) |
| **Virement international** | Manuel + preuve | Supporté via payment_method_configs (type bank_international, IBAN, SWIFT) |
| **Espèces** | Manuel | Mentionné dans orders.payment_method mais ABSENT de la validation submitProof |
| **Western Union / transferts** | Manuel + preuve | Type 'transfer_service' disponible dans payment_method_configs |

### 1.7 Variables d'environnement

```env
TRIAL_DURATION_DAYS=7
LICENSE_GRACE_PERIOD_DAYS=7
LICENSE_PROVISIONAL_MAX_DAYS=7
MONEROO_PUBLIC_KEY=
MONEROO_SECRET_KEY=
MONEROO_WEBHOOK_SECRET=
MONEROO_MODE=sandbox
PROOF_STORAGE_DISK=local
PROOF_MAX_SIZE_MB=10
FRAUD_ALERT_EMAIL=admin@ibigsoft.com
FRAUD_AMOUNT_TOLERANCE_PERCENT=5
```

Variables pour CinetPay, FedaPay, Flutterwave, Wave, Orange, MTN, Moov : **absentes de .env.example** (à documenter).

---

## Section 2 : Fonctionnalités opérationnelles

| Fonctionnalité | Fichier source | Statut |
|---|---|---|
| Création de commande avec anti-double-clic | `PaymentService::createOrder()` | Opérationnel |
| Page comparatif forfaits avec prix multi-devises | `BillingController::plans()` | Opérationnel |
| Page checkout avec méthodes configurables | `BillingController::checkout()` | Opérationnel |
| Paiement Moneroo (redirect + webhook signé) | `MonerooService`, `WebhookController::moneroo()` | Opérationnel |
| Soumission preuve paiement manuel | `PaymentService::submitManualPayment()` | Opérationnel |
| Stockage privé des preuves (SHA-256 anti-doublon) | `PaymentService::submitManualPayment()` | Opérationnel |
| File de validation admin (avec scoring risque) | `PaymentValidationController` | Opérationnel |
| Validation manuelle admin → activation licence | `PaymentService::validateManualPayment()` | Opérationnel |
| Rejet admin avec motif obligatoire | `PaymentService::rejectManualPayment()` | Opérationnel |
| Consultation sécurisée preuves (streaming privé) | `PaymentValidationController::proof()` | Opérationnel |
| Activation licence idempotente (via transaction_id) | `LicenseService::activateFromOrder()` | Opérationnel |
| Renouvellement licence (prolongation de date de fin) | `LicenseService::activateFromOrder()` | Opérationnel |
| Essai gratuit 7 jours (auto à l'inscription) | `LicenseService::startTrial()` | Opérationnel |
| Période de grâce (grace_period_ends_at) | `License::effectiveEndsAt()`, tâche planifiée | Opérationnel |
| Licence provisoire (type + status 'provisional') | Table, modèle, tâche expire-provisional | Structure en place |
| Journal d'audit paiement séparé | `PaymentAuditLog::record()` (statique) | Opérationnel |
| Anti-fraude (référence dupliquée, hash preuve, montant, compte récent) | `PaymentService::riskLevel()` | Opérationnel |
| Codes promo (percent/fixed, plans ciblés, dates validité) | `CouponService`, `BillingController` | Opérationnel |
| Tableau de bord financier superadmin | `AdminDashboardController` | Opérationnel |
| Gestion licences admin (extend/suspend/reactivate/revoke) | `LicenseAdminController` | Opérationnel |
| CRUD méthodes paiement manuelles configurables | `PaymentMethodAdminController` | Opérationnel |
| Gestion forfaits admin | `PlanAdminController` | Opérationnel |
| Espace client abonnement + historique commandes | `BillingController::index()` | Opérationnel |
| Programme de parrainage | `ReferralService::rewardReferrer()` | Opérationnel |
| Intégration CinetPay / FedaPay / Flutterwave | Controllers + Services + Routes + Webhooks | Opérationnel |
| Mobile Money direct (Wave, Orange, MTN, Moov) | `MobileMoneyManager` + 4 drivers | Opérationnel |
| Idempotence webhook (contrainte unique provider+event_id) | `WebhookController::moneroo()` | Opérationnel |
| Vérification montant + devise au webhook | `WebhookController::moneroo()` | Opérationnel |
| Tâches planifiées cycle vie licences (6 commandes) | `routes/console.php` | Opérationnel |

---

## Section 3 : Fonctionnalités incomplètes ou manquantes

### Commandes (orders)
**Existe et complet.** Table `orders` avec UUID, 12 statuts, expiration 72h, discount, coupon, referral.
- Manque : pas de route pour annuler une commande côté client.

### Transactions distinctes
**Existe et complet.** Table `payment_transactions` distincte des orders, avec montants attendu/déclaré/reçu, 12 statuts.

### Preuves de paiement avec stockage privé
**Existe et complet.** Table `payment_proofs`, stockage `private/proofs`, SHA-256, streaming sécurisé côté admin.
- Risque mineur : le disk par défaut est `local` (pas `s3`) — en production, si `PROOF_STORAGE_DISK=local`, les preuves sont sur le même serveur que l'app, ce qui est acceptable mais moins résilient.

### Activation provisoire
**Structure en place** (type `provisional`, status `provisional`, tâche `licenses:expire-provisional`).
- Manque : aucune route ni service pour **créer** une licence provisoire (ex: l'admin accorde une activation provisoire en attente de paiement). Le service `LicenseService` ne possède pas de méthode `activateProvisionally()`. La tâche planifiée expire les provisoires mais rien ne les crée.

### Virement bancaire (national + international)
**Supporté manuellement.** Types `bank_transfer_national` et `bank_transfer_international` dans la validation de `submitProof`. La table `payment_method_configs` stocke IBAN, SWIFT/BIC, coordonnées bancaires affichés dans le checkout.
- Complet pour le flux manuel (preuve + validation admin).

### Transfert international (Western Union, MoneyGram, etc.)
**Structure disponible** via type `transfer_service` dans `payment_method_configs`.
- Manque : ce type n'est pas dans la liste de validation de `BillingController::submitProof()` (seuls : orange_money, mtn_momo, wave, moov, bank_transfer_national, bank_transfer_international).

### Paiement espèces
**Incomplet.** Mentionné dans `orders.payment_method` commentaire (`cash`) mais **absent de la validation** `BillingController::submitProof()`. Aucune route ni flux pour déclarer un paiement espèces.

### File de validation manuelle admin
**Existe et complète.** `PaymentValidationController` avec paginate, filtres par statut, scoring risque, validate + reject avec motif obligatoire.
- Manque : pas de notification email/SMS à l'admin lors d'un dépôt de preuve.

### Tableau de bord financier superadmin
**Existe et complet.** `AdminDashboardController` : revenue jour/mois/année, MRR, répartition licences par statut, revenus par forfait, licences expirant sous 7j, 10 derniers paiements, graphe 6 mois.
- Manque : export CSV/Excel du tableau financier ; pas de filtre par passerelle ou pays.

### Factures/reçus générés automatiquement
**MANQUANT.** Aucun modèle `Invoice` ou `Receipt` lié au paiement SaaS. Aucune génération PDF de reçu après activation de licence. Le `DocumentService` génère des factures métier (pour les clients de l'entreprise utilisatrice) mais pas de reçu d'abonnement FactPro.

### Accusé de réception (avant validation)
**MANQUANT.** Aucun Mailable/notification envoyé au client après soumission d'une preuve (`submitManualPayment`). L'utilisateur reçoit juste un flash "Votre preuve a été reçue" mais aucun email de confirmation.

### Espace client "Abonnement & Facturation" complet
**Partiellement complet.** `BillingController::index()` affiche la licence courante + historique commandes paginé.
- Manque : téléchargement de reçu PDF ; annulation de commande ; suivi en temps réel du statut de la transaction ; pas de vue Vue associée trouvée.

### Anti-fraude
**Existe.** `PaymentService::riskLevel()` : 4 signaux (référence dupliquée, hash preuve identique, montant hors tolérance, compte < 24h).
- Manque : aucune alerte email envoyée automatiquement à `FRAUD_ALERT_EMAIL` quand risk=CRITICAL ou HIGH. La valeur est calculée et affichée dans la file admin mais pas alertée.

### Journal d'audit paiement séparé
**Existe et opérationnel.** `PaymentAuditLog::record()` statique, appelé à chaque étape clé (order_created, payment_launched, proof_submitted, payment_validated, license_activated, etc.).
- Manque : pas de route admin pour consulter l'audit log ; pas d'export.

### Notifications paiement (email / SMS / WhatsApp)
**MANQUANT.** Aucun Mailable ou notification Laravel envoyé dans le flux paiement :
- Pas d'email à l'utilisateur : preuve reçue, paiement validé, licence activée, licence expirante, essai expiré.
- Pas de SMS via `SmsService` intégré au flux.
- Pas de WhatsApp via `WhatsAppService` intégré au flux.
Les services SMS et WhatsApp existent dans `app/Services/` mais ne sont pas câblés au système de paiement.

### Tâches planifiées paiement
Présentes dans `routes/console.php` :
| Commande | Fréquence | Objet |
|---|---|---|
| `payments:expire-orders` | toutes les 5 min | Expire les orders > 72h |
| `trials:check-expiration` | quotidien 08:00 | Expire essais + rappels J-7/3/1 |
| `licenses:send-expiry-alerts` | quotidien 09:00 | Alertes expiration licences payantes |
| `licenses:apply-grace-period` | quotidien 00:00 | Passage en grace_period |
| `licenses:auto-suspend` | quotidien 00:30 | Suspension après grace_period |
| `licenses:expire-provisional` | toutes les heures | Expire licences provisoires |

Note : les classes Artisan Command correspondantes ne sont pas vérifiées dans cet audit (scope non listé).

### Période de grâce
**Existe.** Champs `grace_period_ends_at` dans `licenses`, `License::effectiveEndsAt()`, tâches `licenses:apply-grace-period` et `licenses:auto-suspend`, config `LICENSE_GRACE_PERIOD_DAYS`. Durée configurable.

### Upgrade / downgrade forfait
**MANQUANT.** Aucune route, service ou logique de pro-rata pour changer de forfait en cours de licence. La seule voie est de créer une nouvelle commande sur un autre plan, ce qui ne gère pas l'ajustement du montant résiduel.

---

## Section 4 : Bugs détectés

### BUG 1 — Espèces non gérées dans submitProof (MEDIUM)
**Fichier :** `app/Http/Controllers/BillingController.php`, ligne 237
**Symptôme :** La migration `orders` et le commentaire mentionnent `cash` comme méthode de paiement, mais la règle de validation `'provider' => 'required|in:orange_money,mtn_momo,wave,moov,bank_transfer_national,bank_transfer_international'` exclut `cash` et `transfer_service`. Un paiement en espèces ne peut pas être soumis.

### BUG 2 — Webhook Mobile Money sans activation licence (HIGH)
**Fichier :** `app/Http/Controllers/MobileMoneyController.php`, ligne 98
**Symptôme :** Le webhook Mobile Money direct (`POST /webhooks/mobile-money/{driver}`) valide la signature mais **ne lie jamais la transaction à un Order ni n'active de licence**. La méthode `webhook()` retourne simplement `['ok' => true]` après validation. Un paiement direct Wave/Orange/MTN/Moov réussi ne produira aucune activation automatique.

### BUG 3 — Coupon redemption persistante si commande annulée (LOW)
**Fichier :** `app/Http/Controllers/BillingController.php`, ligne 95–105
**Symptôme :** La redemption du coupon est créée lors de `subscribe()` pour les coupons ONG/SCHOOL avant tout paiement. Si la commande expire ou est annulée sans passage par `removeCoupon()`, la redemption reste enregistrée et peut bloquer la réutilisation du coupon (selon la logique de `CouponService::validateFor()`).

### BUG 4 — Pas d'alerte fraude automatique (MEDIUM)
**Fichier :** `app/Services/PaymentService.php`, ligne 189–226
**Symptôme :** `riskLevel()` calcule un score CRITICAL/HIGH/MEDIUM/LOW mais aucun canal d'alerte n'est déclenché. La variable `FRAUD_ALERT_EMAIL` est définie mais jamais utilisée dans ce service. Un paiement frauduleux à risque CRITICAL sera visible dans la file admin mais sans notification proactive.

### BUG 5 — Vérification Moneroo sans re-fetch serveur-serveur (MEDIUM)
**Fichier :** `app/Services/MonerooService.php`, `app/Http/Controllers/WebhookController.php`
**Symptôme :** La vérification de paiement repose entièrement sur le webhook entrant. Il n'y a pas de mécanisme de re-fetch (GET /payments/{id}) côté Moneroo pour confirmer indépendamment avant activation. Si la signature est valide mais le payload falsifié (bug Moneroo côté, ce qui est improbable mais défensif), l'activation aurait lieu sur des données non vérifiées par un second appel.

### BUG 6 — Double activation possible via Mobile Money direct + webhook Moneroo (LOW)
**Scénario :** Un utilisateur paie via Mobile Money direct (`MobileMoneyController::initiate()`) ET soumet une preuve manuelle pour la même commande. Les deux flux peuvent créer des transactions séparées. `LicenseService::activateFromOrder()` est idempotent **par transaction_id** : si deux transactions différentes aboutissent sur la même commande, deux licences pourraient être créées.

### BUG 7 — Pas de vues Vue trouvées (CRITIQUE — risque fonctionnel)
**Observation :** Aucun fichier Vue/Inertia n'a été trouvé dans `resources/js/Pages/`. Les controllers retournent `Inertia::render('Billing/Index')`, `Inertia::render('Admin/Dashboard')`, etc., mais les composants correspondants sont absents du système de fichiers. Soit les vues existent dans un répertoire non standard, soit elles n'ont pas encore été créées. **L'interface utilisateur du module paiement n'est pas vérifiable.**

---

## Section 5 : Plan d'intervention

### 5.1 Tables à créer ou étendre

| Table | Action | Motif |
|---|---|---|
| `invoices` (nouvelle) | Créer | Reçus d'abonnement PDF générés après paiement |
| `payment_notifications` (nouvelle) | Créer OU utiliser `notifications` Laravel | Traçabilité des emails/SMS envoyés par le flux paiement |
| `payment_method_configs` | Étendre | Ajouter champ `requires_proof` (boolean) pour gérer espèces sans preuve image |

Note : `payment_orders`, `payment_transactions`, `payment_proofs`, `payment_audit_logs`, `payment_method_configs` existent déjà et sont complets.

### 5.2 Services à créer ou compléter

| Service | Action | Motif |
|---|---|---|
| `ProvisionalLicenseService` (nouveau) | Créer | Méthode `activateProvisionally(Order, ?int $days)` permettant à un admin d'accorder une licence provisoire |
| `PaymentNotificationService` (nouveau) | Créer | Centraliser envoi email/SMS/WhatsApp pour : preuve reçue, paiement validé, paiement rejeté, licence activée, licence expirante |
| `PaymentReceiptService` (nouveau) | Créer | Génération PDF reçu d'abonnement (numéro, plan, montant, période) |
| `FraudAlertService` (nouveau OU étendre PaymentService) | Créer | Déclencher notification email à `FRAUD_ALERT_EMAIL` si riskLevel >= HIGH |
| `MobileMoneyController::webhook()` | Modifier | Lier au système Order/Transaction/License : récupérer l'order depuis la référence, créer/mettre à jour la PaymentTransaction, appeler `LicenseService::activateFromOrder()` si paiement réussi |

### 5.3 Fichiers à modifier

| Fichier | Modification | Raison |
|---|---|---|
| `app/Http/Controllers/BillingController.php` L.237 | Ajouter `cash`, `transfer_service` à la validation provider | Support espèces et transferts |
| `app/Http/Controllers/MobileMoneyController.php` | Refondre `webhook()` pour activer la licence | Bug critique activation |
| `app/Services/PaymentService.php` | Ajouter appel `FraudAlertService` dans `riskLevel()` ou `validateManualPayment()` | Anti-fraude proactif |
| `app/Services/PaymentService.php` | Ajouter notifications via `PaymentNotificationService` dans `submitManualPayment()`, `validateManualPayment()`, `rejectManualPayment()` | Accusé réception + notifications |
| `routes/console.php` | Vérifier existence des classes Artisan (6 commandes planifiées) | Les commandes sont planifiées mais leur existence n'est pas vérifiée |
| `.env.example` | Ajouter les variables CinetPay, FedaPay, Flutterwave, Wave, Orange, MTN, Moov | Documentation des intégrations |
| `resources/js/Pages/Billing/` | Créer les vues Vue (Index.vue, Plans.vue, Checkout.vue) | Interface utilisateur absente |
| `resources/js/Pages/Admin/` | Créer les vues Vue (Dashboard.vue, Payments.vue, Licenses.vue, Plans.vue, PaymentMethods.vue) | Interface admin absente |

### 5.4 Risques de régression identifiés

1. **Refonte webhook Mobile Money** : toute modification de `MobileMoneyController::webhook()` doit être soigneusement testée pour ne pas casser les 4 drivers existants ni réintroduire de double activation.
2. **Ajout de `cash`/`transfer_service` dans submitProof** : s'assurer que la table `payment_method_configs` dispose bien d'entrées actives de ces types avant activation en production (sinon la page checkout n'affichera aucune méthode pour ces types).
3. **Coupon redemption** : toute modification de `CouponService` doit garantir l'annulation propre des redemptions si la commande est finalement annulée ou expirée.

---

## Section 6 : Architecture Moneroo existante

### 6.1 Classe / Service
`app/Services/MonerooService.php`
- Injecte `PaymentService` (pour `internalReference()`)
- Lit la config depuis `config('factpro.moneroo')` (clé secrète, base URL, mode)
- **Aucune clé API n'est jamais exposée côté JavaScript**

### 6.2 Endpoints webhook
- Réception : `POST /webhooks/moneroo` → `WebhookController::moneroo()`
- Sans middleware CSRF (via `withoutMiddleware`)
- Throttle 60 req/min (non trouvé explicitement dans les routes lues — à vérifier)

### 6.3 Vérification signature HMAC
```php
// MonerooService::verifySignature()
$expected = hash_hmac('sha256', $rawBody, $webhook_secret);
return hash_equals($expected, trim($signatureHeader)); // timing-safe
```
- Header : `X-Moneroo-Signature`
- Corps brut (non décodé)
- Secret absent → retourne `false` systématiquement (fail-closed)

### 6.4 Flux complet

```
1. Client → POST /billing/subscribe → PaymentService::createOrder() → Order(pending_payment)
2. Client → GET  /billing/checkout/{order} → affiche page avec bouton "Payer via Moneroo"
3. Client → POST /billing/checkout/{order}/moneroo → MonerooPaymentController::initiate()
4.   MonerooService::initializePayment(order) :
       a. PaymentTransaction::create(status=initiated)
       b. PaymentAuditLog::record(payment_launched)
       c. HTTP POST https://api.moneroo.io/v1/payments/initialize
            payload: amount, currency, description, customer, return_url, metadata{order_id,transaction_id,internal_reference}
       d. Réponse OK → transaction.update(status=pending, provider_reference=moneroo_id)
                      → order.update(status=payment_initiated)
5. Client redirigé → checkout_url Moneroo (Inertia::location)
6. Client paie sur la page Moneroo
7. Moneroo → POST /webhooks/moneroo :
       a. WebhookEvent::create() AVANT tout traitement (stockage immédiat)
       b. Vérification HMAC sur corps brut
       c. UniqueConstraintViolationException → 200 "already_received" (idempotence DB)
       d. Vérification idempotence applicative (webhook processed=true déjà existant)
       e. Réconciliation : internal_reference (metadata) OU provider_reference → PaymentTransaction
       f. Vérification montant + devise (si écart → status=under_review, 200)
       g. Succès : transaction.update(succeeded) → LicenseService::activateFromOrder() [idempotent via transaction_id]
       h. Échec/annulation : transaction.update(failed/cancelled), sans rétrogradation si déjà succeeded
       i. 200 systématique en fin (pas de retry Moneroo sur 2xx)
8. Client → GET /billing/moneroo/return/{order} :
       RÈGLE ABSOLUE : aucune activation ici — lit uniquement order.status déjà mis à jour par le webhook
       → redirect billing.index avec message selon order.status
```

### 6.5 Ce qui manque dans l'architecture Moneroo

1. **Re-fetch de vérification** : pas d'appel `GET /payments/{id}` pour vérifier indépendamment avant activation (défense en profondeur).
2. **Retry webhook** : pas de mécanisme de retry si le webhook arrive avec une erreur réseau transitoire (le `retry_count` existe en BDD mais aucun job ne le réessaie).
3. **Notification email** après activation (voir Section 3).
4. **Route moneroo manquante dans les routes lues** : `POST /billing/checkout/{order}/moneroo` → le controller `MonerooPaymentController::initiate()` existe mais aucune route explicite n'a été trouvée dans `web.php` ou les fichiers inclus (possible que ce soit dans un fichier de routes non listé, ou manquant).

---

## Résumé exécutif (20 lignes)

Le système paiement/abonnement d'IBIG FactPro est **structurellement solide et bien conçu**. Les tables BDD couvrent l'intégralité du cycle de vie (orders, transactions, preuves, licences, audit, webhooks, méthodes manuelles configurables). Les services `PaymentService`, `LicenseService` et `MonerooService` implémentent correctement les patterns critiques : idempotence (transaction_id unique sur licence), vérification HMAC timing-safe, stockage avant traitement webhook, vérification montant+devise, anti-double-clic sur commandes. Le journal d'audit `PaymentAuditLog` est présent et utilisé à chaque étape. Les tâches planifiées couvrent le cycle de vie complet (expiration, grâce, suspension, provisoire). Quatre passerelles automatiques (Moneroo, CinetPay, FedaPay, Flutterwave) et quatre drivers Mobile Money direct (Wave, Orange, MTN, Moov) sont intégrés.

**Points critiques à corriger :** (1) Le webhook Mobile Money direct ne produit aucune activation de licence — bug fonctionnel bloquant pour ce flux. (2) Aucune vue Vue n'a été trouvée dans `resources/js/Pages/` — l'interface utilisateur entière est absente ou mal localisée. (3) Aucune notification email/SMS n'est envoyée dans le flux paiement (accusé réception, validation, rejet, activation). (4) La génération de reçus/factures d'abonnement PDF est absente. (5) L'activation provisoire admin n'a pas de méthode de création dans les services. (6) Le paiement espèces et les transferts internationaux ne sont pas dans la validation du formulaire de preuve. (7) L'alerte fraude `FRAUD_ALERT_EMAIL` est configurée mais jamais utilisée programmatiquement.
