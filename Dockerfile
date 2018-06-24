FROM php:7.2.6-fpm-alpine

ADD public/* /var/www/html/

VOLUME /var/www/html


