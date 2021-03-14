FROM php:8.0-fpm-alpine
#https://github.com/docker-library/php
ENV PHP_CONFIG="/usr/local/bin/php-config"

RUN echo 'Set disable_coredump false' >> /etc/sudo.conf

COPY installer/docker-php-pecl-install.sh /usr/local/bin/docker-php-pecl-install
RUN chmod +x /usr/local/bin/docker-php-pecl-install

COPY bin /in_container
RUN chmod +x /in_container/* -R

COPY php.ini "${PHP_INI_DIR}/"

RUN apk add --no-cache --update  \
    sudo \
    curl \
    bash \
    grep \
    git \
    perl \
    gnu-libiconv \
    && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

########################################################################################################################
ENV INTL_DEPS="icu"
ENV INTL_BUILD_DEPS="icu-dev"
########################################################################################################################
ENV GMP_DEPS="gmp"
ENV GMP_BUILD_DEPS="gmp-dev"

RUN sed -e 's/# %wheel ALL=(ALL) NOPASSWD: ALL/%wheel ALL=(ALL) NOPASSWD: ALL/g' -i /etc/sudoers && \
    sed -e 's/^wheel:\(.*\)/wheel:\1,www-data/g' -i /etc/group

RUN set -xe && \
    echo "install build-deps" && \
    apk add --no-cache --update \
    $INTL_DEPS \
    $GMP_DEPS \
    && \
    apk add --no-cache --update --virtual .phpize-deps $PHPIZE_DEPS && \
    apk add --no-cache --update --virtual .intl-deps $INTL_BUILD_DEPS && \
    apk add --no-cache --update --virtual .gmp-deps $GMP_BUILD_DEPS && \
    export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" && \
    echo "install php extensions" && \
    printf "\n" | docker-php-pecl-install \
        intl \
        gmp \
    && \
    rm -rf /tmp/* && \
    rm -rf /usr/include/* && \
    apk del .phpize-deps .intl-deps .gmp-deps

RUN rm -f /etc/php-fpm.conf

ADD php-fpm.conf /etc/php-fpm.conf

EXPOSE 9000

WORKDIR /var/www

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
