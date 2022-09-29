FROM php:7.3.11-fpm

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    build-essential \
    iputils-ping \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    libzip-dev \
    libxml2-dev \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    nano \
    git \
    curl

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring zip exif pcntl
RUN docker-php-ext-configure gd --with-gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/
RUN docker-php-ext-install gd
RUN docker-php-ext-install -j$(nproc) xmlrpc

# SSH
RUN mkdir ~/.ssh
RUN echo 'IdentityFile ~/.ssh/id_rsa' > ~/.ssh/config
RUN ssh-keyscan -t rsa github.com >> /root/.ssh/known_hosts

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install nodejs
RUN curl -sL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y \
    nodejs

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]
