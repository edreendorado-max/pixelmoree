# Pixmoree

Pixmoree is a Laravel 12 application for the guest-facing marketplace prototype and its Week 12 artwork screens. Guest and account-demo data is handled in the browser; Week 12 artwork listings are stored in the configured database. Authentication and payments are not implemented.

## Requirements

- PHP 8.2 or newer with `pdo_sqlite` enabled (included with the XAMPP PHP installation)
- Composer

On Windows, install Composer using the official Composer installer and select `C:\xampp\php\php.exe` as the PHP executable.

## Run locally

From the project directory in PowerShell:

```powershell
Copy-Item .env.example .env
composer install
& 'C:\xampp\php\php.exe' artisan key:generate
& 'C:\xampp\php\php.exe' artisan migrate --seed
& 'C:\xampp\php\php.exe' artisan serve
```

Open <http://127.0.0.1:8000/> for the guest homepage. The marketplace and admin prototype are at `/week12`, `/week12/admin`, and `/week12/admin/artworks`.

The default local database is SQLite at `database/database.sqlite`. Migrations create the artwork table, and `--seed` adds the fictional starter listings. The SQLite database file is ignored by Git.

## Use MySQL instead

Start MySQL in XAMPP, create a `pixelmoree` database, then set these values in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixelmoree
DB_USERNAME=root
DB_PASSWORD=
```

Run `php artisan migrate --seed` to create and populate the table. The existing [`database/setup.sql`](database/setup.sql) can also create the table and fictional starter data in MySQL; the Laravel migration detects an existing `artworks` table.

## Project structure

- `routes/web.php` contains the Laravel web routes.
- `resources/views` contains the guest, account-demo, and Week 12 views.
- `public/assets`, `public/css`, and `public/js` contain static site assets.
- `database/migrations` and `database/seeders` define the Week 12 database schema and demo listings.

For local development, run `php artisan serve` from the project root. For Apache deployment, configure the document root to the project's `public` directory.
