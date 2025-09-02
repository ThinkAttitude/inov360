FROM php:8.1-apache

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite (optional but common)
RUN a2enmod rewrite

# Install Composer (optional)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
