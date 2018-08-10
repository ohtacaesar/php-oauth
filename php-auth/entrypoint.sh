#!/bin/sh

#env | grep SESSION | sed 's/_/./' | tr [A-Z] [a-z] > /usr/local/etc/php/conf.d/session.ini

php init_db.php

set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- php-fpm "$@"
fi

exec "$@"
