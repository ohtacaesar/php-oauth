FROM php:7.2.6-fpm-alpine3.7

RUN set -xe \
    && apk add --no-cache postgresql-libs yaml \
    && apk add --no-cache --virtual .build-deps postgresql-dev yaml-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql \
    && yes no | pecl install redis \
    && pecl install msgpack yaml \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin \
    && php -r "unlink('composer-setup.php');" \
    && apk del .build-deps \
    && rm -rf /tmp/pear ~/.pearrc \
    && docker-php-ext-enable redis msgpack yaml pdo_pgsql

WORKDIR /app
VOLUME  /app

COPY ./ /app
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN composer.phar install

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
