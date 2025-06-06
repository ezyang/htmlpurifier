FROM ubuntu:24.04

ARG DEBIAN_FRONTEND=noninteractive

ENV PHP_VERSION="8.4"
ENV LANG en_US.UTF-8
ENV LC_ALL en_US.UTF-8

RUN apt update -y && apt -y install git curl locales doxygen software-properties-common

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    locale-gen en_US.UTF-8 && \
    update-locale LANG=en_US.UTF-8 \

RUN echo -y | add-apt-repository ppa:ondrej/php && apt update -y

RUN apt install -y  \
    php${PHP_VERSION}  \
    php${PHP_VERSION}-dev \
    php${PHP_VERSION}-xdebug \
    php${PHP_VERSION}-iconv  \
    php${PHP_VERSION}-bcmath  \
    php${PHP_VERSION}-tidy \
    php${PHP_VERSION}-xml

RUN echo "xdebug.mode=debug,coverage" >> /etc/php/${PHP_VERSION}/cli/php.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /opt/htmlpurifier
