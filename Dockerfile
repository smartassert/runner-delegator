ARG php_version=8.1

FROM php:${php_version}-cli-alpine

LABEL org.opencontainers.image.source="https://github.com/smartassert/runner-delegator"

ARG proxy_server_version=0.8
ARG php_version

WORKDIR /app

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY composer.json phpunit.run.xml /app/
COPY bin /app/bin
COPY src /app/src

RUN apk --no-cache add libzip-dev \
    && docker-php-ext-install pcntl sockets zip \
    && composer install --prefer-dist --no-dev \
    && composer clear-cache \
    && curl -L https://raw.githubusercontent.com/webignition/tcp-cli-proxy-server/${proxy_server_version}/composer.json --output composer.json \
    && curl -L https://github.com/webignition/tcp-cli-proxy-server/releases/download/${proxy_server_version}/composer-${php_version}.lock --output composer.lock \
    && composer check-platform-reqs --ansi \
    && rm composer.json \
    && rm composer.lock \
    && rm /usr/bin/composer \
    && curl -L https://github.com/webignition/tcp-cli-proxy-server/releases/download/${proxy_server_version}/server-${php_version}.phar --output ./server \
    && chmod +x ./server

CMD ./server
