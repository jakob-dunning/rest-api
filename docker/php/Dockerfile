FROM php:8.3-fpm-alpine
COPY install_composer.sh /
RUN chmod +x /install_composer.sh \
    && /install_composer.sh \
    && rm /install_composer.sh \
    && mv composer.phar /usr/bin/composer
RUN apk update && apk upgrade
RUN apk add --no-cache --update $PHPIZE_DEPS linux-headers libpq-dev bash icu-dev \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug
RUN docker-php-ext-install pdo_pgsql pgsql intl
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash \
    && apk add symfony-cli