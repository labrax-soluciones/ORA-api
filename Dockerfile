FROM php:8.3-fpm

# Paquetes del sistema
RUN apt-get update && apt-get install -y \
    git unzip zip curl nano \
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    libzip-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Opcional: Xdebug (comenta si no lo necesitas)
# RUN pecl install xdebug \
#  && docker-php-ext-enable xdebug

WORKDIR /var/www

# Copia (no afectará si montas volumen)
COPY . /var/www

# Permisos Laravel
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && find /var/www/storage /var/www/bootstrap/cache -type d -exec chmod 775 {} \; \
    && find /var/www/storage /var/www/bootstrap/cache -type f -exec chmod 664 {} \;

# php.ini recomendado (ajustes simples)
RUN { \
    echo "memory_limit=${PHP_MEMORY_LIMIT:-512M}"; \
    echo "upload_max_filesize=20M"; \
    echo "post_max_size=21M"; \
    echo "max_execution_time=60"; \
    echo "date.timezone=Europe/Madrid"; \
    } > /usr/local/etc/php/conf.d/zz-aparca.ini

# Exponer puerto FPM
EXPOSE 9000

CMD ["php-fpm"]
