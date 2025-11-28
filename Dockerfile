# Imagen base con PHP 8.2 y extensiones mínimas
FROM php:8.2-cli

# Instalar dependencias del sistema necesarias para Laravel + MySQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

# Copiar el código de la aplicación
COPY . .

# Instalar dependencias PHP (sin dev)
RUN composer install --no-dev --optimize-autoloader

# Exponer el puerto 10000 (el que Render usa para Docker web services)
EXPOSE 10000

# Comando de arranque: servidor embebido de Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
