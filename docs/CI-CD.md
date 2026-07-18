# CI/CD FactPro — Guide complet

## Vue d'ensemble

Le pipeline CI/CD FactPro repose sur **GitHub Actions** avec trois workflows :

| Workflow | Déclencheur | Rôle |
|----------|-------------|------|
| `ci.yml` | Push/PR sur main, develop, feature/** | Tests Pest + lint |
| `deploy.yml` | Push sur main ou déclenchement manuel | Déploiement LWS |
| `security.yml` | Chaque lundi à 6h UTC | Audit CVE dépendances |

---

## 1. Pipeline CI — Tests & Qualité

**Fichier :** `.github/workflows/ci.yml`

### Jobs

- **tests** : matrice PHP 8.2 / 8.3, MySQL 8.0 en service Docker, Pest avec couverture Xdebug, rapport Codecov
- **lint** : Laravel Pint (dry-run) + PHPStan

### Prérequis locaux

```bash
# Vérifier le lint avant commit
vendor/bin/pint --test

# Lancer les tests comme en CI
cp .env.ci .env
php artisan key:generate
php artisan migrate --force
php artisan test --parallel
```

---

## 2. Pipeline Déploiement LWS

**Fichier :** `.github/workflows/deploy.yml`

### Flux

1. Build production (`composer install --no-dev`, `npm run build`)
2. Création archive `deploy.tar.gz`
3. Upload sur le serveur via SCP
4. Exécution SSH :
   - Backup `.env` et `storage/app`
   - Mode maintenance (`php artisan down`)
   - Extraction archive
   - Migrations, caches, queue restart
   - Retour en ligne (`php artisan up`)
5. Health check HTTP sur `/health`

### GitHub Secrets à configurer

Aller dans **Settings → Secrets and variables → Actions** du dépôt GitHub et créer :

| Secret | Description |
|--------|-------------|
| `LWS_HOST` | IP ou hostname du serveur LWS |
| `LWS_USERNAME` | Nom d'utilisateur SSH LWS |
| `LWS_SSH_KEY` | Clé privée SSH (RSA ou ED25519, sans passphrase) |
| `LWS_PORT` | Port SSH (22 par défaut, laisser vide si standard) |
| `LWS_DOMAIN` | Domaine de l'application (ex. `app.factpro.ibigsoft.com`) |
| `DEPLOY_SECRET` | Token secret pour lever la maintenance (`artisan down --secret`) |

### Génération de la clé SSH pour le déploiement

```bash
# Côté local — générer une clé dédiée CI/CD
ssh-keygen -t ed25519 -C "github-actions-factpro" -f ~/.ssh/factpro_deploy -N ""

# Copier la clé publique sur le serveur LWS
ssh-copy-id -i ~/.ssh/factpro_deploy.pub user@lws-server

# Copier la clé PRIVÉE dans le secret GitHub LWS_SSH_KEY
cat ~/.ssh/factpro_deploy
```

---

## 3. Audit Sécurité Hebdomadaire

**Fichier :** `.github/workflows/security.yml`

Exécuté chaque **lundi à 6h UTC** (ou manuellement via `workflow_dispatch`) :

- `composer audit` — vérifie les CVE dans les packages PHP
- `npm audit --audit-level=high` — vérifie les CVE critiques JS

---

## 4. Health Check

### Endpoint HTTP

```
GET /health
```

Réponse :
```json
{
  "status": "ok",
  "app": "FactPro",
  "env": "production",
  "timestamp": "2026-07-18T10:00:00.000000Z",
  "php": "8.2.x",
  "laravel": "12.x.x"
}
```

### Script shell

```bash
bash scripts/health-check.sh https://app.factpro.ibigsoft.com
```

---

## 5. Variables CI (`.env.ci`)

Le fichier `.env.ci` est versionné (sans secrets sensibles) et utilisé par le pipeline CI :

- `APP_ENV=testing`
- `DB_CONNECTION=mysql` / base `factpro_test`
- `CACHE_DRIVER=array`, `QUEUE_CONNECTION=sync`
- `ANTHROPIC_API_KEY=test-key-ci` (valeur fictive, tests mockés)

---

## 6. Déclenchement manuel d'un déploiement

1. Aller sur **GitHub → Actions → Deploy — Production LWS**
2. Cliquer **Run workflow**
3. Choisir la branche `main` et l'environnement `production`
4. Suivre les logs en temps réel

---

## 7. Rollback

En cas de problème, se connecter en SSH et restaurer depuis le backup automatique :

```bash
# Lister les backups disponibles
ls ~/backups/

# Restaurer le .env
cp ~/backups/YYYYMMDD_HHMMSS/.env.bak ~/factpro/.env

# Restaurer les fichiers storage si nécessaire
cp -r ~/backups/YYYYMMDD_HHMMSS/app ~/factpro/storage/

# Redémarrer
cd ~/factpro && php artisan up
```
