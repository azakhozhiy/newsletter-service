# Используем официальный образ PHP с поддержкой FPM
FROM php:8.2-fpm

# Устанавливаем необходимые пакеты, включая Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Копируем исходные файлы проекта в контейнер
COPY ./ /src/

# Копируем конфигурацию Nginx
COPY ./docker/nginx.conf /etc/nginx/nginx.conf

WORKDIR /src

# Устанавливаем зависимости через Composer
RUN composer install  --optimize-autoloader

# Устанавливаем права доступа к файлам
RUN chown -R www-data:www-data /src

# Добавляем скрипт для запуска Nginx и PHP-FPM
COPY ./docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Указываем начальную точку для контейнера
ENTRYPOINT ["/entrypoint.sh"]
