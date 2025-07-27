FROM php:8.3-cli

# ----------------------------------------
# 📌 ENV : Compilation safe & extensible
# ----------------------------------------
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=production \
    DEBIAN_FRONTEND=noninteractive

# ----------------------------------------
# 📦 Dépendances système & PHP extensions
# ----------------------------------------
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    zip \
    libzip-dev \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    pkg-config \
    gcc \
    g++ \
    make \
    redis-tools \
    supervisor \
    && docker-php-ext-install zip pdo pdo_pgsql pcntl sockets

# ----------------------------------------
# 🧠 Installation manuelle de Swoole sans Brotli
# ----------------------------------------
RUN git clone https://github.com/swoole/swoole-src.git /usr/src/swoole && \
    cd /usr/src/swoole && \
    phpize && \
    ./configure \
        --enable-openssl \
        --enable-http2 \
        --enable-sockets \
        --enable-mysqlnd \
        --enable-json \
        --disable-brotli && \
    make -j$(nproc) && make install && \
    echo "extension=swoole.so" > /usr/local/etc/php/conf.d/docker-php-ext-swoole.ini && \
    rm -rf /usr/src/swoole

# ----------------------------------------
# 🧰 Composer
# ----------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ----------------------------------------
# 📂 Copie du code
# ----------------------------------------
WORKDIR /var/www
COPY . .

# ----------------------------------------
# 🛠 Préparation Laravel + Octane
# ----------------------------------------
RUN composer install --no-interaction --optimize-autoloader && \
     php artisan octane:install --server=swoole --no-interaction && \
     php artisan vendor:publish --tag=octane-config --force && \
     php artisan config:cache && \
     php artisan route:cache

# ----------------------------------------
# 🔁 Supervision Horizon/Queue (optionnel)
# ----------------------------------------
COPY ./docker/supervisord.conf /etc/supervisord.conf

# ----------------------------------------
# 🔥 Exposition & démarrage Octane
# ----------------------------------------
EXPOSE 8000
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000", "--no-reload"]
