#!/bin/bash
set -euo pipefail

# --- Clés JWT ---
# Le filesystem est éphémère sur les plans gratuits (Render) : les clés ne
# peuvent pas être générées une fois pour toutes comme en dev, elles doivent
# être injectées à chaque démarrage depuis des variables d'environnement
# (sinon un redeploy invaliderait tous les tokens émis).
mkdir -p config/jwt
if [ -n "${JWT_PRIVATE_KEY_BASE64:-}" ] && [ -n "${JWT_PUBLIC_KEY_BASE64:-}" ]; then
    echo "$JWT_PRIVATE_KEY_BASE64" | base64 -d > config/jwt/private.pem
    echo "$JWT_PUBLIC_KEY_BASE64" | base64 -d > config/jwt/public.pem
else
    echo "[entrypoint] JWT_PRIVATE_KEY_BASE64 / JWT_PUBLIC_KEY_BASE64 manquantes, impossible de démarrer." >&2
    exit 1
fi
chown -R www-data:www-data config/jwt

# --- Cache Symfony ---
# Reconstruit avec les vraies variables d'environnement injectées par Render
# (DATABASE_URL, APP_SECRET, CORS_ALLOW_ORIGIN...), absentes au moment du build.
rm -rf var/cache/*
php bin/console cache:clear --no-debug
chown -R www-data:www-data var

# --- Migrations ---
# Idempotent (les migrations déjà appliquées sont ignorées) : exécuté à
# chaque démarrage pour ne pas dépendre d'une étape manuelle sur Render.
php bin/console doctrine:migrations:migrate --no-interaction --no-debug

# --- Nginx ---
# Render impose le port d'écoute via $PORT ; on l'injecte dans le template.
export PORT="${PORT:-10000}"
envsubst '${PORT}' < /etc/nginx/http.d/prod.conf.template > /etc/nginx/http.d/default.conf

php-fpm -F &
exec nginx -g 'daemon off;'
