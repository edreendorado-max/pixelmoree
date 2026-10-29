# Pixmoree

Pixmoree is a Laravel 12 application for the guest-facing marketplace prototype and its Week 12 artwork screens. User registration, login, profile details, uploaded images, and artwork listings are stored in MySQL. Payments and third-party social sign-in are not implemented.

## Requirements

- PHP 8.2 or newer with `pdo_mysql` enabled (included with the XAMPP PHP installation)
- Composer
- MySQL Server (the XAMPP MySQL service works)

On Windows, install Composer using the official Composer installer and select `C:\xampp\php\php.exe` as the PHP executable.

## Run locally

From the project directory in PowerShell:

```powershell
$env:PATH = 'C:\xampp\php;' + $env:PATH
Copy-Item .env.example .env
composer install
& 'C:\xampp\php\php.exe' artisan key:generate
& 'C:\xampp\php\php.exe' artisan migrate --seed
php artisan serve
```

Before running migrations, update `.env` with the MySQL connection values below. Adding XAMPP's PHP directory to the front of `PATH` ensures `php artisan serve` launches the same PHP runtime as the Artisan commands. Open <http://127.0.0.1:8000/> for the guest homepage. Registration is at `/register`; profile and image management is at `/user/profile-settings`. The public marketplace is at `/week12`; admin routes at `/admin`, `/week12/admin`, and `/week12/admin/artworks` require an admin account.

## MySQL Workbench connection

1. Start the MySQL service in XAMPP.
2. In MySQL Workbench, create a connection to `127.0.0.1` on port `3306`. For a default local XAMPP install, use username `root` and leave the password blank unless you configured one.
3. Open `database/setup.sql` in Workbench and execute it. It creates the `pixmoreedb` database and schema without adding sample accounts or artwork or copying legacy listings. The old database is left in place.
4. Configure the Laravel `.env` connection to use the same MySQL server:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixmoreedb
DB_USERNAME=root
DB_PASSWORD=
```

The same values are provided in `.env.example`. After configuring Laravel, run `php artisan migrate --seed` to create an empty marketplace schema. The default seeder does not create demo users or artwork; the supplied images remain available as files in `public/assets/nfts` for creators to use when they choose.

The `users` table stores account and editable profile fields. Users can update their display name, email, phone, bio, and profile photo in `/user/profile-settings`, and change their password after confirming the current password. New passwords must be at least 8 characters; users may choose an easy-to-remember password. The `images` table stores each signed-in user's uploaded image path, MIME type, size, and description; deleting a user cascades to their image records. Uploaded files are stored on Laravel's public disk. Run `php artisan storage:link` once so their thumbnails can be displayed.

Admins can update the homepage, About page, marketplace, log-in, and registration pages from the Content Studio at `/admin/content`. Text and supported page images can be edited in the matching page editor; marketplace and gallery artwork is managed from the admin artwork screen. The About page's “people behind Pixmoree” section is a separate, editable developer team, not the marketplace creator list. Developer cards support a section, quote, and validated social URLs. Admin screens share the Pixmoree visual theme. Signed-in creators can upload original artwork from `/user/create`; artwork metadata and the stored image path are saved in MySQL, while the actual image is stored on Laravel's public disk. Users can edit or delete their own artwork, and duplicate image files are rejected by SHA-256. Admins can manage all listings. Creator profile images are stored in `users.avatar_path` and can be changed in profile settings. The supplied artwork and developer photos are kept in `public/assets/nfts` and `public/assets/creators`, separate from the site background and logo.

Artwork cards and detail pages also show a separate demo market-value estimate; this never changes a creator's asking price or Pixel Coin checkout amount. The estimate starts at the asking price, adds 5% per recorded Pixel Coin demo purchase in the previous 30 days (up to +25%), and after 30 days without a purchase subtracts 1% for each full inactive week (down to -25%). The displayed amount is rounded to two decimals. These simulated purchases are not real market sales, and the estimate is informational only—not a financial valuation.

## Pixel Coin demo wallet

Authenticated users can view their account balance from the upper-right wallet link or open `/user/pixel-coin` to add free demo credits and review wallet activity. Pixel Coin is non-cash, non-redeemable, and does not connect to a payment provider. The session-authenticated, CSRF-protected JSON endpoints are:

- `GET /api/pixel-coins` — balance and the latest 20 ledger entries.
- `POST /api/pixel-coins/top-ups` — adds a server-validated demo amount of 500, 1,000, or 2,500 coins.
- `POST /api/pixel-coins/purchases` — records a demo purchase for a listed artwork using its database price and deducts the balance atomically.

Wallet balances and ledger entries are stored in MySQL. Seed the database only with test fixtures explicitly when running tests; `ArtworkFixtureSeeder` is not part of the default seeding path.

The Workbench schema remains available as `database/setup.sql` inside the project, but Apache rules deny direct web requests to the database directory and SQL files. Configure Apache to use the project's `public` directory as its document root.

The local beta accounts use local-only `@pixmoree.test` addresses; configure real email addresses before using accounts outside local development.

## Beta user and admin accounts

Create a regular beta user through `/register`; all registrations receive the `user` role and cannot grant themselves admin access. To create a beta admin, register that account normally and promote it from the project directory:

```powershell
& 'C:\xampp\php\php.exe' artisan pixmoree:make-admin admin@example.com
```

The command only promotes an existing registered account and does not set or expose a password. Both roles sign in at `/login`; users are sent to their account area and admins to the admin dashboard. No shared demo credentials are seeded or committed.

## Tests

The test suite uses an in-memory SQLite database regardless of the local MySQL settings. Run `php artisan test`.

## Project structure

- `routes/web.php` contains the Laravel web routes.
- `resources/views` contains the guest, account, profile, and Week 12 views.
- `public/assets`, `public/css`, and `public/js` contain static site assets.
- `database/migrations` and `database/seeders` define the account, image, artwork, and Pixel Coin wallet schema. Default seeding leaves user and artwork tables empty.

For local development, run `php artisan serve` from the project root. For Apache deployment, configure the document root to the project's `public` directory.
