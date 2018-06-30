FROM php:7.2.6-fpm-alpine3.7

RUN set -xe \
    && apk add --no-cache postgresql-libs \
    && apk add --no-cache --virtual .build-deps postgresql-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql \
    && yes no | pecl install redis \
    && pecl install msgpack \
    && apk del .build-deps \
    && rm -rf /tmp/pear ~/.pearrc \
    && docker-php-ext-enable redis msgpack pdo_pgsql

COPY public/* /var/www/html/
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

VOLUME /var/www/html

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
