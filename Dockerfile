FROM php:8-alpine

LABEL org.opencontainers.image.source="https://github.com/smartassert/runner-delegator"

ARG proxy_server_version=0.5

WORKDIR /app

COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock phpunit.run.xml /app/
COPY bin /app/bin
COPY src /app/src

RUN apk --no-cache add libzip-dev \
    && docker-php-ext-install pcntl sockets zip \
    && composer check-platform-reqs --ansi \
    && composer install --prefer-dist --no-dev \
    && composer clear-cache \
    && curl https://raw.githubusercontent.com/webignition/docker-tcp-cli-proxy/${proxy_server_version}/composer.json --output composer.json \
    && curl https://raw.githubusercontent.com/webignition/docker-tcp-cli-proxy/${proxy_server_version}/composer.lock --output composer.lock \
    && composer check-platform-reqs --ansi \
    && rm composer.json \
    && rm composer.lock \
    && rm /usr/bin/composer \
    && curl -L https://github.com/webignition/docker-tcp-cli-proxy/releases/download/${proxy_server_version}/server.phar --output ./server \
    && chmod +x ./server

CMD ./server
