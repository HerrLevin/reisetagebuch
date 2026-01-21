# Installation

This guide will help you set up your own instance of Reisetagebuch.

## System Requirements

- Docker and Docker Compose (optional, for containerized setup)

## Docker Compose Setup

Reisetagebuch is published as a [Docker image](https://hub.docker.com/r/herrlevin/reisetagebuch), making it easy to deploy using Docker Compose.

The easiest way to get started is by using Docker Compose.

This is the recommended way to run Reisetagebuch, as it handles all dependencies including the database.

This is a sample `docker-compose.yml` file you can use:
```yaml
services:
    rtb.prod:
        restart: always
        image: herrlevin/reisetagebuch:main
        env_file: .env
        volumes:
            - ./app/public:/var/www/html/storage/app/public # Persist uploaded files
        depends_on:
            - pgsql
            - redis

    pgsql:
        restart: always
        platform: 'linux/amd64' # Force x86_64 architecture for Postgres, because they don't support arm64 yet
        image: 'postgis/postgis:17-master'
        environment:
            PGPASSWORD: '${DB_PASSWORD:-secret}'
            POSTGRES_DB: '${DB_DATABASE}'
            POSTGRES_USER: '${DB_USERNAME}'
            POSTGRES_PASSWORD: '${DB_PASSWORD:-secret}'
        ports:
            - '5432:5432'
        volumes:
            - ./pgsql:/var/lib/postgresql/data
        healthcheck:
            test:
                - CMD
                - pg_isready
                - '-q'
                - '-d'
                - '${DB_DATABASE}'
                - '-U'
                - '${DB_USERNAME}'
            retries: 3
            timeout: 5s

    redis:
        restart: always
        image: 'redis:alpine'
        volumes:
            - ./redis.conf:/etc/redis/redis.conf
            - ./redis:/data
        command: [ "redis-server", "/etc/redis/redis.conf" ]
        networks:
            - rtb-prod
        healthcheck:
            test:
                - CMD
                - redis-cli
                - ping
            retries: 3
            timeout: 5s
```

You'll also need a `.env` file with your configuration. You can use the provided `.env.example` as a starting point.

You can get it here: [.env.prod.example](https://github.com/HerrLevin/reisetagebuch/blob/main/.env.prod.example)

You can start the application with:

```bash
docker-compose up -d
```

::: warning
You'll still need some kind of reverse proxy (like Nginx or Traefik) to handle HTTPS and route traffic to the `rtb.prod` service.
:::

## Next Steps

- [Configure the application](./configuration.md)
- [Create your own development setup](./dev-setup.md)
