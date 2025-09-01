FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    curl \
    netcat-openbsd \
    supervisor \
    cron \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    gd \
    bcmath \
    mbstring \
    exif \
    pcntl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure PHP
RUN echo 'LoadModule php_module /usr/lib/apache2/modules/libphp.so\nAddHandler application/x-httpd-php .php\nDirectoryIndex index.php' > /etc/apache2/mods-available/php.conf

# Configure Apache for Laravel
RUN echo '<Directory /var/www/html/public>\n    Options Indexes FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>' > /etc/apache2/conf-available/laravel.conf

# Set document root to public directory
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Enable Laravel configuration
RUN a2enconf laravel

WORKDIR /var/www/html

# Copy application files first
COPY . /var/www/html/

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
