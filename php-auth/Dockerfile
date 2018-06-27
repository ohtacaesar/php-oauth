FROM php:7.2.6-fpm-alpine3.7

RUN set -xe \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && yes no | pecl install redis \
    && pecl install msgpack \
    && apk del .build-deps \
    && rm -rf /tmp/pear ~/.pearrc \
    && echo extension=redis.so > /usr/local/etc/php/conf.d/redis.ini \
    && echo extension=msgpack.so > /usr/local/etc/php/conf.d/msgpack.ini

COPY public/* /var/www/html/
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

VOLUME /var/www/html

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
