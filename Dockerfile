FROM php:8.3-apache

# 1. Instalar dependencias del sistema y extensiones de PHP necesarias (PDO MySQL)
RUN apt-get update && apt-get install -y \
    ca-certificates \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql gd \
    && rm -rf /var/lib/apt/lists/*

# 2. Habilitar mod_rewrite y configurar pase de variables de entorno en Apache
RUN a2enmod rewrite \
    && echo "PassEnv DB_HOST DB_PORT DB_NAME DB_USER DB_PASS DB_SSL DB_SSL_CA" > /etc/apache2/conf-available/docker-env.conf \
    && a2enconf docker-env

# 3. Copiar el código fuente de la aplicación al directorio web
COPY . /var/www/html/

# 4. Asegurar que la carpeta de almacenamiento de fotos tenga permisos de escritura para Apache
RUN mkdir -p /var/www/html/archivos \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/archivos

# 5. Adaptar el puerto de escucha dinámico asignado por Render ($PORT) o 80 por defecto
CMD sed -i "s/80/${PORT:-80}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && apache2-foreground

EXPOSE 80
