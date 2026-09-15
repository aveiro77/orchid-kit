FROM php:8.3.0-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    libssl-dev \
    libicu-dev \
    libbz2-dev \
    libreadline-dev \
    libsqlite3-dev \
    libldap2-dev \
    libmagickwand-dev \
    libmagickcore-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache \
    mysqli \
    pdo \
    && docker-php-ext-enable opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js 20 (Opsional jika sudah ada container node terpisah)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Make storage and bootstrap/cache directories fully writable for Laravel
# Uses 0777 to ensure both host user (1000) and container FPM user (www-data) can write
RUN chmod -R 0777 storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]