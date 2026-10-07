FROM php:8.5-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
        git \
        unzip \
    && docker-php-ext-install pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite \
    && sed -i 's|AllowOverride None|AllowOverride All|' /etc/apache2/apache2.conf \
    && sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/app/public|' /etc/apache2/sites-available/000-default.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

RUN composer create-project --no-interaction --prefer-dist laravel/laravel app

WORKDIR /var/www/app

COPY overlay/ ./

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

CMD ["/entrypoint.sh"]