#!/bin/sh

echo ""
echo "***********************************************************"
echo " Starting NGINX PHP-FPM Docker Container                   "
echo "***********************************************************"

set -e
set -e
info() {
    { set +x; } 2> /dev/null
    echo '[INFO] ' "$@"
}
warning() {
    { set +x; } 2> /dev/null
    echo '[WARNING] ' "$@"
}
fatal() {
    { set +x; } 2> /dev/null
    echo '[ERROR] ' "$@" >&2
    exit 1
}

## Run Composer install
cd /var/www/html && rm -f composer.lock | true && composer install

# npm run build

php artisan storage:link

# php artisan reverb:start

# Set ownership and permissions for the log file
info "Setting log file ownership and permissions..."
chown -R www-data:www-data storage/app/public
touch /var/www/html/storage/logs/laravel.log
chown www-data:www-data /var/www/html/storage/logs/laravel.log
chmod 775 /var/www/html/storage/logs/laravel.log

php artisan optimize

## Start Supervisord
supervisord -c /etc/supervisor/supervisord.conf
