FROM php:8.2-fpm

# 1. Installer les dépendances système, Nginx ET ImageMagick
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    libpq-dev \
    libmagickwand-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# 2. Installer Redis et Imagick via PECL
RUN pecl install redis imagick \
    && docker-php-ext-enable redis imagick

# 3. Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Configurer le répertoire de travail
WORKDIR /var/www/html

# 5. Copier les fichiers du projet
COPY . .

# 6. Installer les dépendances Laravel (Prod)
# UTILISATION DE 'update' pour générer le lock file si nécessaire + installation de ImageMagick pour le code
RUN composer update --no-interaction --prefer-dist --optimize-autoloader --no-dev

# 7. Configurer les permissions (pour que Nginx/PHP puisse écrire dans storage)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 8. Copier la config Nginx et le script de démarrage
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/startup.sh /usr/local/bin/startup.sh

# 9. Rendre le script exécutable
RUN chmod +x /usr/local/bin/startup.sh

# 10. Commande par défaut (pour le service Web)
CMD ["/usr/local/bin/startup.sh"]
