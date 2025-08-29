FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    netcat-openbsd \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/docker-php.conf && \
    a2enconf docker-php

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && find /var/www/html -name "*.php" -exec chmod 644 {} \; 2>/dev/null || true \
    && find /var/www/html -name "*.html" -exec chmod 644 {} \; 2>/dev/null || true \
    && find /var/www/html -name "*.css" -exec chmod 644 {} \; 2>/dev/null || true \
    && find /var/www/html -name "*.js" -exec chmod 644 {} \; 2>/dev/null || true

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
