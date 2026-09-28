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

## Reverse Proxy

The `rtb.prod` container does not publish any ports and does not terminate TLS itself, so it isn't reachable from
outside Docker by default. You need a reverse proxy (Traefik, Nginx, Caddy, etc.) in front of it to:

- Terminate HTTPS. ActivityPub federation (HTTP Signatures, WebFinger) requires your instance to be reachable over
  valid HTTPS, not plain HTTP.
- Forward traffic to `rtb.prod` on port 80.

Set `APP_URL` in your `.env` to the public `https://` URL the proxy serves before starting the stack. Also set
`TRUSTED_PROXIES=*` so Laravel trusts the `X-Forwarded-*` headers from your proxy and sees the real client IP and
scheme instead of the proxy's &mdash; this is safe since `rtb.prod` publishes no ports and is only reachable through
the proxy.

::: details Traefik example
Put `rtb.prod` on the same Docker network as Traefik and add labels:

```yaml
services:
    rtb.prod:
        networks:
            - traefik
        labels:
            - traefik.enable=true
            - traefik.http.routers.rtb.rule=Host(`your-domain.tld`)
            - traefik.http.routers.rtb.entrypoints=websecure
            - traefik.http.routers.rtb.tls.certresolver=letsencrypt
            - traefik.http.services.rtb.loadbalancer.server.port=80

networks:
    traefik:
        external: true
```
:::

::: details Nginx example
```nginx
server {
    listen 443 ssl;
    server_name your-domain.tld;

    ssl_certificate     /etc/letsencrypt/live/your-domain.tld/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.tld/privkey.pem;

    location / {
        proxy_pass http://rtb.prod:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```
:::

## Next Steps

- [Configure the application](./configuration.md)
- [Create your own development setup](./dev-setup.md)
