FROM php:8.3-fpm-alpine

# pdo_mysql is not bundled with the official image; unzip lets Composer unpack dist archives.
RUN apk add --no-cache unzip \
    && docker-php-ext-install pdo_mysql

# The image ships without an active php.ini (display_errors=On, logging off).
# The production template hides errors from users and logs them to stderr; app.ini adds our overrides.
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY docker/php/app.ini "$PHP_INI_DIR/conf.d/zz-app.ini"

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
