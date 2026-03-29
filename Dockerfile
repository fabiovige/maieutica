# PHP 8.2 FPM — paridade com produção Hostinger
FROM php:8.2-fpm

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
    nodejs \
    npm \
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

# ── Usuário não-root ──────────────────────────────────────────────
RUN groupmod -g 1000 www-data \
    && usermod -u 1000 -g www-data www-data

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
