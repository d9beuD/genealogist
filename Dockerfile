FROM dunglas/frankenphp:1-php8.5-trixie AS base

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends rsync && \
	rm -rf /var/lib/apt/lists/*

ARG USER=appuser

# Common setup for dev and prod
RUN \
	useradd ${USER}; \
	setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
	chown -R ${USER}:${USER} /config/caddy /data/caddy

# Add PHP extensions here if needed
RUN install-php-extensions \
	intl \
    pdo_mysql \
    zip

# Composer for PHP dependencies
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

FROM base AS dev

# Development php.ini
RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

# Symfony CLI needs a writable config/cache home
RUN mkdir -p /home/${USER}/.config/symfony-cli/cache /home/${USER}/.cache && \
	chown -R ${USER}:${USER} /home/${USER}

ENV HOME=/home/${USER} \
	XDG_CONFIG_HOME=/home/${USER}/.config \
	XDG_CACHE_HOME=/home/${USER}/.cache

# Symfony CLI only for dev
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash && \
    apt-get install -y symfony-cli && \
    symfony server:ca:install && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

USER ${USER}

FROM base AS prod

# Production php.ini
RUN cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini

COPY composer.json composer.lock symfony.lock* /app/
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

COPY . /app

USER ${USER}
