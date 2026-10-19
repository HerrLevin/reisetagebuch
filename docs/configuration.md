# Configuration

This page covers the configuration options available in Reisetagebuch.

## Environment Variables

### Application Settings

| Variable    | Description                     | Default          |
|-------------|---------------------------------|------------------|
| `APP_NAME`  | Application name                | Reisetagebuch    |
| `APP_ENV`   | Environment (local, production) | local            |
| `APP_KEY`   | Laravel application key         | Generated        |
| `APP_DEBUG` | Enable debug mode               | true in local    |
| `APP_URL`   | Base URL                        | http://localhost |

### Database Configuration

| Variable        | Description       | Example       |
|-----------------|-------------------|---------------|
| `DB_CONNECTION` | Database driver   | pgsql         |
| `DB_HOST`       | Database host     | 127.0.0.1     |
| `DB_PORT`       | Database port     | 5432          |
| `DB_DATABASE`   | Database name     | reisetagebuch |
| `DB_USERNAME`   | Database username | your_username |
| `DB_PASSWORD`   | Database password | your_password |

### External Services

| Variable        | Description   | Required |
|-----------------|---------------|----------|
| `MAIL_MAILER`   | Mail driver   | No       |
| `MAIL_HOST`     | SMTP host     | No       |
| `MAIL_PORT`     | SMTP port     | No       |
| `MAIL_USERNAME` | SMTP username | No       |
| `MAIL_PASSWORD` | SMTP password | No       |

### Registration & Invites

| Variable               | Description                                                                  | Default |
|------------------------|-------------------------------------------------------------------------------|---------|
| `APP_REGISTRATION`     | Allow open self-registration                                                  | `true`  |
| `APP_INVITE_ENABLED`   | Require an invite code to register (checked in addition to `APP_REGISTRATION`)| `false` |
| `APP_INVITE_WHITELIST` | Comma-separated email addresses/domains allowed to register without an invite | (empty) |

### Location & Transit Data Sources

Location check-ins and transport check-ins are resolved against external geo/routing services:

| Variable                           | Description                                                                 | Default                                     |
|-------------------------------------|------------------------------------------------------------------------------|----------------------------------------------|
| `APP_OVERPASS_URL`                   | Overpass API instance used to look up OSM locations                          | `https://overpass.private.coffee/api/interpreter` |
| `APP_OVERPASS_RADIUS`                | Search radius (meters) for nearby OSM locations                              | `1000`                                       |
| `APP_OVERPASS_CONNECT_TIMEOUT`       | Overpass connect timeout (seconds)                                           | `5`                                          |
| `APP_OVERPASS_REQUEST_TIMEOUT`       | Overpass request timeout (seconds)                                          | `30`                                         |
| `APP_MOTIS_API_URL`                  | [Transitous](https://transitous.org/)/[motis](https://github.com/motis-project/motis) API used for vehicle/trip check-ins | `https://api.transitous.org/api` |
| `APP_MOTIS_RADIUS`                   | Search radius (meters) for nearby stops/vehicles                             | `500`                                        |
| `APP_MOTIS_SINGLE_LOCATION_RADIUS`   | Search radius (meters) used when resolving a single stop                     | `100`                                        |
| `APP_NEARBY_RADIUS`                  | Search radius (meters) for the generic "nearby" lookup                       | `500`                                        |
| `APP_TIMEAPI_URL`                    | Self-hosted [geo2tz](https://github.com/noandrea/geo2tz) instance, used to resolve a location's timezone | `http://geo2tz:2004/tz` |
| `APP_TRANSIT_REFRESH_INTERVAL`       | Polling interval (seconds) for refreshing live trip data                     | `5`                                          |

### Active Trip Tracking

| Variable                        | Description                                                              | Default |
|----------------------------------|---------------------------------------------------------------------------|---------|
| `APP_ACTIVE_POST_GRACE_START`    | Minutes before a trip's departure it already counts as the active post    | `15`    |
| `APP_ACTIVE_POST_GRACE_END`      | Minutes after a trip's arrival it still counts as the active post         | `60`    |
| `APP_ACTIVE_POST_REPUSH_DAYS`    | Days after which a stale active-post push notification is resent          | `7`     |

### Dashboards & Testing

| Variable                     | Description                                                               | Default |
|-------------------------------|-----------------------------------------------------------------------------|---------|
| `TELESCOPE_ENABLED`           | Enable the [Laravel Telescope](https://laravel.com/docs/telescope) debug dashboard | `true` in local |
| `HORIZON_DASHBOARD_ENABLED`   | Enable the Horizon queue dashboard                                        | `true`  |
| `APP_TESTING_CYPRESS`         | Enable test-only endpoints/seeding used by the Cypress e2e suite — leave disabled in production | `false` |
| `APP_TESTING_CYPRESS_TOKEN`   | Shared secret required to call the Cypress test-only endpoints            | `testing` |

## Laravel Configuration Files

The application uses standard Laravel configuration files in `config/`:

- `app.php` - General application settings
- `database.php` - Database connections
- `mail.php` - Email configuration
- `services.php` - Third-party services

## Träwelling Cross-Posting Configuration
To enable cross-posting to Träwelling, you'll need a Träwelling Application Key.

Go to your Träwelling [settings > Your Applications](https://traewelling.de/settings/applications) and create a new application.

Your redirect URL should be set to `http://YOUR-DOMAIN-NAME/socialite/traewelling/callback` (for local development e.g.: `http://localhost/socialite/traewelling/callback`).

Make sure `confidential` is checked. Then click `create`.

Once you're back on the applications list, click the name of the application you just created. Copy the `Client Secret` and the `Client ID`.

Add the following lines to your `.env` file:

```
TRAEWELLING_CLIENT_ID=your_client_id
TRAEWELLING_CLIENT_SECRET=your_client_secret
```

Two more variables exist for non-default setups and can usually be left unset:

| Variable                     | Description                                                                          | Default                                                     |
|-------------------------------|----------------------------------------------------------------------------------------|--------------------------------------------------------------|
| `TRAEWELLING_REDIRECT_URI`    | Overrides the OAuth callback URL                                                       | `{APP_URL}/socialite/traewelling/callback`                   |
| `TRAEWELLING_BASE_URI`        | Träwelling instance to authenticate against, for self-hosted/alternate instances        | `https://traewelling.de`                                      |

Afterwards, you can go into your Reisetagebuch profile settings and connect your Träwelling account for cross-posting.
