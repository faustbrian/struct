FROM php:8.5-cli
WORKDIR /usr/src/myapp

ARG INSTALL_XDEBUG=false

RUN apt-get update && apt-get install zip unzip git curl -y \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && if [ "$INSTALL_XDEBUG" = "true" ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer --2.7 \
    && mkdir /.composer && chmod 777 /.composer

USER nobody
