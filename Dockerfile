# Imagen base: Apache + PHP 8.2
FROM php:8.2-apache

# Instala la extensión mysqli (necesaria para conectarse a MySQL)
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Habilita mod_rewrite de Apache (útil para URLs limpias a futuro)
RUN a2enmod rewrite
RUN echo "AddDefaultCharset UTF-8" >> /etc/apache2/apache2.conf

# Configura Apache para permitir .htaccess en el directorio web
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# El código se monta como volumen desde docker-compose,
# pero copiamos todo igual para que funcione como imagen standalone
COPY . /var/www/html/

# Permisos correctos para Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
