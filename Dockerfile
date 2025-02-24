FROM php:8.1-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

# Installation des dépendances et outils nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    git \
    curl \
    && docker-php-ext-install zip pdo pdo_mysql opcache

# Configuration d'Apache
RUN a2enmod rewrite

# Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY src/ /var/www/html/
COPY composer.json /var/www/html/

RUN composer install --no-scripts --no-autoloader
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data /var/www/html

RUN mkdir -p /var/www/logs && \
    chown -R www-data:www-data /var/www/logs && \
    chmod 755 /var/www/logs

# Configuration d'Apache pour servir depuis /var/www/html
RUN sed -i 's!/var/www/html!/var/www/html!g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80

CMD ["apache2-foreground"]
