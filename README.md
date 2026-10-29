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
Copy-Item .env.example .env
composer install
& 'C:\xampp\php\php.exe' artisan key:generate
& 'C:\xampp\php\php.exe' artisan migrate --seed
& 'C:\xampp\php\php.exe' artisan serve
```

Before running migrations, update `.env` with the MySQL connection values below. Open <http://127.0.0.1:8000/> for the guest homepage. Registration is at `/register`; profile and image management is at `/user/profile-settings`. The public marketplace is at `/week12`; admin routes at `/admin`, `/week12/admin`, and `/week12/admin/artworks` require an admin account.

## MySQL Workbench connection

1. Start the MySQL service in XAMPP.
2. In MySQL Workbench, create a connection to `127.0.0.1` on port `3306`. For a default local XAMPP install, use username `root` and leave the password blank unless you configured one.
3. Open `database/setup.sql` in Workbench and execute it. It creates the `pixmoreedb` database and its tables, adds fictional sample artwork, and copies existing artwork rows from the legacy `pixelmoree` database if that database is present. The old database is left in place as a backup.
4. Configure the Laravel `.env` connection to use the same MySQL server:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixmoreedb
DB_USERNAME=root
DB_PASSWORD=
```

The same values are provided in `.env.example`. You can alternatively let Laravel create the schema with `php artisan migrate --seed`; existing tables created by `database/setup.sql` are detected by the migrations.

The `users` table stores account and editable profile fields. The `images` table stores each signed-in user's uploaded image path, MIME type, size, and description; deleting a user cascades to their image records. Uploaded files are stored on Laravel's public disk. Run `php artisan storage:link` once so their thumbnails can be displayed.

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
- `database/migrations` and `database/seeders` define the account, image, and artwork schema and fictional listings.

For local development, run `php artisan serve` from the project root. For Apache deployment, configure the document root to the project's `public` directory.
