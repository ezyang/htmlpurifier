FROM ubuntu:22.04

ARG DEBIAN_FRONTEND=noninteractive

ENV PHP_VERSION="8.3"
ENV LANG en_US.UTF-8
ENV LC_ALL en_US.UTF-8

RUN apt update -y && apt -y install git curl locales doxygen

RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    locale-gen en_US.UTF-8 && \
    update-locale LANG=en_US.UTF-8 \

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt install -y software-properties-common && add-apt-repository ppa:ondrej/php && apt update -y

RUN apt  install -y  \
    php${PHP_VERSION}  \
    php${PHP_VERSION}-dev \
    php${PHP_VERSION}-xdebug \
    php${PHP_VERSION}-iconv  \
    php${PHP_VERSION}-bcmath  \
    php${PHP_VERSION}-tidy

RUN echo "xdebug.mode=debug,coverage" >> /etc/php/${PHP_VERSION}/cli/php.ini

WORKDIR /opt/htmlpurifier
