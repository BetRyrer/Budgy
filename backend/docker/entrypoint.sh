#!/bin/bash
set -e

# Filet de sécurité : si le volume nommé "backend_vendor" a été créé vide
# (par ex. premier démarrage sur une machine où le volume existait déjà),
# on réinstalle les dépendances avant de démarrer PHP-FPM.
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] vendor/ manquant, exécution de composer install..."
    composer install --no-interaction --optimize-autoloader
fi

# Les clés JWT (config/jwt/) sont gitignorées : on les génère au premier démarrage
# si elles sont absentes, pour que `docker compose up` fonctionne sans étape manuelle.
if [ ! -f config/jwt/private.pem ]; then
    echo "[entrypoint] Clés JWT manquantes, génération..."
    php bin/console lexik:jwt:generate-keypair --no-interaction
fi

exec "$@"
