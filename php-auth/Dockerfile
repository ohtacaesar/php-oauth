FROM php:7.2.6-fpm-alpine3.7

COPY ./ /app

WORKDIR /app

RUN set -xe \
    && apk add --no-cache postgresql-libs yaml \
    && apk add --no-cache --virtual .build-deps nodejs-npm postgresql-dev yaml-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql \
    && yes no | pecl install redis \
    && pecl install msgpack yaml \
    && rm -rf /tmp/pear ~/.pearrc \
    && docker-php-ext-enable redis msgpack yaml pdo_pgsql \
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin \
    && php -r "unlink('composer-setup.php');" \
    && composer.phar install \
    && npm install \
    && npm run webpack \
    && rm -rf node_modules \
    && apk del .build-deps

COPY entrypoint.sh /usr/local/bin/entrypoint.sh

COPY www.conf /usr/local/etc/php-fpm.d/www.conf

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]
