#!/bin/bash

# Wait for storage folder to mount (optional small delay)
sleep 2

# Run Laravel setup tasks
php artisan storage:link || echo "Storage link already exists"

# Start PHP FPM
php-fpm
