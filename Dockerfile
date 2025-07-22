# Stage 1: Build dependencies
FROM composer:latest AS builder
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

# Stage 2: Build PHP application
FROM php:8.4-fpm-alpine

# Set environment variables for runtime user and group IDs
ARG RUNTIME_UID=33
ARG RUNTIME_GID=33
ARG PHP_LOG_DIR="/var/log/php"
ARG NGINX_LOG_DIR="/var/log/nginx"
ARG NGINX_LIB_DIR="/var/lib/nginx"

# Install required system dependencies
RUN apk add --no-cache \
    bash \
    icu-dev \
    libpq-dev \
    libzip-dev \
    nginx \
    nodejs \
    npm \
    oniguruma-dev \
    shadow \
    sqlite-dev \
    supervisor \
    zlib-dev

# Install required PHP extensions
RUN docker-php-ext-install \
        bcmath \
        intl \
        opcache \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        zip

RUN groupmod --gid ${RUNTIME_GID} www-data \
    && usermod --uid ${RUNTIME_UID} --gid ${RUNTIME_GID} www-data

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy dependencies from builder stage
COPY --from=builder /app/vendor ./vendor

# Build frontend assets
RUN npm install
RUN npm run build

# Set permissions
RUN set -e \
  && install -d -o ${RUNTIME_UID} -g ${RUNTIME_GID} -m 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    $NGINX_LIB_DIR/logs $NGINX_LIB_DIR/tmp /run/nginx \
  && install -d -o ${RUNTIME_UID} -g ${RUNTIME_GID} -m 755 /var/run/php $PHP_LOG_DIR $NGINX_LOG_DIR $NGINX_LIB_DIR \
    /run/nginx \
  && touch $PHP_LOG_DIR/php-fpm.log $PHP_LOG_DIR/php-fpm.err $NGINX_LOG_DIR/error.log $NGINX_LOG_DIR/access.log \
  && chown ${RUNTIME_UID}:${RUNTIME_GID} $PHP_LOG_DIR/php-fpm.log $PHP_LOG_DIR/php-fpm.err $NGINX_LOG_DIR/error.log \
    $NGINX_LOG_DIR/access.log \
  && chmod 664 $PHP_LOG_DIR/php-fpm.log $PHP_LOG_DIR/php-fpm.err $NGINX_LOG_DIR/error.log $NGINX_LOG_DIR/access.log

# Exclude SQLite database file
RUN rm -f /var/www/html/database/database.sqlite

# Environment variables
ENV SERVER_NAME={HOSTNAME}

# Copy configuration files
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN chown -R ${RUNTIME_UID}:${RUNTIME_GID} .
USER ${RUNTIME_UID}:${RUNTIME_GID}

# Expose ports
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://localhost/ || exit 1

CMD ["/entrypoint.sh"]
