# Stage 1: Build dependencies
FROM php:8.5-cli AS builder
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
# composer needs unzip to extract dist packages; php:*-cli doesn't ship it
RUN apt-get update && apt-get install -y --no-install-recommends unzip && rm -rf /var/lib/apt/lists/*
WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-gd --ignore-platform-req=ext-pcntl

# Stage 2: Build frontend assets (Node is only needed at build time)
FROM node:25-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
# tailwind.config.js scans Laravel's pagination views
COPY --from=builder /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# Stage 3: Build PHP application
FROM dunglas/frankenphp:1-php8.5-trixie

# Set environment variables for runtime user and group IDs
ARG RUNTIME_UID=33
ARG RUNTIME_GID=33

# Install required system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    bash \
    libcap2-bin \
    logrotate \
    supervisor \
    osmium-tool \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN install-php-extensions \
    bcmath \
    gd \
    intl \
    pcntl \
    pdo_pgsql \
    pdo_sqlite \
    redis \
    zip

RUN groupmod --gid ${RUNTIME_GID} www-data \
    && usermod --uid ${RUNTIME_UID} --gid ${RUNTIME_GID} www-data

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy dependencies from builder stage
COPY --from=builder /app/vendor ./vendor

# Copy built frontend assets
COPY --from=assets /app/public/build ./public/build

# Set permissions
# Non-root FrankenPHP needs the capability to bind port 80 and write access to Caddy's data/config dirs
RUN install -d -o ${RUNTIME_UID} -g ${RUNTIME_GID} -m 775 /var/www/html/storage /var/www/html/bootstrap/cache \
  && setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
  && chown -R ${RUNTIME_UID}:${RUNTIME_GID} /config/caddy /data/caddy

# Exclude SQLite database file
RUN rm -f /var/www/html/database/database.sqlite

# Copy configuration files
COPY docker/php/php.ini $PHP_INI_DIR/conf.d/zz-app.ini
COPY docker/frankenphp/Caddyfile /etc/frankenphp/Caddyfile
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/logrotate/supervisord /etc/logrotate.d/supervisord
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN chown -R ${RUNTIME_UID}:${RUNTIME_GID} .
USER ${RUNTIME_UID}:${RUNTIME_GID}

# Set last so a new version doesn't invalidate the cached layers above
ARG APP_VERSION=0.0.0
ENV APP_VERSION=${APP_VERSION}

# Expose ports
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -fsS -o /dev/null http://localhost/up || exit 1

CMD ["/entrypoint.sh"]
