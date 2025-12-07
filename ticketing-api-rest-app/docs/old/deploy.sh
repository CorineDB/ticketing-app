#!/usr/bin/env bash
set -e

echo "Starting deployment..."

# Install composer dependencies
echo "Installing composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install npm dependencies and build assets
echo "Installing npm dependencies..."
npm ci

echo "Building frontend assets..."
npm run build

# Create database file if using SQLite (only for local dev)
if [ ! -f "database/database.sqlite" ] && [ "$DB_CONNECTION" = "sqlite" ]; then
    echo "Creating SQLite database..."
    touch database/database.sqlite
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Cache configuration and routes for better performance
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if it doesn't exist
if [ ! -L "public/storage" ]; then
    echo "Creating storage link..."
    php artisan storage:link
fi

echo "Deployment completed successfully!"
