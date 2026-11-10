# Pixmoree

Pixmoree is a Laravel 12 beta marketplace with real account, artwork, profile, and engagement data stored in MySQL. Google and Facebook sign-in are supported when provider credentials are configured. Registration, email verification, password reset, artwork uploads, favorites, and profile updates use persistent server-side storage. Payments, payouts, resale, and Pixel Coin transactions are disabled by default; do not accept or send funds through the beta.

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

## Email verification, password reset, and social sign-in

Email/password registration sends a six-digit verification code to the supplied email address. New accounts cannot sign in until the code is confirmed. Codes expire after 10 minutes, are stored as hashes, allow at most five guesses, and can be resent after a 60-second cooldown. Login attempts for existing unverified accounts also send or re-send the code. Code delivery is handled through Laravel's configured SMTP mailer; local `log` and `array` mailers are rejected for verification because they do not deliver to the mailbox. For Gmail, create a Google App Password (requires 2-Step Verification), then set `MAIL_USERNAME` to your Gmail address and `MAIL_PASSWORD` to the App Password in `.env`; the local configuration uses Gmail SMTP on port 587 with TLS and uses that address as the sender. Never use your regular Google account password or commit SMTP credentials. For another SMTP provider, set `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_SCHEME`, and `MAIL_FROM_ADDRESS` to that provider's values. A successful code check verifies that the user can receive messages at that address; it does not certify the person's legal identity.

Password reset is available from the login page and reset links expire after 60 minutes. New passwords, including reset and profile password changes, must be at least six characters and include an uppercase letter, a number, and a special character.

Google and Facebook OAuth sign-in do not charge an API usage fee for basic sign-in, but require developer app credentials and provider setup. Social sign-ups also require the Pixmoree email verification code before login. To enable them, create OAuth apps with each provider and set `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `FACEBOOK_CLIENT_ID`, and `FACEBOOK_CLIENT_SECRET` in `.env`. Register these callback URLs with the providers:

```text
http://127.0.0.1:8000/auth/google/callback
http://127.0.0.1:8000/auth/facebook/callback
```

The callback base uses `APP_URL`; update it for another host or production domain. Keep provider secrets out of Git. Social sign-in does not automatically link to an existing password account with the same email. If you do not want OAuth or cannot configure the provider credentials, remove the social links from the login and registration pages; email/password verification does not depend on OAuth.

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

The `users` table stores account and editable profile fields, including a unique username. Users can sign in with either their email or username, and can update their display name, email, phone, bio, and profile photo in `/user/profile-settings`. Changing the email address requires verifying the new mailbox. The `email_verification_codes` table stores only a hash of the time-limited code and is deleted after successful verification. The `images` table stores each signed-in user's uploaded image path, MIME type, size, and description; deleting a user cascades to their image records. Uploaded files are stored on Laravel's public disk. Run `php artisan storage:link` once so their thumbnails can be displayed.

### Share a safe local database snapshot with collaborators

The Git repository shares schema and project files, not a live MySQL server. Each collaborator should create their own local `pixmoreedb`, run `php artisan migrate --seed`, then optionally execute `database/collaborator-data.sql` in MySQL Workbench to load the sanitized shared starter artwork. The SQL snapshot excludes all user accounts, emails, password hashes, wallet balances, and transactions; the artwork creator is anonymized and its image is committed at `public/assets/nfts/collaboration-anime.png`. Commit and push any future changes to this SQL file and its shared assets so collaborators can pull them. Never export `.env` or production credentials into Git.

Admins can update the homepage, About page, marketplace, log-in, and registration pages from the Content Studio at `/admin/content`. Text and supported page images can be edited in the matching page editor; marketplace and gallery artwork is managed from the admin artwork screen. The About page's “people behind Pixmoree” section is a separate, editable developer team, not the marketplace creator list. Developer cards support a section, quote, and validated social URLs. Admin screens share the Pixmoree visual theme. Signed-in creators can upload original artwork from `/user/create`; artwork metadata and the stored image path are saved in MySQL, while the actual image is stored on Laravel's public disk. Users can edit or delete their own artwork, and duplicate image files are rejected by SHA-256. Admins can manage all listings. Creator profile images are stored in `users.avatar_path` and can be changed in profile settings. The supplied artwork and developer photos are kept in `public/assets/nfts` and `public/assets/creators`, separate from the site background and logo.

To replace all local accounts and account-owned artwork with one verified administrator, configure `PIXMOREE_ADMIN_NAME`, `PIXMOREE_ADMIN_EMAIL`, `PIXMOREE_ADMIN_USERNAME`, and `PIXMOREE_ADMIN_PASSWORD` in the local `.env`, then run `php artisan pixmoree:reset-accounts`. The command is restricted to the local `pixmoreedb` MySQL database and asks for confirmation before deleting accounts and their linked data. Keep the password in `.env` or another secret store; never commit it. The admin can sign in with the configured email or username.

Marketplace listings show creator-provided asking prices only. Simulated market estimates and purchase-driven price analytics are not shown because the beta has no verified sales data.

The signed-in home page shows a For You feed ranked from the user's latest liked, purchased, and viewed artwork categories. Marketplace search, category filtering, and sorting operate on current database-backed listings. Recommendations are recomputed on each page load.

## Read-only legacy wallet

Authenticated users can view their legacy account balance from the upper-right wallet link or open `/user/pixel-coin` to review wallet activity. Pixel Coin has no cash value; top-ups and purchases are disabled. The session-authenticated, CSRF-protected JSON endpoints are:

- `GET /api/pixel-coins` — balance and the latest 20 ledger entries.
- `POST /api/pixel-coins/top-ups` — returns `503 Service Unavailable` while `PAYMENTS_ENABLED=false`.
- `POST /api/pixel-coins/purchases` — returns `503 Service Unavailable` while `PAYMENTS_ENABLED=false`.

Wallet balances and ledger entries are stored in MySQL. Seed the database only with test fixtures explicitly when running tests; `ArtworkFixtureSeeder` is not part of the default seeding path.

## Payments, cash balances, and resale

No payment provider is integrated. Financial actions, payment-order review, resale actions, and payouts remain unavailable while `PAYMENTS_ENABLED=false`. The existing order and ledger data is retained for historical records; no new transactions should be accepted during beta. Never collect passwords, wallet PINs, or OTPs.

Existing PHP balance and cash ledger fields are retained for historical records. New top-ups and withdrawals are disabled while `PAYMENTS_ENABLED=false`.

Prepaid load, Pixel Coin top-ups, and Pixel Coin purchases are disabled during beta.

Resale listings and reviews are disabled during beta. Existing ownership and order records remain stored for historical reference.

The normalized account/profile structure uses `users.id` as the internal account key and a stable UUID `users.account_id` for the public account identifier; one-to-one contact/profile details are mirrored to `user_profiles`. Artwork rows keep the existing listing key and add UUID `artworks.product_id`. `payment_orders.purchase_id` is the UUID for a purchase, top-up, or withdrawal request. `cash_ledger_entries` records historical PHP movements, `artwork_ownerships` stores current recorded ownership, and `ownership_transfers` records the transfer history. Migrations backfill UUIDs/profile data for existing records. Financial features must not be enabled until payment-provider integration and appropriate legal, tax, fraud, and privacy review are complete.

The Workbench schema remains available as `database/setup.sql` inside the project, but Apache rules deny direct web requests to the database directory and SQL files. Configure Apache to use the project's `public` directory as its document root.

The local beta accounts use local-only `@pixmoree.test` addresses; configure real email addresses before using accounts outside local development.

## Beta user and admin accounts

Create a regular beta user through `/register`; all registrations receive the `user` role and cannot grant themselves admin access. To create a beta admin, register that account normally and promote it from the project directory:

```powershell
& 'C:\xampp\php\php.exe' artisan pixmoree:make-admin admin@example.com
```

The command only promotes an existing registered account and does not set or expose a password. Both roles sign in at `/login`; users are sent to their account area and admins to the admin dashboard. No shared demo credentials are seeded or committed.

## Tests

The feature suite uses an in-memory SQLite database regardless of the local MySQL settings. Run `php artisan test --testsuite=Feature`.

## Project structure

- `routes/web.php` contains the Laravel web routes.
- `resources/views` contains the guest, account, profile, and Week 12 views.
- `public/assets`, `public/css`, and `public/js` contain static site assets.
- `database/migrations` and `database/seeders` define the account, image, artwork, and Pixel Coin wallet schema. Default seeding leaves user and artwork tables empty.

For local development, run `php artisan serve` from the project root. For Apache deployment, configure the document root to the project's `public` directory.
