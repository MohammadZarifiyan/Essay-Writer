# Use the official PHP Apache image as the base
FROM php:8.4-apache

# Replace default directory references in Apache configurations
ENV APACHE_DOCUMENT_ROOT /var/www/public

RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf
RUN sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable Apache requierd modules
RUN a2enmod rewrite
RUN a2enmod negotiation

# Install system dependencies & build libraries
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
      curl \
      unzip \
      git \
      libpq-dev \
      libonig-dev \
      libssl-dev \
      libxml2-dev \
      libcurl4-openssl-dev \
      libicu-dev \
      libzip-dev \
      libfcgi-bin \
      procps \
      supervisor \
      nodejs \
      npm \
    # Install php dependencies \
    && docker-php-ext-install -j$(nproc) \
      pcntl \
      pdo_mysql \
      opcache \
      intl \
      zip \
      bcmath \
      exif \
    && pecl install redis \
    && docker-php-ext-enable redis \
    # Cleanup \
    && apt-get autoremove -y \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Use the recommended production PHP configuration
# -----------------------------------------------------------
# PHP provides development and production configurations.
# Here, we replace the default php.ini with the production
# version to apply settings optimized for performance and
# security in a live environment.
# -----------------------------------------------------------
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy supervisor configs
COPY docker/production/app/supervisor.conf /etc/supervisor/conf.d/app.conf

# Copy the entrypoint script
COPY --chmod=+x docker/production/app/entrypoint.sh /usr/local/bin/entrypoint.sh

# Install Supercronic
RUN curl -fsSLo /usr/local/bin/supercronic https://github.com/aptible/supercronic/releases/download/v0.2.39/supercronic-linux-amd64
RUN chmod +x /usr/local/bin/supercronic

# Set the working directory
WORKDIR /var/www

# Change working dorectory ownership to www-data
RUN chown -R www-data:www-data /var/www

# Copy project files
COPY --chown=www-data:www-data . /var/www

# Switch to non-root user
USER www-data

# Install Composer dependencies, Node.js dependencies and build frontend assets
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist \
  && npm ci \
  && npm run build \
  && npm prune --production

# Switch back to root
USER root

# Set custom entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Expose Apache port & start Supervisor to manage services
EXPOSE 80
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/app.conf"]
