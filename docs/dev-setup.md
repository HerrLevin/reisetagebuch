# Local Development Setup for Reisetagebuch

This guide will help you set up your own instance of Reisetagebuch.

### Prerequisites
The dev-setup of Reisetagebuch uses [Laravel Sail](https://laravel.com/docs/12.x/sail) for local development.
Ensure you have the following installed:
- PHP >= 8.1
- Composer
- Node.js and npm
- Docker

### 1. Clone the Repository

```bash
git clone https://github.com/herrlevin/reisetagebuch.git
cd reisetagebuch
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the environment file and configure it:

```bash
cp .env.example .env
```

Edit `.env` with your database and other settings:

```env
APP_NAME=Reisetagebuch
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Start the Sail Environment

```bash
./vendor/bin/sail up -d
```


### 7. Migrate and Seed the Database (Optional)

For development, you can seed with sample data:

```bash
./vendor/bin/sail artisan migrate --seed
```

### 8. Create Passport Keys

```bash
./vendor/bin/sail artisan passport:keys
```

### 9. Build Assets

```bash
npm run build
```

You can also run the following for continuous development:
```bash
npm run dev
```

Visit [http://localhost](http://localhost) to see the application.

::: info
Laravel Telescope is enabled in local environment for debugging. Configure in `config/telescope.php`.
You can access it at [http://localhost/telescope](http://localhost/telescope).
:::

## Testing

Run the test suite:

```bash
./vendor/bin/sail artisan test
```

## Queue Workers
To start queue workers, run:

```bash
./vendor/bin/sail artisan queue:work
```

## Next Steps

- [Configure the application](./configuration.md)
