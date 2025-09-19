# Dockerfile
FROM php:8.3-fpm

# Paquetes del sistema y extensiones
RUN apt-get update && apt-get install -y \
    git unzip zip curl nano \
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    libzip-dev libonig-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" gd pdo_mysql zip \
 && rm -rf /var/lib/apt/lists/*

# Opcional: Xdebug
# RUN pecl install xdebug && docker-php-ext-enable xdebug

WORKDIR /var/www

# Copiamos entrypoint
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# (Opcional) Copiar composer.json para cachear dependencias en builds sin bind-mount
# COPY composer.json composer.lock /var/www/
# RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
#  && composer install --no-dev --prefer-dist --no-interaction || true

# php.ini recomendado
RUN { \
  echo "memory_limit=${PHP_MEMORY_LIMIT:-512M}"; \
  echo "upload_max_filesize=20M"; \
  echo "post_max_size=21M"; \
  echo "max_execution_time=60"; \
  echo "date.timezone=Europe/Madrid"; \
} > /usr/local/etc/php/conf.d/zz-aparca.ini

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
