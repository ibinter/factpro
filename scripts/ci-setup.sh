#!/bin/bash
# Script de setup de l'environnement CI
# Usage : bash scripts/ci-setup.sh

set -e

echo "==> Setup CI FactPro"

# Copie du .env CI si pas encore fait
if [ ! -f .env ]; then
    cp .env.ci .env
    echo "  [ok] .env créé depuis .env.ci"
fi

# Génération de la clé application
php artisan key:generate --force
echo "  [ok] APP_KEY générée"

# Migrations
php artisan migrate --force
echo "  [ok] Migrations exécutées"

# Cache des configs (optionnel en CI)
# php artisan config:cache

echo "==> Setup CI terminé"
