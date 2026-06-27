# Deployment

## Production environment

Copy `.env.example` to `.env`, then set production values at minimum:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_TIMEZONE=Asia/Manila
LOG_LEVEL=warning
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
SEED_USER_PASSWORD=a-strong-unique-password
SEED_ADMIN_EMAIL=your-admin@example.com
SEED_DEMO_DATA=false
SEED_SERVICE_CATALOG=false
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
FILESYSTEM_DISK=local
```

Never enable `SEED_DEMO_DATA` in production. `DemoSeeder` and `AppointmentSeeder` also refuse to run when `APP_ENV=production`. Keep `SEED_SERVICE_CATALOG=false` after initial setup so deployments cannot overwrite service prices edited in the application.

## Release commands

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize
```

Configure the web root to `public/`, make `storage/` and `bootstrap/cache/` writable, use HTTPS, and run a queue worker if queued notifications are enabled.

Configure a real production mail driver and `MAIL_FROM_ADDRESS`. If SMS completion notifications are required, also configure `SEMAPHORE_API_KEY` and `SEMAPHORE_SENDER_NAME`.

## Test/demo data

On a non-production database only:

```bash
php artisan db:seed --class=DemoSeeder
```

The demo seed is idempotent and includes duplicate names, returning clients, different statuses, multiple staff assignments, and paid invoices.
