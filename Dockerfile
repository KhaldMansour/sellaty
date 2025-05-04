FROM php:8.3-apache
WORKDIR /var/www/html

# Mod Rewrite
RUN a2enmod rewrite

COPY .env.example .env

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
    libpng-dev

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP Extension
RUN docker-php-ext-install gettext intl pdo_mysql pcntl gd

RUN curl -sSL https://github.com/jwilder/dockerize/releases/download/v0.6.1/dockerize-linux-amd64-v0.6.1.tar.gz | tar -xz -C /usr/local/bin

RUN docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# RUN chown -R www-data:www-data /var/www/html
# RUN chown -R www-data:www-data storage/app/public

# Set proper permissions for Laravel
RUN chown -R www-data:www-data /var/www/html \
    && find storage -type d -exec chmod 775 {} \; \
    && find bootstrap/cache -type d -exec chmod 775 {} \;

# Laravel storage link (optional, you can do it in entrypoint too)
RUN php artisan storage:link || true

RUN sudo apt remove nodejs npm

# Clean up
RUN apt autoremove
RUN apt autoclean

# Add NodeSource repository
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
RUN apt-get install -y nodejs
RUN apt-get install -y npm

# RUN apt-get install -y nodejs
# RUN npm install -g npm@latest

# Copy the custom Apache config
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80 6001

