#!/bin/bash
set -e

# Filet de sécurité : si le volume nommé "backend_vendor" a été créé vide
# (par ex. premier démarrage sur une machine où le volume existait déjà),
# on réinstalle les dépendances avant de démarrer PHP-FPM.
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] vendor/ manquant, exécution de composer install..."
    composer install --no-interaction --optimize-autoloader
fi

exec "$@"
