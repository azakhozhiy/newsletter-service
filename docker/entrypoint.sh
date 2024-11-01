#!/bin/bash

# Запускаем PHP-FPM
php-fpm &

# Запускаем Nginx
nginx -g "daemon off;"