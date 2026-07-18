# Déploiement sur hébergement mutualisé LWS (SSH) — IBIG FactPro

Procédure conforme au cahier des charges IBIG §21 : hébergement mutualisé LWS avec accès SSH, domaine `factpro.ibigsoft.com`.

## 1. Préparation côté panel LWS

### 1.1 Version PHP

Dans le panel LWS (**Configuration PHP**), sélectionner **PHP 8.3** pour le domaine.

### 1.2 Extensions PHP requises

Vérifier / activer dans le panel :

`pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip`, `curl`, `intl`, `bcmath`, `xml`

Contrôle rapide en SSH :

```bash
/usr/local/php8.3/bin/php -m | grep -Ei 'pdo_mysql|mbstring|openssl|gd|zip|curl|intl|bcmath|xml'
```

### 1.3 Base de données

Dans le panel **MySQL / phpMyAdmin** :

1. Créer une base `factpro` (encodage `utf8mb4_unicode_ci`).
2. Créer un utilisateur dédié avec mot de passe fort, tous privilèges sur cette base.
3. Noter : hôte (souvent `localhost`), nom de base, utilisateur, mot de passe.

### 1.4 DNS

Chez le registrar / zone DNS ibigsoft.com, créer l'enregistrement :

```
factpro.ibigsoft.com.   CNAME   <hôte-mutualisé-LWS>.
```

(ou un enregistrement A vers l'IP du mutualisé selon la configuration LWS), puis déclarer le sous-domaine dans le panel LWS.

## 2. Déploiement du code (SSH)

```bash
ssh user@serveur-lws

# Cloner dans le home (PAS dans public_html)
cd /home/user
git clone <url-du-depot> factpro
cd factpro

# Dépendances PHP en mode production
/usr/local/php8.3/bin/php $(which composer) install --no-dev --optimize-autoloader
# ou si composer est un binaire global compatible :
composer install --no-dev --optimize-autoloader
```

### 2.1 Assets frontend

**Option A — build sur le serveur** (si Node 20+ disponible sur le mutualisé) :

```bash
npm ci
npm run build
```

**Option B — build local puis upload** (recommandé sur mutualisé sans Node) :

```bash
# Sur votre poste
npm ci && npm run build
# Puis transférer uniquement le dossier compilé
scp -r public/build user@serveur-lws:/home/user/factpro/public/
```

## 3. Configuration `.env` production

```bash
cp .env.example .env
nano .env
```

Valeurs essentielles :

```env
APP_NAME="IBIG FactPro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://factpro.ibigsoft.com
APP_LOCALE=fr

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=factpro
DB_USERNAME=<utilisateur-mysql-lws>
DB_PASSWORD=<mot-de-passe-fort>

# Mutualisé : drivers fichier (pas de Redis)
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync        # ou "database" avec le cron queue:work (voir §6)

# SMTP LWS
MAIL_MAILER=smtp
MAIL_HOST=mail.ibigsoft.com   # serveur SMTP fourni par LWS
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=factpro@ibigsoft.com
MAIL_PASSWORD=<mot-de-passe-boite>
MAIL_FROM_ADDRESS=factpro@ibigsoft.com
MAIL_FROM_NAME="${APP_NAME}"

VERIFY_BASE_URL="${APP_URL}/verify"
TRIAL_DURATION_DAYS=7
LICENSE_GRACE_PERIOD_DAYS=7
LICENSE_PROVISIONAL_MAX_DAYS=7
PROOF_STORAGE_DISK=local
PROOF_MAX_SIZE_MB=10
FRAUD_ALERT_EMAIL=admin@ibigsoft.com
FRAUD_AMOUNT_TOLERANCE_PERCENT=5
```

## 4. Initialisation de l'application

```bash
cd /home/user/factpro
PHP=/usr/local/php8.3/bin/php

$PHP artisan key:generate
$PHP artisan migrate --force
$PHP artisan db:seed --class=PlanSeeder --force   # grille tarifaire uniquement (pas les comptes de démo)
$PHP artisan storage:link

# Caches de production
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
```

> **Compte superadmin** : en production, ne pas exécuter `DatabaseSeeder` (comptes de démo). Créer le superadmin manuellement via tinker :
>
> ```bash
> $PHP artisan tinker --execute="App\Models\User::create(['name'=>'Superadmin IBIG','email'=>'admin@ibigsoft.com','password'=>bcrypt('<MOT-DE-PASSE-FORT-UNIQUE>'),'is_superadmin'=>true,'email_verified_at'=>now()]);"
> ```

## 5. Document root → `public/`

Le document root doit pointer vers `/home/user/factpro/public`, jamais vers la racine du projet (le `.env` et `storage/` ne doivent pas être servis).

**Option A — symlink (préférée)** :

```bash
cd /home/user
mv public_html public_html.bak    # sauvegarde de l'existant
ln -s /home/user/factpro/public public_html
```

**Option B — `.htaccess` de redirection** (si LWS interdit le symlink) — créer `/home/user/public_html/.htaccess` :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ /factpro/public/$1 [L]
</IfModule>
```

et copier/déployer le projet de sorte que `factpro/public` soit atteignable sous le web root, **ou** copier le contenu de `public/` dans `public_html/` en adaptant `index.php` (`require __DIR__.'/../factpro/vendor/autoload.php'` et `bootstrap/app.php`). L'option A reste la plus simple et la plus sûre.

Vérifier que le `.htaccess` Laravel de `public/` est bien actif (réécriture vers `index.php`).

## 6. CRON (planificateur + file d'attente)

Dans le panel LWS (**Tâches planifiées / Cron**) :

```cron
* * * * * /usr/local/php8.3/bin/php /home/user/factpro/artisan schedule:run >> /dev/null 2>&1
```

File d'attente sur mutualisé (pas de démon persistant) — deux options :

- **`QUEUE_CONNECTION=sync`** (le plus simple) : les jobs s'exécutent dans la requête.
- **`QUEUE_CONNECTION=database`** + cron dédié qui vide la file puis s'arrête :

```cron
* * * * * /usr/local/php8.3/bin/php /home/user/factpro/artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

## 7. SSL

Dans le panel LWS, activer **Let's Encrypt** pour `factpro.ibigsoft.com` (renouvellement automatique), puis forcer HTTPS (option du panel ou règle `.htaccess`).

## 8. Checklist post-déploiement

| # | Vérification | Attendu |
|---|---|---|
| 1 | `https://factpro.ibigsoft.com/` | Page d'accueil (Welcome) en HTTPS, assets Vite chargés |
| 2 | `/register` | Inscription : création utilisateur + société + **essai 7 jours** démarré |
| 3 | Connexion → `/dashboard` | Bandeau licence « essai — X jours restants » |
| 4 | Créer un devis puis **Finaliser** | Numéro `DEV-2026-0001`, statut `sent`, scellement OK |
| 5 | Export **PDF** | PDF A4 avec QR + filigrane « VERSION ESSAI FACTPRO » |
| 6 | Scanner le QR / ouvrir `/verify/{uuid}` | Page publique **DOCUMENT AUTHENTIQUE** sans être connecté |
| 7 | `/billing/plans` | 4 forfaits FCFA affichés (PlanSeeder) |
| 8 | Déclarer un paiement test + preuve | Preuve dans `storage/app/private/proofs`, **non accessible par URL publique** |
| 9 | `/admin/payments` (superadmin) | File de validation + statistiques |
| 10 | `storage/logs/laravel.log` | Aucune erreur ; `APP_DEBUG=false` confirmé (page 500 générique) |

## 9. Retour arrière (rollback)

**Avant chaque mise à jour**, systématiquement :

```bash
cd /home/user/factpro
PHP=/usr/local/php8.3/bin/php

# 1. Sauvegarde base de données
mysqldump -u <user> -p factpro > /home/user/backups/factpro-$(date +%Y%m%d-%H%M).sql

# 2. Point de restauration git
git tag prod-$(date +%Y%m%d-%H%M)

# 3. Mode maintenance
$PHP artisan down --retry=60
```

Mise à jour :

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
$PHP artisan migrate --force
$PHP artisan config:cache && $PHP artisan route:cache && $PHP artisan view:cache
$PHP artisan up
```

**En cas de problème**, retour à l'état précédent :

```bash
$PHP artisan down
git checkout prod-<horodatage-du-tag>
composer install --no-dev --optimize-autoloader
mysql -u <user> -p factpro < /home/user/backups/factpro-<horodatage>.sql
$PHP artisan config:cache && $PHP artisan route:cache && $PHP artisan view:cache
$PHP artisan up
```

> Conserver au minimum les 7 dernières sauvegardes SQL et purger les plus anciennes. Les preuves de paiement (`storage/app/private/proofs`) doivent être incluses dans la sauvegarde de fichiers LWS.
