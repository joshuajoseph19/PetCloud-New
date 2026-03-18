FROM php:8.2-apache

# Install PDO MySQL extension for Aiven connection
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache Mod Rewrite for .htaccess and pretty URLs
RUN a2enmod rewrite

# Copy project files into the Apache document root
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/

# Expose port 80
EXPOSE 80
