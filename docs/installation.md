# Installation

This guide will help you set up your own instance of Reisetagebuch.

## System Requirements

- Docker and Docker Compose (optional, for containerized setup)

## Docker Compose Setup

Reisetagebuch is published as a [Docker image](https://hub.docker.com/r/herrlevin/reisetagebuch), making it easy to deploy using Docker Compose.

The easiest way to get started is by using Docker Compose.

This is the recommended way to run Reisetagebuch, as it handles all dependencies including the database.

The `rtb.prod` service serves the web application and runs the scheduler. Queued jobs are processed separately by
[Laravel Horizon](https://laravel.com/docs/horizon), running in its own `horizon` service so it can be scaled
independently of the web server. 
You can find a docker compose sample, ready to copy, here:
[docker-compose.example.yml](https://github.com/HerrLevin/reisetagebuch/blob/main/docker-compose.example.yml)

::: tip
You can run multiple Horizon workers by scaling the `horizon` service, e.g.:
```bash
docker compose up -d --scale horizon=3
```
Each replica registers itself as its own Horizon "master supervisor" in Redis, so they coordinate automatically
and won't double-process jobs. On first deploy, run migrations explicitly before starting workers, since
`depends_on`'s healthcheck only waits for `rtb.prod`'s web server to respond, not for its migrations to finish:
```bash
docker compose run --rm rtb.prod php artisan migrate
```
:::

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
