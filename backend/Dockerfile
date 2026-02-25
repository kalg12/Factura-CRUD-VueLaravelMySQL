FROM php:8.4-cli

# Extensiones para Laravel + MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar el código del proyecto
COPY . .

# Instalar dependencias
RUN composer install

# Comando para levantar el servidor de desarrollo de Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
