#!/usr/bin/env bash
php artisan config:cache
php artisan migrate:fresh --force --seed
php artisan storage:link
php artisan serve --host=0.0.0.0 --port=$PORT
