FROM php:8.1.0-apache

WORKDIR /var/www/html

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Instalar Composer manualmente
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv composer.phar /usr/local/bin/composer

# Copiar archivos relevantes si existen
 COPY ./negocio/composer.json .
 COPY ./negocio/.htaccess .

# Instalar herramientas adicionales
RUN apt-get update && apt-get install -y zip git

# Instalar dependencias de Composer
RUN composer install
RUN composer dump-autoload

# Configurar DocumentRoot a /public
RUN sed -i 's#DocumentRoot.*#DocumentRoot /var/www/html/public#' /etc/apache2/sites-enabled/000-default.conf # Cambiar DocumentRoot