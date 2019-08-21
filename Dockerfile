FROM php:7.2.6-fpm-alpine3.7

ARG ALPINE_SERVER=""
ARG NPM_PROXY=""
ARG COMPOSER_URL
ARG COMPOSER_HASH=""

WORKDIR /app

COPY composer.* ./
COPY package* ./
COPY webpack.config.js ./
COPY src/js ./src/js
COPY src/css ./src/css
COPY public/ ./public

RUN set -eux \
    &&  if [[ -n "${ALPINE_SERVER}" ]]; then \
          sed -i "s/dl-cdn.alpinelinux.org/${ALPINE_SERVER}/" /etc/apk/repositories; \
        fi \
    &&  apk add --no-cache postgresql-libs \
    &&  apk add --no-cache --virtual .build-deps nodejs-npm postgresql-dev $PHPIZE_DEPS \
    &&  docker-php-ext-install pdo_pgsql \
    &&  wget  -O /usr/local/bin/composer.phar ${COMPOSER_URL} \
    &&  chmod +x /usr/local/bin/composer.phar \
    &&  if [[ -n "${COMPOSER_HASH}" ]]; then \
          echo "${COMPOSER_HASH}  /usr/local/bin/composer.phar" > /tmp/hash; \
          sha256sum -c /tmp/hash; \
        fi \
    &&  composer.phar install --no-dev \
    &&  if [[ -n "${NPM_PROXY}" ]]; then \
          npm config set proxy       $NPM_PROXY; \
          npm config set https-proxy $NPM_PROXY; \
          npm config set strict-ssl  false; \
        fi \
    &&  npm install \
    &&  npm run webpack \
    &&  rm -rf node_modules \
    &&  apk del .build-deps \
    &&  mkdir logs

COPY src/ ./src
COPY bin/ ./bin
COPY tests/ ./tests
COPY .php_cs.dist ./
COPY phpunit.xml ./
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
COPY www.conf /usr/local/etc/php-fpm.d/www.conf

RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

CMD ["php-fpm"]
