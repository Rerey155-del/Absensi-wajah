FROM php:8.2-apache

# Aktifkan mod_rewrite untuk Laravel
RUN a2enmod rewrite

# Install ekstensi PHP yang dibutuhkan Laravel
RUN docker-php-ext-install pdo pdo_mysql

# Copy semua file project ke direktori web server Apache
COPY . /var/www/html

# Ubah permission agar Apache bisa akses file Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Ganti DocumentRoot jadi ke folder `public` Laravel
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Expose port default Apache
EXPOSE 80
