#!/bin/bash
set -e

mkdir -p /var/www/storage/logs /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
mkdir -p /var/www/storage/framework/views
chown -R www-data:www-data /var/www/storage/framework/views
exec "$@"
