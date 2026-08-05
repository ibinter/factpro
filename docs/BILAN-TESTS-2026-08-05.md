# Bilan — Remise à zéro des échecs de tests & correctifs de production

**Date :** 05/08/2026
**Résultat :** suite de tests **100 % verte — 969 passés · 1 skipped · 0 échec** (3948 assertions).
**Point de départ :** ~44 échecs préexistants.
**Périmètre :** correctifs déployés sur `factpro.ibigsoft.com` (commits `62c5ffaf` → `108499e5`), plusieurs vérifiés en conditions réelles au navigateur.

> Le seul test ignoré est le VAPID / EC P-256 (indisponible sur XAMPP Windows, opérationnel en prod Linux).

---

## 10 bugs de production corrigés

### 1. Conversion devis→facture cassée par un doublon de route ⚠️ critique
`POST /documents/{document}/convert` était déclarée **deux fois** sous le même nom `documents.convert` :

| Fichier | Contrôleur | État |
|---|---|---|
| `routes/web.php` | `DocumentController@convert` (via `DocumentService`) | ✅ correct, testé |
| `routes/feature_document_conversion.php` (requis **après**) | `DocumentConversionController@convert` | ❌ appelle `Document::generateNumber()` — méthode inexistante |

Le second fichier étant `require` après `web.php`, sa route **écrasait** la bonne → **HTTP 500 sur toute conversion en production**, alors même que le contrôleur testé était sain.

**Correctif :** suppression de la route `convert` dupliquée (on conserve `documents.chain`). L'UI utilise désormais `DocumentController@convert`.
**Régression :** `tests/Feature/DocumentConvertRouteTest.php` verrouille que `documents.convert` résout vers `DocumentController@convert`.
**Leçon :** `php artisan route:list` est la source de vérité. Deux routes de même nom → la dernière chargée gagne ; le contrôleur couvert par les tests unitaires peut ne jamais être appelé en réel. Seul un test bout-en-bout (ou navigateur) révèle ce masquage.

### 2. Collision `public_token` à la conversion
`DocumentService::convert()` faisait `replicate()` sans exclure `public_token` (unique). Le hook `creating` ne régénère le jeton que s'il est nul (`??=`) → collision `UNIQUE` garantie (chaque document a un `public_token`).
**Correctif :** exclusion de `public_token` + champs de signature (`signature_path`, `signed_by_name`, `signed_at`, `signature_ip`) — un brouillon converti ne doit pas hériter de la signature client du devis.

### 3. Redirections vers des noms de routes inexistants (×8 contrôleurs)
Après la réorganisation des fichiers de routes (préfixes de nom appliqués au `require`), plusieurs contrôleurs redirigeaient encore vers `route('X.index')` inexistant → `RouteNotFoundException` → **HTTP 500** sur : créer un coupon, mettre à jour une passerelle, enregistrer un deal CRM / un temps / un webhook, soumettre un template, révoquer une clé API.

| Contrôleur | Avant ❌ | Après ✅ |
|---|---|---|
| CrmController | `crm.index` | `crm.pipeline` |
| TimeEntryController | `time-entries.index` | `projects.index` |
| OutgoingWebhookController | `webhooks.index` | `outgoing-webhooks.index` |
| CouponAdminController | `admin.coupons.index` | `admin.coupons` |
| GatewayConfigController | `gateway-config.index` | `admin.gateways` |
| TemplateMarketplaceController | `marketplace.index` | `templates.marketplace.index` |
| ApiTokenController | `profile.index` | `profile.edit` |

### 4. Fuite de fonctionnalité BUSINESS+
`ApprovalController::ALLOWED_PLANS` incluait `starter` et `pro` → le circuit de validation multi-niveaux (fonctionnalité payante BUSINESS+) était accessible aux plans inférieurs.
**Correctif :** `['business', 'enterprise']`.

### 5. Streaming de signature client — route manquante
La route `documents.signature` (streaming du PNG de signature capturé au portail) n'existait pas → `RouteNotFoundException`.
**Correctif :** ajout de `GET /documents/{document}/signature-image` (`DocumentController@signature`), 404 (pas 403) pour société tierce ou document non signé.

### 6. FK `payslips.contract_id` vers la mauvaise table
`foreignId('contract_id')->constrained()` inférait la table `contracts` (module e-signature) au lieu de `employee_contracts` (où vit `App\Models\Contract`) → `FOREIGN KEY constraint failed` en test.
**Correctif :** `->constrained('employee_contracts')`. (La production pointait déjà correctement.)

---

## Tests périmés corrigés (données réelles vs hypothèses codées en dur)

- **BillingFlowTest** : le plan pro coûte **12 900 XOF/mois** (pas 10 000) ; annuel = 12 mois facturés 10 = **129 000**.
- **FraudDetectionTest** : `total_amount` supposé = 10 000 → fixé explicitement pour un écart déterministe.
- **CompanyTest** : clé template `corporate-01` (ancien schéma) → `corporate-classic` (registre actuel).
- **RegistrationFlowTest** : l'inscription redirige désormais vers `onboarding.welcome` (nouveau flux), plus vers `dashboard`.

---

## Pièges à retenir

- **`ManualPaymentMethod` mappe la table `payment_method_configs`** ; une migration y insère « Moov Money CI » (active). Ne pas supposer cette table vierge en test → raisonner en delta.
- **Message générique « Failed asserting that false is true »** = souvent un `assertRedirect()` / `assertSessionHas()` masquant un HTTP 500 en amont. Toujours remonter à l'exception réelle (log serveur ou `route:list`).
- **Les modèles de templates PDF** utilisent le nommage descriptif (`corporate-classic`, `finance-classic`…), pas `corporate-01`.
