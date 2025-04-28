# Use official PHP image with Apache
FROM php:8.2-apache

# Set Apache ServerName and document root
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

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

# Copy custom php.ini (ensure it doesn't conflict with extensions)
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

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