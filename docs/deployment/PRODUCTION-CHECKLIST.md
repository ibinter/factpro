# Checklist de mise en production — IBIG FactPro

> Cocher chaque point avant de mettre l'application en ligne.  
> Une seule case non cochée peut entraîner une faille de sécurité ou un dysfonctionnement.

---

## Configuration de l'environnement

- [ ] `APP_ENV=production` dans `.env`
- [ ] `APP_DEBUG=false` dans `.env` (ne jamais laisser `true` en prod)
- [ ] `APP_KEY` généré (`php artisan key:generate`)
- [ ] `APP_URL` correct avec `https://` (ex: `https://votre-domaine.com`)
- [ ] `APP_TIMEZONE` configuré (ex: `Africa/Abidjan`, `Africa/Dakar`, `Europe/Paris`)
- [ ] `LOG_LEVEL=error` (éviter de loguer des données sensibles)

## Base de données

- [ ] `DB_CONNECTION=mysql` (ou `pgsql` selon le serveur)
- [ ] `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` corrects
- [ ] Connexion testée : `php artisan db:show`
- [ ] Migrations exécutées : `php artisan migrate --force`
- [ ] Seeders de base lancés : `php artisan db:seed --class=PlansSeeder`
- [ ] Compte super-admin créé
- [ ] Compte démo créé (optionnel, pour la démonstration)

## Cache, Queues et Sessions

- [ ] `QUEUE_CONNECTION=redis` (VPS) ou `database` (mutualisé) — ne jamais laisser `sync` en prod multi-utilisateurs (emails en attente)
- [ ] `CACHE_STORE=redis` (VPS) ou `file` (mutualisé)
- [ ] `SESSION_DRIVER=database` ou `redis`
- [ ] `SESSION_LIFETIME=480` (8 heures, adapté à un usage professionnel)
- [ ] `BROADCAST_CONNECTION=log` (sauf si Reverb/Pusher configuré)

## Fichiers et permissions

- [ ] Fichier `.env` hors du dossier `public/` et non accessible via navigateur
- [ ] `chmod 600 .env`
- [ ] `storage/` writable : `chmod -R 775 storage/`
- [ ] `bootstrap/cache/` writable : `chmod -R 775 bootstrap/cache/`
- [ ] Lien symbolique storage : `php artisan storage:link`

## Assets et cache applicatif

- [ ] Assets compilés : `npm run build` (dossier `public/build/` présent)
- [ ] Config cachée : `php artisan config:cache`
- [ ] Routes cachées : `php artisan route:cache`
- [ ] Vues cachées : `php artisan view:cache`
- [ ] (ou tout en une commande : `php artisan optimize`)

## Email (MAIL_*)

- [ ] `MAIL_MAILER=smtp` (pas `log` en production)
- [ ] `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` configurés
- [ ] `MAIL_FROM_ADDRESS` et `MAIL_FROM_NAME` corrects
- [ ] Test d'envoi réel effectué : `php artisan tinker` → `Mail::to('test@email.com')->send(...)`

## SSL et HTTPS

- [ ] Certificat SSL valide (Let's Encrypt ou commercial)
- [ ] Redirection HTTP → HTTPS active
- [ ] `APP_URL` utilise `https://`
- [ ] En-têtes de sécurité HTTP configurés (X-Frame-Options, X-Content-Type-Options, etc.)

## Queue Worker

- [ ] Queue worker en cours d'exécution (Supervisor sur VPS)
- [ ] `php artisan queue:restart` effectué après chaque déploiement
- [ ] Logs des workers accessibles : `storage/logs/worker.log`
- [ ] Sur mutualisé : `QUEUE_CONNECTION=sync` accepté et compris (pas de worker nécessaire)

## Scheduler (cron)

- [ ] Tâche cron configurée : `* * * * * php /chemin/artisan schedule:run`
- [ ] Tâche testée manuellement : `php artisan schedule:run`
- [ ] `php artisan schedule:list` affiche les tâches attendues

## Sécurité

- [ ] `APP_DEBUG=false` (vérifié une 2ème fois — critique)
- [ ] `.env` non accessible via URL : tester `https://votre-domaine.com/.env` → doit retourner 403/404
- [ ] `MONEROO_WEBHOOK_SECRET` (ou équivalent) généré et fort (32+ caractères aléatoires)
- [ ] `FRAUD_ALERT_EMAIL` configuré avec une vraie adresse surveillée
- [ ] 2FA activé sur le compte super-admin (si disponible dans l'app)
- [ ] Mot de passe BDD fort (12+ caractères, mixte)
- [ ] Utilisateur BDD avec droits minimaux (pas `root`)
- [ ] Sur VPS : UFW activé, seuls les ports 22/80/443 ouverts

## Passerelles de paiement

- [ ] `MONEROO_MODE=live` (et non `sandbox`) si activé
- [ ] `MONEROO_PUBLIC_KEY` et `MONEROO_SECRET_KEY` en mode production
- [ ] `MONEROO_WEBHOOK_SECRET` configuré côté LWS/serveur ET côté Moneroo dashboard
- [ ] Autres passerelles (CinetPay, FedaPay, Flutterwave) : clés live renseignées
- [ ] URLs de webhook enregistrées dans les dashboards des passerelles
- [ ] Test de paiement en sandbox effectué avant passage en live

## Backup

- [ ] Script de backup configuré : `scripts/backup.sh`
- [ ] Cron de backup quotidien configuré (02h00 recommandé)
- [ ] Rétention des backups vérifiée (30 jours recommandé)
- [ ] Test de restauration effectué au moins une fois
- [ ] Backups stockés hors du serveur de production (S3, SFTP distant, etc.)

---

## Tests post-déploiement

> Effectuer ces tests manuellement depuis un navigateur en navigation privée.

### Fonctionnalités de base

- [ ] Création d'un nouveau compte (inscription)
- [ ] Connexion avec un compte existant
- [ ] Création d'un client
- [ ] Création d'une facture
- [ ] Téléchargement PDF d'une facture
- [ ] Envoi de facture par email
- [ ] Accès au lien de vérification QR code (`/verify/{uuid}`)

### Paiement

- [ ] Paiement en mode sandbox (CinetPay / Moneroo / FedaPay selon pays)
- [ ] Réception du webhook de confirmation
- [ ] Statut de la facture mis à jour après paiement

### Email

- [ ] Email de bienvenue reçu après inscription
- [ ] Email de facture reçu après envoi
- [ ] Email de confirmation de paiement reçu

### Abonnements / Plans

- [ ] Page des plans accessible
- [ ] Essai gratuit démarré correctement (7 jours)
- [ ] Limite de facturation respectée selon le plan

---

## Rollback d'urgence

En cas de problème critique après déploiement :

```bash
# Activer la page de maintenance
php artisan down

# Revenir à la version précédente (Git)
git log --oneline -10
git checkout COMMIT_PRECEDENT

# Restaurer le cache
php artisan optimize

# Si nécessaire, rollback de migration
php artisan migrate:rollback

# Désactiver la maintenance
php artisan up
```

---

## Contacts utiles

| Service | URL | Notes |
|---------|-----|-------|
| LWS Support | https://www.lws.fr/support/ | Tickets, chat |
| Let's Encrypt | https://letsencrypt.org/fr/ | Renouvellement SSL |
| Moneroo | https://moneroo.io | Dashboard paiements |
| Mailgun | https://www.mailgun.com | Dashboard emails |

---

*Dernière mise à jour : Phase 10 — Documentation déploiement IBIG FactPro*
