FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
        libxml2-dev \
        oniguruma-dev \
        curl \
    && docker-php-ext-install \
        mbstring \
        simplexml \
        json \
    && rm -rf /var/cache/apk/*

WORKDIR /app
COPY . .

RUN mkdir -p cache && chmod 777 cache
