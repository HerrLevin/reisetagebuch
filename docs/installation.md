# Installation

This guide will help you set up your own instance of Reisetagebuch.

## Prerequisites

- Docker with Docker Compose v2
- A domain and a reverse proxy (like traefik) that terminates HTTPS (see [Reverse Proxy](#reverse-proxy))
- A directory for your instance containing two files:
  - `docker-compose.yml`: copy [docker-compose.example.yml](https://github.com/HerrLevin/reisetagebuch/blob/main/docker-compose.example.yml)
  - `.env`: copy [.env.prod.example](https://github.com/HerrLevin/reisetagebuch/blob/main/.env.prod.example)

You don't need to create any other directories. Storage, database and Redis data live in Docker-managed volumes
that are created on the first start.

::: details Local data folders
If you'd rather keep the data in folders next to your `docker-compose.yml` (e.g. for backups), use the commented-out
`volumes` block at the end of the compose file instead of the default one. The folders must exist before the first
start, and `storage` must be writable by the container user (uid 33):

```bash
mkdir -p ./app/storage ./pgsql ./redis
sudo chown -R 33:33 ./app/storage
```
:::

## Configure `.env`

You **must** change these values in `.env` before the first start:

| Variable                                   | What to set                                                                                                                          |
|--------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------|
| `APP_KEY`                                  | Generate with `echo "base64:$(openssl rand -base64 32)"`. Keep it, because changing it later invalidates sessions and encrypted data. |
| `APP_URL`                                  | The public `https://` URL your reverse proxy serves, e.g. `https://your-domain.tld`                                                   |
| `DB_PASSWORD`                              | A strong password. It is only applied when the database is first created, so set it before the first start.                           |
| `REDIS_PASSWORD`                           | A strong password instead of `changeme`                                                                                                 |
| `MAIL_*`                                   | Your SMTP server, so invite and notification mails can be sent                                                                          |

All other options are described in [Configuration](./configuration.md).

## Docker Compose Setup

Reisetagebuch is published as a [Docker image](https://hub.docker.com/r/herrlevin/reisetagebuch), and Docker Compose
is the recommended way to run it, since it handles all dependencies including the database.

The `rtb.prod` service serves the web application and runs the scheduler. Queued jobs are processed separately by
[Laravel Horizon](https://laravel.com/docs/horizon), running in its own `horizon` service so it can be scaled
independently of the web server. Both wait until Postgres has finished its initial setup and Redis is ready.

Start the application with:

```bash
docker compose up -d
```

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

::: details Testing locally without a reverse proxy
Uncomment the `ports` mapping of `rtb.prod` in `docker-compose.yml` and set these in `.env`:

```dotenv
APP_ENV=local
APP_URL=http://localhost
```

With `APP_ENV=production` or an `https://` `APP_URL`, all links are forced to HTTPS, which breaks plain HTTP access.
Don't use this for a public instance.
:::

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
