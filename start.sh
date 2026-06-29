#!/usr/bin/env bash
set -e
mkdir -p storage/framework/views storage/framework/cache/data storage/framework/sessions storage/logs bootstrap/cache
npm ci
npx vite build
php artisan config:cache
php artisan migrate --force
php artisan storage:link
php artisan serve --host=0.0.0.0 --port=$PORT
