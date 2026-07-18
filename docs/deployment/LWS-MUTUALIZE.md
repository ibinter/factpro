# Déploiement IBIG FactPro sur LWS Mutualisé (cPanel)

## Prérequis

- Offre **LWS Pro ou supérieure** (PHP 8.1+, MySQL 5.7+)
- Accès cPanel / Gestionnaire de fichiers
- Accès SSH (optionnel mais recommandé)
- Domaine configuré et pointant vers LWS

---

## Étapes de déploiement

### 1. Préparation locale

Sur votre machine de développement :

```bash
# Installer les dépendances PHP sans les paquets dev
composer install --no-dev --optimize-autoloader

# Compiler les assets front-end
npm ci && npm run build

# Créer une archive du projet (sans node_modules, .git, storage/logs, .env)
zip -r factpro.zip . \
  --exclude "node_modules/*" \
  --exclude ".git/*" \
  --exclude "storage/logs/*" \
  --exclude ".env" \
  --exclude "tests/*"
```

### 2. Upload des fichiers

#### Via le Gestionnaire de fichiers cPanel

1. Connectez-vous à cPanel (https://votre-domaine.com:2083)
2. Ouvrez **Gestionnaire de fichiers**
3. Naviguez vers `/home/votrelogin/`
4. Créez un dossier `factpro` (hors de `public_html`)
5. Uploadez `factpro.zip` dans ce dossier et extrayez-le

#### Via SSH (recommandé)

```bash
scp factpro.zip votrelogin@votre-serveur.lws.fr:/home/votrelogin/
ssh votrelogin@votre-serveur.lws.fr
cd /home/votrelogin/
unzip factpro.zip -d factpro/
```

#### Configuration du domaine pour pointer vers public/

Deux options :

**Option A — Lien symbolique (SSH requis) :**
```bash
# Supprimer public_html existant (sauvegardez d'abord !)
rm -rf /home/votrelogin/public_html
ln -s /home/votrelogin/factpro/public /home/votrelogin/public_html
```

**Option B — Copie du contenu public/ dans public_html :**
```bash
cp -r /home/votrelogin/factpro/public/. /home/votrelogin/public_html/
```
Puis modifier `public_html/index.php` pour ajuster les chemins :
```php
require __DIR__.'/../factpro/vendor/autoload.php';
$app = require_once __DIR__.'/../factpro/bootstrap/app.php';
```

**Option C — Sous-domaine dédié (recommandé) :**
Dans cPanel > Domaines > Sous-domaines, créer `app.votre-domaine.com` avec racine `/home/votrelogin/factpro/public`.

### 3. Configuration .env

Via SSH ou le Gestionnaire de fichiers, créer `/home/votrelogin/factpro/.env` :

```env
APP_NAME="IBIG FactPro"
APP_ENV=production
APP_KEY=                          # Générer : php artisan key:generate
APP_DEBUG=false
APP_URL=https://votre-domaine.com

APP_LOCALE=fr
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=votrelogin_factpro    # Nom créé dans cPanel
DB_USERNAME=votrelogin_user
DB_PASSWORD=VotreMotDePasseSecure

# Sur mutualisé : utiliser file/database (pas Redis)
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
SESSION_LIFETIME=480

MAIL_MAILER=smtp
MAIL_HOST=mail.votre-domaine.com
MAIL_PORT=587
MAIL_USERNAME=factpro@votre-domaine.com
MAIL_PASSWORD=VotreMotDePasse
MAIL_FROM_ADDRESS=factpro@votre-domaine.com
MAIL_FROM_NAME="IBIG FactPro"

VERIFY_BASE_URL=https://votre-domaine.com/verify
TRIAL_DURATION_DAYS=7
LICENSE_GRACE_PERIOD_DAYS=7
```

Sécuriser le fichier :
```bash
chmod 600 /home/votrelogin/factpro/.env
```

### 4. Base de données MySQL

1. Dans cPanel > **MySQL Databases** :
   - Créer une base : `votrelogin_factpro`
   - Créer un utilisateur : `votrelogin_user` avec mot de passe fort
   - Accorder **ALL PRIVILEGES** à l'utilisateur sur la base

2. Exécuter les migrations :
```bash
cd /home/votrelogin/factpro
php artisan migrate --force
php artisan db:seed --class=PlansSeeder
```

Ou via phpMyAdmin si SSH non disponible : importer un dump SQL préparé localement avec `php artisan migrate` + `mysqldump`.

### 5. Permissions des dossiers

```bash
cd /home/votrelogin/factpro

# Dossiers writables par PHP
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Propriétaire correct
chown -R votrelogin:votrelogin storage/ bootstrap/cache/
```

### 6. Génération de la clé et mise en cache

```bash
cd /home/votrelogin/factpro

php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### 7. Tâche cron pour le scheduler Laravel

Dans cPanel > **Tâches Cron** :

```
* * * * * /usr/local/bin/php /home/votrelogin/factpro/artisan schedule:run >> /dev/null 2>&1
```

> **Note** : Vérifier la version de PHP disponible. Sur LWS, utiliser le chemin complet :  
> `/usr/local/php81/bin/php` pour PHP 8.1  
> `/usr/local/php82/bin/php` pour PHP 8.2

### 8. Queue Workers

**Sur mutualisé, les queues asynchrones ne sont pas disponibles.**

Utiliser obligatoirement dans `.env` :
```env
QUEUE_CONNECTION=sync
```

Avec `sync`, chaque job s'exécute immédiatement dans la même requête HTTP (léger impact sur les temps de réponse). Pour les queues asynchrones (emails en arrière-plan, notifications, etc.), passer sur un **VPS** (voir [LWS-VPS.md](LWS-VPS.md)).

### 9. SSL / HTTPS

1. Dans cPanel > **SSL/TLS** > Let's Encrypt :
   - Sélectionner votre domaine
   - Cliquer **Émettre** — le certificat est installé automatiquement

2. Forcer HTTPS dans `.htaccess` (dossier `public/`) :
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    # Forcer HTTPS
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    # Laravel front controller
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

3. Mettre à jour `APP_URL` dans `.env` :
```env
APP_URL=https://votre-domaine.com
```

Puis reconstruire le cache :
```bash
php artisan config:cache
```

---

## Vérification post-déploiement

```bash
# Tester la configuration
php artisan config:show app

# Tester la connexion BDD
php artisan db:show

# Voir les logs en cas d'erreur
tail -f storage/logs/laravel.log
```

---

## Limitations : Mutualisé vs VPS

| Fonctionnalité | Mutualisé LWS | VPS LWS |
|----------------|---------------|---------|
| PHP 8.1 / 8.2 | Oui | Oui |
| MySQL | Oui | Oui |
| Redis | Non | Oui |
| Queues asynchrones | Non (sync uniquement) | Oui (Supervisor) |
| Supervisor / démons | Non | Oui |
| Accès root | Non | Oui |
| Websockets (Reverb) | Non | Oui |
| Personnalisation Nginx/Apache | Limitée (.htaccess) | Complète |
| Cron (min. 1 min) | Oui | Oui |
| Stockage S3 | Via API externe | Via API externe |
| Certificat SSL Let's Encrypt | Automatique cPanel | Certbot manuel |
| Mémoire PHP (max) | 256 MB (selon offre) | Configurable |
| Temps d'exécution max | 30–300 s | Illimité |
| Prix (indicatif) | ~3–15 €/mois | ~12–50 €/mois |

> **Recommandation** : Pour une utilisation en production avec plusieurs entreprises clientes, optez pour le VPS qui offre les queues Redis, les workers en arrière-plan et la personnalisation complète. Voir [LWS-VPS.md](LWS-VPS.md).

---

## Dépannage courant

| Erreur | Cause probable | Solution |
|--------|---------------|----------|
| 500 Internal Server Error | `.env` absent ou `APP_KEY` vide | Créer `.env` et `php artisan key:generate` |
| White screen | `APP_DEBUG=true` et erreur PHP | Vérifier `storage/logs/laravel.log` |
| "Permission denied" | `storage/` non writable | `chmod -R 775 storage/ bootstrap/cache/` |
| "No application encryption key" | APP_KEY manquant | `php artisan key:generate` |
| Pas d'email reçu | MAIL_MAILER=log en prod | Configurer SMTP réel |
| Assets 404 | `npm run build` non exécuté | Exécuter en local et re-uploader `public/build/` |
| Cron ne s'exécute pas | Chemin PHP incorrect | Tester manuellement depuis SSH |
