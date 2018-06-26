FROM php:7.2.6-fpm-alpine

RUN set -xe \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && yes no | pecl install redis \
    && pecl install msgpack \
    && apk del .build-deps \
    && rm -rf /tmp/pear ~/.pearrc \
    && echo extension=redis.so > /usr/local/etc/php/conf.d/redis.ini \
    && echo extension=msgpack.so > /usr/local/etc/php/conf.d/msgpack.ini

ADD public/* /var/www/html/

VOLUME /var/www/html
