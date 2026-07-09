# --- Image PHP-FPM pour le backend Symfony ---
FROM php:8.3-fpm-alpine

# Dépendances système + extensions PHP nécessaires à Symfony/Doctrine :
#   - pdo_pgsql / pgsql : driver PostgreSQL
#   - intl              : requis par Symfony (formatage, validation)
#   - zip / opcache     : performances et gestion des paquets composer
RUN apk add --no-cache \
        bash \
        git \
        icu-dev \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        intl \
        zip \
        opcache

# Configuration OPcache adaptée au dev (revalidation à chaque requête)
RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.validate_timestamps=1'; \
        echo 'opcache.revalidate_freq=0'; \
    } > /usr/local/etc/php/conf.d/opcache-custom.ini

# Composer, copié depuis son image officielle
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copie du code puis installation des dépendances. En dev, le bind mount du
# docker-compose écrase ensuite ce répertoire, mais vendor/ et var/ restent
# dans des volumes dédiés afin de conserver ce qui a été installé ici.
COPY . .

RUN composer install --prefer-dist --no-progress --no-interaction --optimize-autoloader \
    && mkdir -p var/cache var/log \
    && chown -R www-data:www-data var

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
