# Stage 1: Build dependencies
FROM php:8.2-apache as build

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libmariadb-dev \
    unzip zip \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    git

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd zip bcmath intl

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies and optimize
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Final Stage
FROM php:8.2-apache

# Install runtime dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libmariadb-dev \
    libpng-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql gd zip bcmath intl

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy built application
COPY --from=build /var/www/html /var/www/html

# Configure Apache for Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Build Swagger docs during build
RUN php artisan l5-swagger:generate

# Render uses the PORT environment variable
# We configure Apache to listen on the port provided by Render
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Optimization and cache
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Expose port (Render will override this with PORT env var)
EXPOSE 80

CMD ["apache2-foreground"]
