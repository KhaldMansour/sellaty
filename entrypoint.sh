#!/bin/bash

# Make sure the target exists (inside mounted volume)
mkdir -p storage/app/public

# Recreate the symlink safely
rm -f public/storage
php artisan storage:link

# Start the app
php-fpm
