FROM php:8.2-apache

# MySQL kiterjesztés telepítése a PHP-hoz
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Az Apache rewrite modul engedélyezése a .htaccess szabályokhoz
RUN a2enmod rewrite

# Munkakönyvtár beállítása a konténeren belül
WORKDIR /var/www/html

# Jogosultságok megadása a fájlokhoz
RUN chown -R www-data:www-data /var/www/html

# Apache konfiguráció módosítása: AllowOverride All engedélyezése
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf