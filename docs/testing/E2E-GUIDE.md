# Guide des tests E2E Playwright — FactPro

## Vue d'ensemble

Ce guide couvre la mise en place et l'utilisation des tests End-to-End (E2E) avec [Playwright](https://playwright.dev/) pour FactPro.

Les tests E2E simulent un vrai navigateur et testent l'application de bout en bout, depuis l'interface utilisateur jusqu'à la base de données. Ils complètent les 519+ tests unitaires/intégration Pest existants.

---

## Installation

### Prérequis

- Node.js ≥ 20
- npm ≥ 9
- PHP 8.2 + Laravel 12
- XAMPP ou serveur local sur le port 8000

### Étapes d'installation

```bash
# 1. Installer Playwright et ses dépendances Node
npm install --save-dev @playwright/test

# 2. Installer les navigateurs (Chromium uniquement pour commencer)
npx playwright install chromium

# 3. Vérifier l'installation
npx playwright --version
```

---

## Lancement des tests

### Tous les tests (mode headless)

```bash
npx playwright test
```

### Mode UI interactif (recommandé pour le développement)

```bash
npx playwright test --ui
```

### Un seul fichier de spec

```bash
npx playwright test tests/e2e/auth.spec.js
```

### Un seul test par son nom

```bash
npx playwright test --grep "connexion avec email"
```

### Voir le rapport HTML après les tests

```bash
npx playwright show-report
```

### Mode debug (pas à pas dans le navigateur)

```bash
PWDEBUG=1 npx playwright test
```

### Avec traces complètes (utile pour déboguer un échec CI)

```bash
npx playwright test --trace on
```

---

## Structure des tests

```
tests/e2e/
├── helpers/
│   ├── auth.js          # Fonctions de connexion/déconnexion réutilisables
│   └── fixtures.js      # Données de test (clients, produits, factures)
├── auth.spec.js         # Tests d'authentification (login, register, logout)
├── document-lifecycle.spec.js  # Golden path : créer → finaliser → payer
├── pos.spec.js          # Caisse enregistreuse (POS)
├── customer-portal.spec.js     # Portail client public
└── admin.spec.js        # Console superadmin
```

### Configuration centrale

Le fichier `playwright.config.js` à la racine du projet définit :

- `baseURL` : `http://localhost:8000` (ou `$APP_URL` en CI)
- `testDir` : `./tests/e2e`
- `workers: 1` : séquentiel pour éviter les conflits de base de données
- `webServer` : démarre automatiquement `php artisan serve` si le serveur n'est pas lancé

---

## Comment écrire un nouveau test

### 1. Choisir le bon fichier spec

| Fonctionnalité | Fichier |
|---|---|
| Authentification | `auth.spec.js` |
| Factures, devis, paiements | `document-lifecycle.spec.js` |
| Caisse (POS) | `pos.spec.js` |
| Portail client public | `customer-portal.spec.js` |
| Console admin | `admin.spec.js` |
| Nouvelle fonctionnalité | Créer `ma-feature.spec.js` |

### 2. Structure d'un test

```js
import { test, expect } from '@playwright/test'
import { loginAsDemo } from './helpers/auth.js'

test.describe('Ma fonctionnalité', () => {
    // Exécuté avant chaque test du groupe
    test.beforeEach(async ({ page }) => {
        await loginAsDemo(page)
    })

    test('fait quelque chose', async ({ page }) => {
        // Navigation
        await page.goto('/ma-page')

        // Interactions
        await page.fill('[name="titre"]', 'Mon titre')
        await page.click('button:has-text("Sauvegarder")')

        // Assertions
        await expect(page).toHaveURL(/ma-page/)
        await expect(page.locator('text="Mon titre"')).toBeVisible()
    })
})
```

### 3. Utiliser les helpers

```js
import { loginAs, loginAsDemo, logout } from './helpers/auth.js'
import { testCustomer, uniqueEmail, uniqueName } from './helpers/fixtures.js'

// Connexion avec un compte spécifique
await loginAs(page, 'user@example.com', 'MonMotDePasse')

// Connexion avec le compte démo
await loginAsDemo(page)

// Déconnexion
await logout(page)

// Données uniques par run (évite les doublons)
const email = uniqueEmail('test')   // → test-1234567890@test.factpro
const name = uniqueName('Client')   // → Client 1234567890
```

### 4. Bonnes pratiques

**Sélecteurs robustes** (par ordre de préférence) :
1. `[data-testid="mon-bouton"]` — ajouter des attributs testid dans les composants Vue
2. `button:has-text("Sauvegarder")` — texte visible
3. `[name="email"]` — attributs de formulaire
4. `[role="dialog"] h2` — rôles ARIA
5. Éviter : `.flex.items-center.text-sm` — classes CSS instables

**Attendre les éléments** :
```js
// Attendre qu'un élément soit visible
await expect(page.locator('text="Succès"')).toBeVisible({ timeout: 10000 })

// Attendre une navigation
await page.waitForURL('**/dashboard')

// Attendre que le réseau soit calme
await page.waitForLoadState('networkidle')
```

**Tester les téléchargements** :
```js
const [download] = await Promise.all([
    page.waitForEvent('download'),
    page.click('a:has-text("Télécharger PDF")'),
])
expect(download.suggestedFilename()).toMatch(/\.pdf$/i)
```

---

## Variables d'environnement

| Variable | Description | Défaut |
|---|---|---|
| `APP_URL` | URL du serveur Laravel | `http://localhost:8000` |
| `CI` | Activer le mode CI (retries, no reuse server) | non défini |
| `DEMO_PORTAL_TOKEN` | Token portail client pour les tests portail | non défini |
| `PWDEBUG` | Activer le mode debug Playwright | non défini |

---

## Intégration CI/CD (GitHub Actions)

Le fichier `.github/workflows/e2e.yml` :

1. Lance MySQL dans un service Docker
2. Configure l'environnement Laravel
3. Exécute les migrations et les seeders (dont `DemoSeeder`)
4. Build les assets Vue/Vite
5. Installe Playwright et Chromium
6. Démarre `php artisan serve` en arrière-plan
7. Exécute `npx playwright test`
8. Upload le rapport HTML comme artifact GitHub Actions

### Déclencher manuellement

```bash
# Via GitHub CLI
gh workflow run e2e.yml
```

---

## Ajouter des attributs `data-testid` dans les composants Vue

Pour des sélecteurs stables, ajouter des attributs `data-testid` dans les composants Vue :

```vue
<!-- Avant -->
<button @click="save" class="btn btn-primary">Enregistrer</button>

<!-- Après -->
<button @click="save" class="btn btn-primary" data-testid="save-button">
    Enregistrer
</button>
```

---

## Dépannage

### Le serveur ne démarre pas

```bash
# Vérifier que le port 8000 est libre
netstat -an | grep 8000

# Démarrer manuellement
php artisan serve --port=8000
```

### Les tests échouent en CI mais pas en local

- Vérifier que `DemoSeeder` crée toutes les données nécessaires
- Augmenter les timeouts dans `playwright.config.js`
- Consulter les traces : `npx playwright show-report`

### Conflit de base de données entre tests

Les tests sont exécutés en séquentiel (`workers: 1`) pour éviter les conflits.
Chaque test qui crée des données doit utiliser `uniqueName()` / `uniqueEmail()` pour éviter les doublons.

### Debug d'un test spécifique

```bash
# Lancer un seul test en mode headed (avec navigateur visible)
npx playwright test auth.spec.js --headed

# Lancer en mode debug (pas à pas)
PWDEBUG=1 npx playwright test auth.spec.js --grep "connexion"
```

---

## Rapport de couverture

Playwright génère un rapport HTML dans `playwright-report/` :

```bash
npx playwright show-report
```

Le rapport affiche :
- Résultats par test (passé / échoué / ignoré)
- Captures d'écran des échecs
- Vidéos des tests échoués
- Traces réseau et DOM

---

## Références

- [Documentation Playwright](https://playwright.dev/docs/intro)
- [API Playwright Test](https://playwright.dev/docs/api/class-test)
- [Best practices Playwright](https://playwright.dev/docs/best-practices)
- [GitHub Actions + Playwright](https://playwright.dev/docs/ci-intro)
