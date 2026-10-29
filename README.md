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
3. Open `database/setup.sql` in Workbench and execute it. It creates the `pixmoreedb` database and tables, adds the sample artwork rows, and copies existing artwork rows from the legacy `pixelmoree` database if present. The old database is left in place as a backup.
4. Configure the Laravel `.env` connection to use the same MySQL server:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixmoreedb
DB_USERNAME=root
DB_PASSWORD=
```

The same values are provided in `.env.example`. After configuring Laravel, run `php artisan migrate --seed` to create the creator accounts, assign the supplied artwork, and calculate duplicate-image hashes. Existing tables created by `database/setup.sql` are upgraded by the migrations.

The `users` table stores account and editable profile fields. The `images` table stores each signed-in user's uploaded image path, MIME type, size, and description; deleting a user cascades to their image records. Uploaded files are stored on Laravel's public disk. Run `php artisan storage:link` once so their thumbnails can be displayed.

Admins can update the homepage, About page, and marketplace copy and upload About-page images at `/admin/content/home`, `/admin/content/about`, and `/admin/content/marketplace`. Creator artwork is owned by a user account, listed from MySQL across the homepage and marketplace, and duplicate image files are rejected by SHA-256. Users can create, edit, and delete their own artwork; admins can manage all listings. Creator profile images are stored in `users.avatar_path` and can be changed in profile settings. Uploaded files use Laravel's public storage. The supplied artwork and creator photos are kept in `public/assets/nfts` and `public/assets/creators`, separate from the site background and logo.

The artwork seeder creates the four creator profiles and assigns the seven supplied artworks across them. New creator accounts use local-only `@pixmoree.test` addresses; configure real email addresses before using the accounts outside local development.

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
- `database/migrations` and `database/seeders` define the account, image, creator, and artwork schema and sample listings.

For local development, run `php artisan serve` from the project root. For Apache deployment, configure the document root to the project's `public` directory.
