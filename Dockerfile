# Use official PHP image with Apache
FROM php:8.2-apache

# Install necessary PHP extensions for PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy custom php.ini
COPY docker/php.ini /usr/local/etc/php/conf.d/

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY public/ /var/www/html/

# Create uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

# Expose port 80
EXPOSE 80
