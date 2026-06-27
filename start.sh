#!/usr/bin/env bash
mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs bootstrap/cache
php artisan config:cache
php artisan migrate:fresh --force --seed
php artisan storage:link
php artisan serve --host=0.0.0.0 --port=$PORT
