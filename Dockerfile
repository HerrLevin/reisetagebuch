# Stage 1: Build dependencies
FROM composer:latest AS builder
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader

# Stage 2: Build PHP application
FROM dunglas/frankenphp:1-php8.5-alpine

# Set environment variables for runtime user and group IDs
ARG RUNTIME_UID=33
ARG RUNTIME_GID=33

# Install required system dependencies
RUN apk add --no-cache \
    bash \
    icu-dev \
    libpq-dev \
    libzip-dev \
    logrotate \
    nodejs \
    npm \
    oniguruma-dev \
    shadow \
    sqlite-dev \
    supervisor \
    zlib-dev

# Install required PHP extensions
RUN install-php-extensions \
        bcmath \
        intl \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        zip

RUN groupmod --gid ${RUNTIME_GID} www-data \
    && usermod --uid ${RUNTIME_UID} --gid ${RUNTIME_GID} www-data

# Allow FrankenPHP to bind to port 80 as a non-root user
RUN setcap cap_net_bind_service=+ep /usr/local/bin/frankenphp

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
  && chown -R ${RUNTIME_UID}:${RUNTIME_GID} /config/caddy /data/caddy

# Exclude SQLite database file
RUN rm -f /var/www/html/database/database.sqlite

# Environment variables
ARG APP_VERSION=0.0.0
ENV APP_VERSION=${APP_VERSION}
ENV SERVER_NAME=localhost

# Copy configuration files
COPY docker/caddy/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/logrotate/supervisord /etc/logrotate.d/supervisord
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN chown -R ${RUNTIME_UID}:${RUNTIME_GID} .
USER ${RUNTIME_UID}:${RUNTIME_GID}

# Expose ports
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD wget --no-verbose --tries=1 --spider http://localhost/ || exit 1

CMD ["/entrypoint.sh"]
