# Pixmoree

Pixmoree is a Laravel 12 digital art marketplace with accounts, artwork editions, profiles, engagement, simulated purchases, receipts, notifications, and creator earnings. NFT purchases and creator withdrawals are simulations only. The site does not charge or transfer real money, connect to GCash or a payment gateway, or use blockchain tokens. Pixel Coin and its top-up feature have been removed.

## Requirements

- PHP 8.2 or newer with `pdo_mysql` enabled; `pdo_sqlite` is used by the isolated feature suite
- Composer

On Windows, install Composer with `C:\xampp\php\php.exe` as its PHP executable.

## Run locally

Start MySQL from the XAMPP Control Panel and create a local database. In phpMyAdmin, run:

```sql
CREATE DATABASE pixmoreedb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

From the project directory in PowerShell:

```powershell
$env:PATH = 'C:\xampp\php;' + $env:PATH
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
composer install
if (-not (Select-String -Path .env -Pattern '^APP_KEY=.+')) { & 'C:\xampp\php\php.exe' artisan key:generate }
& 'C:\xampp\php\php.exe' artisan migrate --seed
& 'C:\xampp\php\php.exe' artisan storage:link
.\start-server.bat
```

If `.env` already exists, keep its database and mail settings. The command above only creates it when missing and generates an application key only when `APP_KEY` is blank.

After setup, you can start the app by double-clicking `start-server.bat` or running `.\start-server.bat` from the project directory. It checks for PHP with `pdo_mysql` and a reachable local MySQL server before starting Laravel, so a stopped database is reported immediately instead of making the first page request wait and fail. Keep the XAMPP Control Panel open with MySQL running.

Open <http://127.0.0.1:8000/>. Register at `/register`; the signed-in Store is at `/store` (`/week12` redirects there). The Store requires an account. Admin routes at `/admin`, `/week12/admin`, and `/week12/admin/artworks` require an account promoted to admin.

### Payment and withdrawal simulations

Checkout records a simulated PHP purchase, transfers one edition item to the buyer, creates a receipt, and adds the same demonstration amount to the seller's simulated earnings balance. Resale prices are calculated from the current simulated market estimate when the seller submits the listing; sellers cannot enter a price. Ownership review uses the seller's purchase and transfer history instead of an uploaded evidence file. Resale purchases have their own receipt and are labeled in purchase history. Creators can simulate withdrawing up to their recorded balance from the Earnings page. A withdrawal updates the in-app ledger and creates a receipt. Neither flow contacts a payment provider, requests personal payment details, or moves money.

Keep `PAYMENTS_ENABLED=true` and `PAYMENTS_SIMULATION=true` in `.env` for the local demo. `PAYMENTS_PHP_PER_ETH` is a demonstration conversion for artwork asking prices. These values do not represent money owed, charged, or paid. Top-ups and Pixel Coin purchases are no longer available.

The migration `2026_11_22_000001_remove_pixel_coin_wallet` deletes old Pixel Coin balances, wallet ledger rows, coin-related order fields, and obsolete manual-payment settings and proof fields. If an existing database contains those legacy simulation or payment-setup records, back it up before running `php artisan migrate`; old coin balances are not converted into PHP earnings.

### Share the development site on a local network

This makes the development server reachable to devices on the same trusted Wi-Fi or LAN. It does not publish the site to the internet. `artisan serve` is not a production web server.

1. Connect the host and other device to the same network. Run `ipconfig` on Windows and note the host's active IPv4 address.
2. Start Laravel from the project directory:

   ```powershell
   & 'C:\xampp\php\php.exe' artisan serve --host=0.0.0.0 --port=8000
   ```

3. Open `http://<HOST-IP>:8000/` on the other device, replacing `<HOST-IP>` with the host's address. Do not use `127.0.0.1` on the other device.
4. If Windows Firewall blocks the connection, allow inbound TCP port 8000 on Private networks only. Remove the firewall rule when the demo is over. Do not configure router port forwarding for this development server.

## Accounts and password reset

Registration signs the new account in immediately; email verification is not required. Members sign in with email or username and password. Social sign-in is disabled. In local development, password reset generates a temporary link on the page and sends no email. Outside local development, reset links expire after 60 minutes and require a configured SMTP provider to arrive in a real inbox. Changing a password from profile settings updates it directly after confirming the current password and does not send email. Optional welcome emails use the same mail configuration. Never commit SMTP credentials.

New passwords, including reset and profile password changes, must be at least six characters and include an uppercase letter, a number, and a special character.

To promote an existing account to admin, register it normally and run:

```powershell
& 'C:\xampp\php\php.exe' artisan pixmoree:make-admin admin@example.com
```

The command does not set or expose a password. No shared demo credentials are seeded or committed.

## Database

Laravel migrations define the current schema. For the local MySQL database, run:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixmoreedb
DB_USERNAME=root
DB_PASSWORD=
DB_FOREIGN_KEYS=true
```

Then run `php artisan migrate --seed`. The default seeder does not create demo users or artwork. Supplied images are in `public/assets/nfts` and `public/assets/creators`.

The standard feature suite uses an in-memory SQLite database. For a dedicated MySQL feature run, create a separate `pixmoree_test` database and use `phpunit.mysql.xml`; that suite refreshes its configured test database. Never point tests at a database with user data.

Users can update their name, email, phone, bio, and profile image in `/user/profile-settings`. Each new account receives an in-app welcome notification. Users can view and mark their notifications as read from `/user/notifications`. Uploaded images are stored on Laravel's public disk; run `php artisan storage:link` after setup.

Admins can manage users, search and filter artwork by title, price, release date, creator, and resale status, update listing prices, review resale purchase receipts, inspect transaction records and analytics, and edit website content from `/week12/admin`. Simulated transactions complete automatically; there is no payment approval screen. Resale approval publishes a listing and is not a copyright determination.

Marketplace cards show creator asking prices and a separate simulated market estimate. The estimate refreshes every minute without reloading the page; Buy buttons on Discover cards go directly to checkout, while clicking elsewhere on a card opens artwork details. Checkout displays the simulated PHP amount using `PAYMENTS_PHP_PER_ETH`. The member dashboard shows estimated artwork values and daily gainers/decliners. These estimates are not completed sale prices. Run Laravel's scheduler so `artworks:prices:snapshot` can preserve each day's simulated values as history.

## Production readiness

This repository is configured for local development. Production hosting, HTTPS, a managed database, backups, SMTP, and operational review need separate setup. Before hosting, set `APP_ENV=production`, `APP_DEBUG=false`, a production `APP_URL`, and a secret `APP_KEY`; point the web server document root to `public/`; and keep storage and `bootstrap/cache` writable. Keep payments in simulation mode. Do not connect simulated purchase or withdrawal routes to a live provider without a separately reviewed implementation.

## Documentation

- [Website audit and verification notes](WEBSITE_AUDIT.md)
- [Project diagrams and ERD](docs/PROJECT_DIAGRAMS.md)
- [Client and admin demo manual](docs/DEMO_MANUAL.md)

## Tests

Run the SQLite feature suite with:

```powershell
php artisan test --testsuite=Feature
```

## Project structure

- `routes/web.php` contains Laravel web routes.
- `app/Http/Controllers` contains account, marketplace, admin, purchase, and withdrawal flows.
- `resources/views` contains guest, account, checkout, receipt, and Admin Studio views.
- `public/assets`, `public/css`, and `public/js` contain site assets and browser scripts.
- `database/migrations` and `database/seeders` define the active schema and default seed data. Older migration files remain as history for existing installations.

For Apache deployment, configure the document root to the project's `public` directory.
