# Use official PHP image with Apache
FROM php:8.2-apache

# Set Apache ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Install system dependencies and PostgreSQL client library
RUN apt-get update && apt-get install -y \
    libpq-dev \
    postgresql-client \
    && rm -rf /var/lib/apt/lists/*

# Install and enable PHP extensions for PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql && \
    docker-php-ext-enable pdo_pgsql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy custom php.ini (if you have it)
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Set working directory (optional, Apache will still serve properly without this)
WORKDIR /var/www/html

# Copy application files
COPY public/ /var/www/html/

# Fix ownership and permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod -R 755 /var/www/html/uploads

# Expose port 80
EXPOSE 80
