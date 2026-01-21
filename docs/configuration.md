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

## Laravel Configuration Files

The application uses standard Laravel configuration files in `config/`:

- `app.php` - General application settings
- `database.php` - Database connections
- `mail.php` - Email configuration
- `services.php` - Third-party services

## Träwelling Cross-Posting Configuration
To enable cross-posting to Träwelling, you'll need a Träwelling Application Key.

Go to your Träwelling [settings > Your Applications > Create Application](https://traewelling.de/settings/applications/create) and create a new application.

Your redirect URL should be set to `http://YOUR-DOMAIN-NAME/socialite/traewelling/callback` (for local development e.g.: `http://localhost/socialite/traewelling/callback`).

Make sure `confidential` is checked. Then click `create`.

Once you're back on the applications list, click the name of the application you just created. Copy the `Client Secret` and the `Client ID`.

Add the following lines to your `.env` file:

```
TRAEWELLING_CLIENT_ID=your_client_id
TRAEWELLING_CLIENT_SECRET=your_client_secret
```

Afterwards, you can go into your Reisetagebuch profile settings and connect your Träwelling account for cross-posting.
