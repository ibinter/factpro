#!/bin/bash
# Vérification santé post-déploiement FactPro
# Usage : bash scripts/health-check.sh [BASE_URL]

BASE_URL="${1:-https://app.factpro.ibigsoft.com}"

check() {
    local name="$1"
    local url="$2"
    local expected="${3:-200}"
    local status
    status=$(curl -s -o /dev/null -w "%{http_code}" "$url")
    if [ "$status" = "$expected" ]; then
        echo "OK  $name ($status)"
    else
        echo "ERR $name — attendu $expected, reçu $status"
        FAILED=$((FAILED + 1))
    fi
}

FAILED=0

echo "==> Health check sur $BASE_URL"
check "Page accueil"    "$BASE_URL/"
check "Health endpoint" "$BASE_URL/health"
check "Page tarifs"     "$BASE_URL/pricing"
check "API OpenAPI"     "$BASE_URL/api/openapi.json"
check "Login page"      "$BASE_URL/login"

echo ""
if [ "$FAILED" -gt 0 ]; then
    echo "ECHEC : $FAILED vérification(s) ont échoué"
    exit 1
fi
echo "SUCCES : toutes les vérifications sont OK"
