# Déploiement IBIG FactPro sur LWS VPS (Ubuntu 22.04)

## Architecture cible

```
Internet → UFW (80/443/22) → Nginx → PHP 8.2-FPM → Laravel
                                   → MySQL 8.0
                                   → Redis 7
                          Supervisor → Queue Workers (8x)
                          Cron      → Laravel Scheduler
```

---

## 1. Provisionnement initial du VPS

### 1.1 Connexion et mise à jour

```bash
ssh root@VOTRE_IP_VPS

# Mise à jour du système
apt update && apt upgrade -y

# Outils essentiels
apt install -y curl wget git unzip zip vim htop
```

### 1.2 Création d'un utilisateur dédié

```bash
adduser deploy
usermod -aG sudo deploy

# Copier les clés SSH de root vers deploy
rsync --archive --chown=deploy:deploy ~/.ssh /home/deploy

# Se connecter en tant que deploy pour la suite
su - deploy
```

---

## 2. Installation de la stack

### 2.1 Nginx

```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

### 2.2 PHP 8.2-FPM

```bash
# Ajouter le dépôt Ondrej
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# PHP 8.2 + extensions Laravel
sudo apt install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-intl \
    php8.2-bcmath \
    php8.2-redis \
    php8.2-opcache

sudo systemctl enable php8.2-fpm
sudo systemctl start php8.2-fpm
```

### 2.3 MySQL 8.0

```bash
sudo apt install -y mysql-server
sudo systemctl enable mysql
sudo mysql_secure_installation
```

Créer la base de données et l'utilisateur :
```sql
sudo mysql -u root -p

CREATE DATABASE factpro_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'factpro_user'@'localhost' IDENTIFIED BY 'VotreMotDePasseSecure123!';
GRANT ALL PRIVILEGES ON factpro_prod.* TO 'factpro_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2.4 Redis 7

```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server

# Configurer un mot de passe Redis (recommandé)
sudo sed -i 's/# requirepass foobared/requirepass VotreMotDePasseRedis/' /etc/redis/redis.conf
sudo systemctl restart redis-server
```

### 2.5 Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### 2.6 Node.js 20 (pour la compilation des assets)

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2.7 Supervisor (queue workers)

```bash
sudo apt install -y supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

---

## 3. Déploiement de l'application

### 3.1 Cloner le dépôt

```bash
sudo mkdir -p /var/www/factpro
sudo chown deploy:deploy /var/www/factpro

cd /var/www
git clone https://github.com/votre-org/factpro.git factpro
cd /var/www/factpro
```

### 3.2 Installer les dépendances

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

### 3.3 Configurer .env

```bash
cp .env.production.example .env
# Éditer le fichier :
vim .env
# Renseigner DB_PASSWORD, MAIL_*, MONEROO_*, etc.

# Générer la clé
php artisan key:generate
chmod 600 .env
```

### 3.4 Permissions

```bash
sudo chown -R deploy:www-data /var/www/factpro
chmod -R 755 /var/www/factpro
chmod -R 775 /var/www/factpro/storage
chmod -R 775 /var/www/factpro/bootstrap/cache
```

### 3.5 Migrations et cache

```bash
php artisan migrate --force
php artisan db:seed --class=PlansSeeder
php artisan storage:link
php artisan optimize
```

---

## 4. Configuration Nginx

Créer le vhost :

```bash
sudo vim /etc/nginx/sites-available/factpro
```

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name votre-domaine.com www.votre-domaine.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name votre-domaine.com www.votre-domaine.com;

    root /var/www/factpro/public;
    index index.php;

    # SSL (géré par Certbot, voir section 7)
    ssl_certificate /etc/letsencrypt/live/votre-domaine.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/votre-domaine.com/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # Sécurité
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # Taille max upload (preuves de paiement)
    client_max_body_size 15M;

    # Logs
    access_log /var/log/nginx/factpro_access.log;
    error_log  /var/log/nginx/factpro_error.log;

    # Front controller Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_hide_header X-Powered-By;
    }

    # Interdire l'accès aux fichiers sensibles
    location ~ /\.(ht|env|git) {
        deny all;
    }

    # Assets statiques — cache long
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Storage public
    location /storage {
        alias /var/www/factpro/storage/app/public;
        expires 30d;
    }
}
```

Activer le site :
```bash
sudo ln -s /etc/nginx/sites-available/factpro /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx
```

---

## 5. Configuration PHP-FPM

Éditer le pool PHP-FPM :

```bash
sudo vim /etc/php/8.2/fpm/pool.d/factpro.conf
```

```ini
[factpro]
user = deploy
group = www-data
listen = /var/run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 8
pm.max_requests = 500

; Timeouts
request_terminate_timeout = 300

; Variables d'environnement
env[APP_ENV] = production
```

```bash
sudo systemctl restart php8.2-fpm
```

Optimisations OPcache (`/etc/php/8.2/fpm/conf.d/10-opcache.ini`) :
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.revalidate_freq=0
opcache.validate_timestamps=0
opcache.save_comments=1
```

---

## 6. Supervisor — Queue Workers

```bash
sudo vim /etc/supervisor/conf.d/factpro-worker.conf
```

```ini
[program:factpro-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/factpro/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/factpro/storage/logs/worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start factpro-worker:*
sudo supervisorctl status
```

---

## 7. Cron — Laravel Scheduler

```bash
crontab -e -u deploy
```

Ajouter :
```cron
* * * * * /usr/bin/php /var/www/factpro/artisan schedule:run >> /dev/null 2>&1
```

---

## 8. SSL avec Certbot (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx

# Obtenir le certificat (Nginx doit écouter sur 80)
sudo certbot --nginx -d votre-domaine.com -d www.votre-domaine.com

# Vérifier le renouvellement automatique
sudo certbot renew --dry-run
```

Le renouvellement automatique est configuré via un timer systemd par Certbot.

---

## 9. Firewall UFW

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing

sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS

# Optionnel : restreindre SSH à votre IP
# sudo ufw allow from VOTRE_IP to any port 22

sudo ufw enable
sudo ufw status verbose
```

---

## 10. Script de déploiement automatisé

Voir [../../scripts/deploy.sh](../../scripts/deploy.sh) pour le script de déploiement via Git.

Usage :
```bash
chmod +x /var/www/factpro/scripts/deploy.sh
bash /var/www/factpro/scripts/deploy.sh
```

---

## 11. Backup automatique

Voir [../../scripts/backup.sh](../../scripts/backup.sh).

Configurer en cron :
```bash
crontab -e -u deploy
```
```cron
0 2 * * * /var/www/factpro/scripts/backup.sh >> /var/log/factpro-backup.log 2>&1
```

---

## 12. Monitoring

### Logs applicatifs
```bash
tail -f /var/www/factpro/storage/logs/laravel.log
tail -f /var/log/nginx/factpro_error.log
```

### Statut des services
```bash
sudo systemctl status nginx php8.2-fpm mysql redis-server supervisor
sudo supervisorctl status
```

### Performances
```bash
htop
# Connexions actives
ss -tlnp
# Requêtes MySQL en cours
sudo mysql -e "SHOW PROCESSLIST;"
```

---

## 13. Mise à jour de l'application

```bash
bash /var/www/factpro/scripts/deploy.sh
```

Ou manuellement :
```bash
cd /var/www/factpro
php artisan down --secret="votre-token-bypass"

git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
php artisan queue:restart

php artisan up
```

---

## Variables d'environnement clés pour VPS

```env
# Redis disponible sur VPS
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=VotreMotDePasseRedis
REDIS_PORT=6379

# Optimisations prod
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

---

## Checklist finale VPS

- [ ] Nginx répond sur 443 (HTTPS)
- [ ] PHP-FPM pool `factpro` actif
- [ ] MySQL base `factpro_prod` créée et migrée
- [ ] Redis opérationnel et authentifié
- [ ] 8 queue workers Supervisor en `RUNNING`
- [ ] Cron scheduler configuré (`crontab -l`)
- [ ] UFW actif (80, 443, 22 uniquement)
- [ ] `.env` chmod 600 et hors `public/`
- [ ] SSL Let's Encrypt valide
- [ ] `storage/` et `bootstrap/cache/` writables
- [ ] Backup cron configuré
- [ ] `php artisan queue:restart` testé
