# Référence des variables d'environnement — IBIG FactPro

> Toutes les variables disponibles dans `.env`. Voir `.env.production.example` pour un template prêt à l'emploi.

---

## Core Laravel

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `APP_NAME` | Nom affiché de l'application | `"IBIG FactPro"` | Oui |
| `APP_ENV` | Environnement (`local`, `production`, `staging`) | `local` | Oui |
| `APP_DEBUG` | Mode debug — **TOUJOURS `false` en production** | `true` | Oui |
| `APP_KEY` | Clé de chiffrement base64 — générer avec `php artisan key:generate` | *(vide)* | Oui |
| `APP_URL` | URL publique avec protocole (ex: `https://factpro.com`) | `http://localhost` | Oui |
| `APP_TIMEZONE` | Fuseau horaire PHP (ex: `Africa/Abidjan`, `Europe/Paris`) | `UTC` | Non |
| `APP_LOCALE` | Locale principale de l'app | `fr` | Non |
| `APP_FALLBACK_LOCALE` | Locale de secours | `en` | Non |
| `APP_FAKER_LOCALE` | Locale pour les données de test | `fr_FR` | Non |
| `APP_MAINTENANCE_DRIVER` | Driver de maintenance (`file`, `cache`) | `file` | Non |
| `BCRYPT_ROUNDS` | Nombre de rounds bcrypt (12 recommandé en prod) | `12` | Non |

---

## Logging

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `LOG_CHANNEL` | Canal de log (`stack`, `single`, `daily`, `slack`) | `stack` | Non |
| `LOG_STACK` | Canaux empilés dans `stack` | `single` | Non |
| `LOG_LEVEL` | Niveau minimum (`debug`, `info`, `warning`, `error`) — `error` en prod | `debug` | Non |
| `LOG_DEPRECATIONS_CHANNEL` | Canal pour les dépréciations PHP | `null` | Non |
| `LOG_SLACK_WEBHOOK_URL` | URL webhook Slack pour les alertes critiques | *(vide)* | Non |

---

## Base de données

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `DB_CONNECTION` | Driver (`mysql`, `pgsql`, `sqlite`) | `mysql` | Oui |
| `DB_HOST` | Hôte MySQL | `127.0.0.1` | Oui |
| `DB_PORT` | Port MySQL | `3306` | Non |
| `DB_DATABASE` | Nom de la base de données | `factpro` | Oui |
| `DB_USERNAME` | Utilisateur MySQL | `root` | Oui |
| `DB_PASSWORD` | Mot de passe MySQL | *(vide)* | Oui (prod) |

---

## Session

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `SESSION_DRIVER` | Driver de session (`database`, `redis`, `file`, `cookie`) | `database` | Oui |
| `SESSION_LIFETIME` | Durée de session en minutes (480 = 8h) | `120` | Non |
| `SESSION_ENCRYPT` | Chiffrement des sessions | `false` | Non |
| `SESSION_PATH` | Chemin du cookie | `/` | Non |
| `SESSION_DOMAIN` | Domaine du cookie (`.votre-domaine.com` pour sous-domaines) | `null` | Non |

---

## Cache

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `CACHE_STORE` | Driver (`file`, `redis`, `database`, `array`) | `database` | Non |
| `CACHE_PREFIX` | Préfixe des clés de cache (utile sur serveurs partagés) | *(vide)* | Non |

---

## Queues

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `QUEUE_CONNECTION` | Driver (`sync`, `database`, `redis`) — `sync` sur mutualisé | `database` | Oui |

> **Production VPS** : `redis`  
> **Production mutualisé** : `sync` (jobs exécutés immédiatement)  
> **Développement** : `sync` ou `database`

---

## Redis

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `REDIS_CLIENT` | Client PHP (`phpredis`, `predis`) | `phpredis` | Non |
| `REDIS_HOST` | Hôte Redis | `127.0.0.1` | Si Redis utilisé |
| `REDIS_PASSWORD` | Mot de passe Redis | `null` | Non |
| `REDIS_PORT` | Port Redis | `6379` | Non |

---

## Broadcast

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `BROADCAST_CONNECTION` | Driver (`log`, `reverb`, `pusher`) | `log` | Non |
| `REVERB_APP_ID` | ID app Reverb (WebSockets auto-hébergés) | *(vide)* | Non |
| `REVERB_APP_KEY` | Clé Reverb | *(vide)* | Non |
| `REVERB_APP_SECRET` | Secret Reverb | *(vide)* | Non |
| `REVERB_HOST` | Hôte du serveur Reverb | `localhost` | Non |
| `REVERB_PORT` | Port Reverb | `8080` | Non |
| `REVERB_SCHEME` | Schéma (`http`, `https`) | `https` | Non |

---

## Email (MAIL_*)

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `MAIL_MAILER` | Driver (`smtp`, `log`, `sendmail`, `mailgun`, `ses`) | `log` | Oui (prod) |
| `MAIL_SCHEME` | Schéma SMTP (`null`, `ssl`, `tls`) | `null` | Non |
| `MAIL_HOST` | Serveur SMTP | `127.0.0.1` | Si SMTP |
| `MAIL_PORT` | Port SMTP (587 STARTTLS, 465 SSL, 25 sans chiffrement) | `2525` | Si SMTP |
| `MAIL_USERNAME` | Identifiant SMTP | `null` | Si SMTP |
| `MAIL_PASSWORD` | Mot de passe SMTP | `null` | Si SMTP |
| `MAIL_FROM_ADDRESS` | Adresse expéditeur | `factpro@ibigsoft.com` | Oui |
| `MAIL_FROM_NAME` | Nom expéditeur | `${APP_NAME}` | Non |
| `MAILGUN_DOMAIN` | Domaine Mailgun | *(vide)* | Si Mailgun |
| `MAILGUN_SECRET` | Clé API Mailgun | *(vide)* | Si Mailgun |
| `MAILGUN_ENDPOINT` | Endpoint (`api.mailgun.net`, `api.eu.mailgun.net`) | `api.mailgun.net` | Non |

---

## Stockage de fichiers

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `FILESYSTEM_DISK` | Disque par défaut (`local`, `public`, `s3`) | `local` | Non |
| `PROOF_STORAGE_DISK` | Disque pour les preuves de paiement | `local` | Non |
| `PROOF_MAX_SIZE_MB` | Taille max des preuves (Mo) | `10` | Non |

### Amazon S3 (optionnel)

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `AWS_ACCESS_KEY_ID` | Clé d'accès AWS | *(vide)* | Si S3 |
| `AWS_SECRET_ACCESS_KEY` | Clé secrète AWS | *(vide)* | Si S3 |
| `AWS_DEFAULT_REGION` | Région AWS | `us-east-1` | Si S3 |
| `AWS_BUCKET` | Nom du bucket S3 | *(vide)* | Si S3 |
| `AWS_USE_PATH_STYLE_ENDPOINT` | Utiliser path-style (MinIO) | `false` | Non |

---

## Passerelles de paiement

### Moneroo (Côte d'Ivoire, Sénégal, Cameroun, Guinée, etc.)

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `MONEROO_PUBLIC_KEY` | Clé publique Moneroo | *(vide)* | Si Moneroo actif |
| `MONEROO_SECRET_KEY` | Clé secrète Moneroo | *(vide)* | Si Moneroo actif |
| `MONEROO_WEBHOOK_SECRET` | Secret HMAC pour validation des webhooks | *(vide)* | Oui si Moneroo |
| `MONEROO_MODE` | Mode (`sandbox`, `live`) | `sandbox` | Oui si Moneroo |

### CinetPay (Côte d'Ivoire, Sénégal, Mali, Burkina Faso, Togo, etc.)

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `CINETPAY_API_KEY` | Clé API CinetPay | *(vide)* | Si CinetPay actif |
| `CINETPAY_SITE_ID` | Identifiant site CinetPay | *(vide)* | Si CinetPay actif |
| `CINETPAY_MODE` | Mode (`TEST`, `PRODUCTION`) | `TEST` | Oui si CinetPay |

### FedaPay (Bénin, Togo, Sénégal)

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `FEDAPAY_SECRET_KEY` | Clé secrète FedaPay | *(vide)* | Si FedaPay actif |
| `FEDAPAY_MODE` | Mode (`sandbox`, `live`) | `sandbox` | Oui si FedaPay |

### Flutterwave (Afrique anglophone + Nigeria)

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `FLUTTERWAVE_SECRET_KEY` | Clé secrète Flutterwave | *(vide)* | Si Flutterwave actif |
| `FLUTTERWAVE_PUBLIC_KEY` | Clé publique Flutterwave | *(vide)* | Si Flutterwave actif |
| `FLUTTERWAVE_HASH` | Secret de vérification des webhooks | *(vide)* | Si Flutterwave actif |

### Stripe (International)

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `STRIPE_KEY` | Clé publique Stripe | *(vide)* | Si Stripe actif |
| `STRIPE_SECRET` | Clé secrète Stripe | *(vide)* | Si Stripe actif |
| `STRIPE_WEBHOOK_SECRET` | Secret webhook Stripe | *(vide)* | Si Stripe actif |

---

## SMS & WhatsApp

### Africa's Talking

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `AFRICASTALKING_USERNAME` | Nom d'utilisateur Africa's Talking | *(vide)* | Si SMS AT |
| `AFRICASTALKING_API_KEY` | Clé API Africa's Talking | *(vide)* | Si SMS AT |

### Twilio (SMS + WhatsApp)

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `TWILIO_ACCOUNT_SID` | SID de compte Twilio | *(vide)* | Si Twilio |
| `TWILIO_AUTH_TOKEN` | Token d'auth Twilio | *(vide)* | Si Twilio |
| `TWILIO_FROM_NUMBER` | Numéro d'envoi Twilio | *(vide)* | Si Twilio |
| `TWILIO_WHATSAPP_FROM` | Numéro WhatsApp Business Twilio | *(vide)* | Si WhatsApp |

---

## Fonctionnalités avancées

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `VERIFY_BASE_URL` | URL de base pour la vérification QR des documents | `${APP_URL}/verify` | Oui |
| `GOOGLE_VISION_KEY` | Clé Google Vision API pour l'OCR des factures fournisseurs | *(vide)* | Non |
| `EXCHANGE_RATE_API_KEY` | Clé API pour les taux de change (open.er-api.com fallback si vide) | *(vide)* | Non |

---

## Licences et essai gratuit

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `TRIAL_DURATION_DAYS` | Durée de la période d'essai en jours | `7` | Non |
| `LICENSE_GRACE_PERIOD_DAYS` | Jours de grâce après expiration | `7` | Non |
| `LICENSE_PROVISIONAL_MAX_DAYS` | Durée max d'une licence provisoire | `7` | Non |

---

## Anti-fraude

| Variable | Description | Défaut | Obligatoire |
|----------|-------------|--------|-------------|
| `FRAUD_ALERT_EMAIL` | Email de notification des alertes fraude | `admin@ibigsoft.com` | Non |
| `FRAUD_AMOUNT_TOLERANCE_PERCENT` | Tolérance (%) sur les montants de paiement manuel | `5` | Non |

---

## Front-end (Vite)

Ces variables sont exposées au JavaScript côté client via Vite :

| Variable | Description |
|----------|-------------|
| `VITE_APP_NAME` | Nom de l'app pour le front-end |
| `VITE_REVERB_APP_KEY` | Clé Reverb pour les WebSockets |
| `VITE_REVERB_HOST` | Hôte Reverb |
| `VITE_REVERB_PORT` | Port Reverb |
| `VITE_REVERB_SCHEME` | Schéma Reverb |

> **Attention** : Les variables préfixées `VITE_` sont publiques et incluses dans le bundle JavaScript. Ne jamais y mettre de secrets.

---

## Variables PHP (non-.env)

Ces valeurs sont configurées dans `config/` et ne dépendent pas du `.env` mais peuvent être pertinentes :

| Config | Valeur recommandée prod | Fichier |
|--------|------------------------|---------|
| `opcache.enable` | `1` | `php.ini` |
| `memory_limit` | `256M` | `php.ini` |
| `upload_max_filesize` | `15M` | `php.ini` |
| `post_max_size` | `20M` | `php.ini` |
| `max_execution_time` | `300` | `php.ini` |

---

*Référence générée pour IBIG FactPro — Phase 10 Documentation déploiement*
