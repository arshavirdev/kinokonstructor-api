FROM php:8.1.12-fpm-alpine3.16

# Setting up system dependencies
RUN set -x && \
    apk update && \
    apk upgrade && \
    apk add --no-cache --virtual phpize $PHPIZE_DEPS && \
    apk add --no-cache \
    freetype freetype-dev libpng libpng-dev libjpeg-turbo libjpeg-turbo-dev libwebp libwebp-dev libxpm libxpm-dev \
    oniguruma-dev zip libzip-dev libmcrypt-dev icu-dev libxml2-dev libpq-dev && \
# Install nginx
    addgroup -g 101 -S nginx && \
    adduser -S -D -H -u 101 -h /var/cache/nginx -s /sbin/nologin -G nginx -g nginx nginx && \
    apk add --no-cache nginx && \
# Install php extensions
    pecl install ds redis && \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm --with-webp && \
    docker-php-ext-install bcmath exif gd intl opcache pcntl pdo_pgsql pgsql sockets zip && \
    docker-php-ext-enable opcache ds redis && \
# Removing build-time deps
    apk del --no-cache phpize freetype-dev libpng-dev libjpeg-turbo-dev libwebp-dev libxpm-dev && \
# Linking logs to system
    ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stderr /var/log/nginx/error.log

ARG INSTALL_XDEBUG=0f

RUN if [ "$INSTALL_XDEBUG" = 1 ]; then \
    apk add --no-cache --virtual phpize $PHPIZE_DEPS git && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.mode=off" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.max_nesting_level=1500" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    git clone https://github.com/NoiseByNorthwest/php-spx.git && \
    cd php-spx && \
    git checkout release/latest && \
    phpize && \
    ./configure && \
    make && \
    make install && \
    touch "${PHP_INI_DIR}/conf.d/20-spx.ini" && \
    echo "extension=$(find /usr/local/lib/php/extensions/ -name spx.so)" >"${PHP_INI_DIR}/conf.d/20-spx.ini" && \
    echo "spx.http_enabled=1" >>"${PHP_INI_DIR}/php.ini" && \
    echo 'spx.http_key="dev"' >>"${PHP_INI_DIR}/php.ini" && \
    echo 'spx.http_ip_whitelist="*"' >>"${PHP_INI_DIR}/php.ini" && \
    apk del --no-cache phpize && \
    cd ../ && \
    rm -rf ./php-spx && \
    rm -rf /var/cache/apk; \
    fi

# Setting up composer
COPY --from=composer:2.4.3 /usr/bin/composer /usr/local/bin/composer
RUN alias composer='php /usr/bin/composer'

# Overriding default php.ini
COPY docker/php-fpm-override.ini /usr/local/etc/php/conf.d/php-fpm-override.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/vhost.conf /etc/nginx/conf.d/default.conf

RUN chown -R 82:82 /var/www /var/log /var/lib/nginx /var/run /run && \
    chmod -R 755 /var/lib/nginx
RUN mkdir -p /var/run/
WORKDIR /var/www/html

COPY --chown=82:82 composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist && \
    rm -rf /home/www-data/.composer/cache

COPY --chown=82:82 . .
RUN composer dump-autoload

EXPOSE 8000
STOPSIGNAL SIGTERM
CMD chmod -R 777 storage && \
    /usr/local/bin/php ./artisan storage:link && /usr/local/bin/php ./artisan migrate --force && \
    /usr/sbin/nginx; /usr/local/sbin/php-fpm -F
