FROM php:8.3-fpm

ARG user=appuser
ARG uid=1000

# Atualiza e instala pacotes necessários
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    libpq-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libicu-dev \
    nano \
    libmagickwand-dev \
    libfreetype6-dev \
    postgresql-client \
    netcat-openbsd 

# Limpa cache do APT para reduzir tamanho da imagem
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip && \
    pecl install redis && \
    docker-php-ext-enable redis

# Copia o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia o php.ini para a configuração correta no container
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Cria usuário no sistema
RUN groupadd -g $uid $user && \
    useradd -u $uid -g $user -m -s /bin/bash $user && \
    chown -R $user:$user /var/www

WORKDIR /var/www
USER $user
