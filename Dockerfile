# PHP 8.2 FPM — paridade com produção Hostinger
FROM php:8.2-fpm

# ── Build args (permite UID/GID do host no WSL2) ─────────────────
ARG UID=1000
ARG GID=1000

# ── Dependências do sistema ───────────────────────────────────────
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libgmp-dev \
    libicu-dev \
    libtidy-dev \
    libsodium-dev \
    libmagickwand-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# ── Extensões PHP ─────────────────────────────────────────────────
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    gmp \
    tidy \
    soap \
    sockets \
    opcache \
    xml \
    dom \
    simplexml \
    xmlwriter \
    xmlreader

# ── Imagick (via PECL) ────────────────────────────────────────────
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# ── Composer ─────────────────────────────────────────────────────
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ── Usuário com UID/GID do host ──────────────────────────────────
RUN groupmod -g ${GID} www-data \
    && usermod -u ${UID} -g www-data www-data \
    && mkdir -p /var/www/.composer \
    && chown -R www-data:www-data /var/www

# ── Entrypoint ────────────────────────────────────────────────────
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
