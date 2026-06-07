FROM php:8.4-apache

WORKDIR /var/www
ENV DEBIAN_FRONTEND=noninteractive

ARG WWW_ROOT="/var/www"
ENV WWW_ROOT=$WWW_ROOT

# Updating packages and installing dependencies in a single layer with cache cleaning
RUN apt-get update -y --allow-insecure-repositories && \
    apt-get install -y --no-install-recommends \
    git \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpng-dev \
    libzip-dev \
    rsync \
    unzip \
    sudo \
    zip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Installing PHP extensions with cache cleaning
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    gd \
    intl \
    mbstring \
    opcache \
    pdo_mysql \
    zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installation of Symfony CLI with cache cleaning
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | sudo -E bash && \
    apt-get install -y symfony-cli && \
    symfony server:ca:install && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure Apache
ADD docker/apache/entrypoint.sh /entrypoint.sh
RUN chmod a+x /entrypoint.sh && \
    a2enmod rewrite remoteip ssl

CMD ["/entrypoint.sh"]

EXPOSE 80