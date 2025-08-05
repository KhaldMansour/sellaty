FROM php:8.3-apache
WORKDIR /var/www/html

# Mod Rewrite
RUN a2enmod rewrite

# COPY .env.example .env

COPY . /var/www/html

# Linux Library
RUN apt-get update -y && apt-get install -y \
    libicu-dev \
    libmariadb-dev \
    unzip zip \
    zlib1g-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    curl \
    gnupg

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP Extension
RUN docker-php-ext-install gettext intl pdo_mysql pcntl gd

RUN curl -sSL https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-linux-amd64-v0.6.1.tar.gz | tar -xz -C /usr/local/bin

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

RUN apt-get remove -y nodejs npm || true
RUN apt-get autoremove -y && apt-get autoclean -y

# Add NodeSource repository (without sudo)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash -

# Install Node.js
RUN apt-get install -y nodejs

# Copy application files
COPY . /var/www/html

# Set proper permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && find storage -type d -exec chmod 775 {} \; \
    && find bootstrap/cache -type d -exec chmod 775 {} \;

# Configure sysctl settings for file watchers
RUN echo 'fs.inotify.max_user_watches=524288' >> /etc/sysctl.conf \
    && echo 'fs.inotify.max_user_instances=512' >> /etc/sysctl.conf

# Copy the custom Apache config
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80 6001
