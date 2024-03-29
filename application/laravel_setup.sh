#!/bin/bash

echo "Setting permissions for /var/www/html/storage and /var/www/html/bootstrap/cache..."

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Permissions set."

echo "Installing composer dependencies"

cd /var/www/html
composer install

echo "Generating application key"

php artisan key:generate

echo "Running database migrations"

php artisan migrate

echo "Seeding database"

php artisan db:seed

echo "Link Storage"

php artisan storage:link

exec php-fpm