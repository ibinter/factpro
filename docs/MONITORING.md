# Monitoring — IBIG FactPro (Phase 17)

## 1. Sentry — Suivi des erreurs applicatives

### Installation

Le package `sentry/sentry-laravel` est installé via Composer. La configuration est dans `config/sentry.php`.

### Configuration

Dans `.env`, renseigner votre DSN Sentry :

```
SENTRY_LARAVEL_DSN=https://<key>@<org>.ingest.sentry.io/<project-id>
```

Le DSN est disponible dans **Sentry > Settings > Projects > [votre projet] > Client Keys**.

### Intégration Laravel 12

La capture automatique des exceptions est activée dans `bootstrap/app.php` :

```php
->withExceptions(function (Exceptions $exceptions) {
    \Sentry\Laravel\Integration::handles($exceptions);
})
```

### Capture manuelle

Utiliser `MonitoringService` pour capturer des erreurs ou messages :

```php
$monitoring->captureException($e, ['order_id' => 42]);
$monitoring->captureMessage('Paiement échoué', 'warning', ['amount' => 5000]);
```

---

## 2. UptimeRobot — Supervision disponibilité

### 5 monitors à créer

| Nom | URL | Type | Intervalle |
|-----|-----|------|-----------|
| FactPro — App | `APP_URL/` | HTTP | 5 min |
| FactPro — Health | `APP_URL/health` | HTTP | 5 min |
| FactPro — API | `APP_URL/api/openapi.json` | HTTP | 15 min |
| FactPro — Login | `APP_URL/login` | HTTP | 10 min |
| FactPro — Tarifs | `APP_URL/pricing` | HTTP | 30 min |

### Récupérer la configuration JSON

```
GET /health/uptimerobot   (authentification superadmin requise)
```

Réponse prête à importer dans UptimeRobot.

### Alertes

- **Email** : ops@ibigsoft.com
- **SMS** : configurer dans UptimeRobot Settings > Alert Contacts

---

## 3. Endpoints de monitoring

| Route | Méthode | Auth | Description |
|-------|---------|------|-------------|
| `/health` | GET | Publique | Statut simplifié — pour load balancer et UptimeRobot |
| `/health/detailed` | GET | Auth | Statut complet avec détail des checks |
| `/health/uptimerobot` | GET | Superadmin | Configuration JSON UptimeRobot |

### Réponse `/health`

```json
{
  "status": "ok",
  "timestamp": "2026-07-18T06:00:00.000000Z"
}
```

### Réponse `/health/detailed`

```json
{
  "status": "healthy",
  "checks": {
    "database": { "status": "ok", "companies": 42 },
    "cache":    { "status": "ok" },
    "storage":  { "status": "ok" },
    "queue":    { "status": "ok", "pending": 0, "failed": 0 },
    "mail":     { "status": "ok", "driver": "smtp" }
  },
  "timestamp": "2026-07-18T06:00:00.000000Z",
  "version": "1.0.0",
  "environment": "production"
}
```

---

## 4. Métriques importantes à surveiller

| Métrique | Seuil alerte | Outil |
|----------|-------------|-------|
| Temps de réponse moyen | > 2 s | UptimeRobot / Sentry |
| Taux d'erreur 5xx | > 1 % | Sentry |
| Jobs en attente (`jobs` table) | > 100 | Health check |
| Jobs échoués (`failed_jobs`) | > 0 | Health check |
| Utilisation disque | > 85 % | Cron serveur |
| Disponibilité | < 99,9 % | UptimeRobot |

### Commande artisan

```bash
php artisan app:health-check
php artisan app:health-check --alert   # envoie une alerte Sentry si dégradé
```

Le scheduler exécute `app:health-check --alert` tous les jours à 06h00.

---

## 5. Runbook — Que faire si l'application est down ?

### Étape 1 — Identifier la cause

```bash
php artisan app:health-check
```

### Étape 2 — Base de données inaccessible

1. Vérifier que MySQL/MariaDB est démarré : `service mysql status`
2. Vérifier les credentials dans `.env` (`DB_HOST`, `DB_USERNAME`, `DB_PASSWORD`)
3. Tester la connexion : `php artisan db:show`

### Étape 3 — Cache défaillant

1. Vider le cache : `php artisan cache:clear`
2. Vérifier la configuration Redis/Memcache si utilisé

### Étape 4 — Stockage inaccessible

1. Vérifier les permissions : `chmod -R 775 storage bootstrap/cache`
2. Relink si nécessaire : `php artisan storage:link`

### Étape 5 — Queue bloquée

1. Redémarrer le worker : `php artisan queue:restart`
2. Vérifier les jobs échoués : `php artisan queue:failed`
3. Rejouer les jobs : `php artisan queue:retry all`

### Étape 6 — Rollback d'urgence

```bash
git log --oneline -10
git checkout <commit-précédent>
php artisan migrate:rollback
php artisan config:clear && php artisan cache:clear
```

### Contacts d'urgence

- Ops : ops@ibigsoft.com
- Sentry : https://sentry.io (dashboard temps réel)
- UptimeRobot : https://uptimerobot.com (historique disponibilité)
