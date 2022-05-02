FROM ghcr.io/sparkfabrik/docker-php-base-image:8.0.8-fpm-alpine3.13-rootless

USER root

ENV PHP_MEMORY_LIMIT 128M
ENV COMPOSER_VERSION 2.3.5
ENV COMPOSER_MEMORY_LIMIT -1

RUN curl -L -o /usr/local/bin/composer https://github.com/composer/composer/releases/download/${COMPOSER_VERSION}/composer.phar
RUN chmod +x /usr/local/bin/composer

COPY ./composer* /var/www/html/
COPY src/ /var/www/html/src

RUN composer install --no-interaction

WORKDIR /var/www/html

USER 1001
ENTRYPOINT ["./src/gitlab-actions"]
