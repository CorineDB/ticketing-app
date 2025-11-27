# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm \
    postgresql-client \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Install npm dependencies and build assets
RUN npm ci && npm run build

# Create SQLite database file if it doesn't exist (fallback)
RUN touch /var/www/html/database/database.sqlite || true

# Configure Apache
RUN a2enmod rewrite headers
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Configure PHP for production
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Expose port (Render will provide PORT env variable)
EXPOSE ${PORT:-80}

# Create entrypoint script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
# Wait for database to be ready\n\
until pg_isready -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}"; do\n\
  echo "Waiting for database..."\n\
  sleep 2\n\
done\n\
\n\
echo "Database is ready!"\n\
\n\
# Run migrations\n\
php artisan migrate --force --no-interaction\n\
\n\
# Cache configuration\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
# Create storage link if it does not exist\n\
php artisan storage:link || true\n\
\n\
# Update Apache port configuration\n\
sed -i "s/Listen 80/Listen ${PORT:-80}/" /etc/apache2/ports.conf\n\
sed -i "s/:80/:${PORT:-80}/" /etc/apache2/sites-available/000-default.conf\n\
\n\
# Start Apache in foreground\n\
apache2-foreground\n\
' > /entrypoint.sh && chmod +x /entrypoint.sh

# Run entrypoint
CMD ["/entrypoint.sh"]
