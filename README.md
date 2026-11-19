# Pixmoree

Pixmoree is a Laravel 12 digital-art marketplace with accounts, artwork editions, profiles, engagement, simulated purchases, receipts, notifications, and a simulated Pixel Coin wallet. Registration uses email and password and makes the account available immediately; email verification is not required. Payment-like actions are local database simulations: no cash, GCash, payment gateway, or blockchain token is involved.

## Website audit and improvement checklist

The code-level website audit and prioritized recommendations are in [WEBSITE_AUDIT.md](WEBSITE_AUDIT.md). Key next steps are:

- Review the main guest, member, creator, checkout, and admin journeys in a browser at mobile and desktop sizes.
- Decide whether marketplace listings and artwork details should be publicly viewable before sign-in; the current Store requires authentication.
- Add clear terms, privacy, content/copyright, payment dispute/refund, and support information before enabling payments.
- Provide optional demo content for evaluating a fresh local install, and ensure navigation destinations have clear, distinct behavior.
- Check accessibility, empty/error states, third-party asset loading, and admin content recovery.

The audit maps repository evidence and automated test results to the rubric. Responsive browser appearance and presentation evidence still require a local demonstration.

The rubric diagrams, ERD, and tri-fold demonstration guide are in [docs/PROJECT_DIAGRAMS.md](docs/PROJECT_DIAGRAMS.md) and [docs/DEMO_MANUAL.md](docs/DEMO_MANUAL.md).

## Requirements

- PHP 8.2 or newer with `pdo_mysql` enabled (included with the XAMPP PHP installation); `pdo_sqlite` is used for isolated automated tests
- Composer

On Windows, install Composer using the official Composer installer and select `C:\xampp\php\php.exe` as the PHP executable.

## Run locally

Before migrating, start MySQL from the XAMPP Control Panel and create the local database. In phpMyAdmin, run `CREATE DATABASE pixmoreedb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;` or use the XAMPP MySQL command line. The example configuration uses the standard local XAMPP root account with a blank password; update `.env` if your MySQL account differs.

From the project directory in PowerShell:

```powershell
$env:PATH = 'C:\xampp\php;' + $env:PATH
Copy-Item .env.example .env
composer install
& 'C:\xampp\php\php.exe' artisan key:generate
& 'C:\xampp\php\php.exe' artisan migrate --seed
php artisan serve
```

Adding XAMPP's PHP directory to the front of `PATH` ensures `php artisan serve` launches the same PHP runtime as the Artisan commands. Open <http://127.0.0.1:8000/> for the guest homepage. Registration is at `/register`; the signed-in Store is at `/store` (`/week12` redirects there). Admin routes at `/admin`, `/week12/admin`, and `/week12/admin/artworks` require an admin account. The app is configured for simulation mode by default.

### Access the development site from devices on the same Wi-Fi/LAN

This shares the site only with devices that can reach this computer on the local network. It does not publish the site to the internet, and it does not make devices on a different Wi-Fi network able to connect. Keep this for trusted networks and development; `artisan serve` is not a production web server.

1. Connect the host computer and phone/tablet/other computer to the same Wi-Fi or LAN. On Windows, run `ipconfig` and find the active adapter's **IPv4 Address** (for example, `192.168.1.28`). Do not use `127.0.0.1` on the other device; that address refers to that device itself.
2. In the project folder, start Laravel bound to all network interfaces:

   ```powershell
   & 'C:\xampp\php\php.exe' artisan serve --host=0.0.0.0 --port=8000
   ```

   Leave this terminal open while using the site. Stop the server with `Ctrl+C`. If port 8000 is already in use, stop the old local server or choose another port and use that port in all URLs and firewall commands.
3. On another device connected to the same network, open `http://<HOST-IP>:8000/`, replacing `<HOST-IP>` with the host's IPv4 address. Example: `http://192.168.1.28:8000/`.
4. If the page times out, allow inbound TCP traffic to the chosen port in Windows Defender Firewall on **Private networks only**. Run PowerShell as Administrator and add a narrow rule for port 8000:

   ```powershell
   New-NetFirewallRule -DisplayName 'Pixmoree Laravel dev (private LAN)' -Direction Inbound -Action Allow -Protocol TCP -LocalPort 8000 -Profile Private
   ```

   Remove the rule when it is no longer needed:

   ```powershell
   Remove-NetFirewallRule -DisplayName 'Pixmoree Laravel dev (private LAN)'
   ```

5. If the router assigns a new IP address later, repeat `ipconfig` and use the new IPv4 address. Do not configure router port forwarding for this development server.

The current development machine reported `192.168.1.28` during setup. This can change; verify it with `ipconfig` before connecting. If you change the host address, check `APP_URL` and restart the development server.

## Production launch readiness

This repository is prepared for local development; production hosting and third-party accounts must be configured separately. Before launch:

- Set `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL` to the final HTTPS domain in the host's secret/configuration store. Generate `APP_KEY` once for that environment and retain it securely; never commit `.env` or rotate the key after users have encrypted data.
- Use a managed production database with backups rather than the local SQLite file. Set its connection variables, run `php artisan migrate --force`, and verify migrations against a staging copy first.
- Configure a real SMTP/mail provider and sender address if email password-reset links and optional welcome emails should be delivered. Registration and sign-in do not require email verification. Social sign-in is disabled.
- Keep `PAYMENTS_SIMULATION=true` for the demo. NFT purchases, Pixel Coin top-ups, and withdrawals only update in-app records; they never charge or transfer money. Do not connect these routes to a live provider without a separately reviewed payment implementation.
- Persist and back up both the database and uploaded files. Keep `storage/` and `bootstrap/cache/` writable by the app; create the public storage link and make sure private files are not exposed through the web root.
- Point the web server document root at `public/`, enforce HTTPS, promote a real registered administrator with `php artisan pixmoree:make-admin <email>`, and smoke-test registration, login, uploads, checkout review, and the `/up` health endpoint.
- Run `php artisan schedule:run` every minute through the host scheduler so daily price snapshots are recorded. Use `composer install --no-dev --optimize-autoloader` and Laravel's production cache commands as part of the host's deployment pipeline.

Production hosting, domain, credentials, backups, and operational review still require separate setup. The included payment flows are simulations, not a production payment integration.

## Accounts and password reset

Registration creates an account and signs the new user in immediately. No verification code, SMTP setup, or email confirmation is needed to register, sign in, update an email address, use the Store, or make a purchase. Social login is disabled; use email or username with a password.

Password reset is available from the login page. Reset links expire after 60 minutes, and delivering them to a real inbox requires a configured SMTP provider. Optional welcome emails use the same mail configuration, but delivery failures do not block account creation. Never commit SMTP credentials; keep them in the local `.env` or the host's secret store. New passwords, including reset and profile password changes, must be at least six characters and include an uppercase letter, a number, and a special character.

## Simulated purchases and Pixel Coins

Pixel Coins use two decimal places. The configured PHP amounts, minimums, conversion cap, and 1.5% top-up fee are demonstration values only. A simulated NFT purchase creates an approved receipt, transfers one selected edition item, notifies buyer and seller, and credits the creator's simulated Pixel Coin balance. Coin purchases debit the buyer and credit the creator in one database transaction. Simulated top-ups and withdrawals update the in-app wallet and ledger only. Each order and edition item has a UUID identifier, as does each coin ledger entry.

Set `PAYMENTS_ENABLED=true` and `PAYMENTS_SIMULATION=true` for local demo transactions. The admin Transactions screen displays completed simulation records. Admin review remains available for artwork moderation and resale evidence, not payment approval.

1. Run migrations after updating the app: `php artisan migrate`.
2. Confirm `PAYMENTS_ENABLED=true` and `PAYMENTS_SIMULATION=true` in `.env`; run `php artisan config:clear` after changing configuration.
3. Register a second member account to act as the simulated buyer.
4. Confirm the buyer receipt and ownership, seller notification and simulated Pixel Coin credit, and the completed order in Admin Studio â†’ Transactions.
5. Simulate a Pixel Coin top-up and withdrawal and confirm each receipt and ledger entry. No external payment is requested.
6. Use the Admin Studio to moderate artwork, review resale evidence, inspect analytics, and update site content.

The local payment feature gate and simulation setting are enabled for the class demo. NFT purchases, Pixel Coin top-ups, and withdrawals complete in-app and do not use GCash, QR codes, receipt uploads, or external payments. Keep `PAYMENTS_SIMULATION=true`; simulated PHP amounts and wallet balances do not represent real funds.
## MySQL database

The rubric demonstration uses the local MySQL database named `pixmoreedb`. Laravel migrations are the source of truth for its schema; run them after pulling schema changes:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pixmoreedb
DB_USERNAME=root
DB_PASSWORD=
DB_FOREIGN_KEYS=true
```

Run `php artisan migrate --seed` to create or update the schema. The default seeder does not create demo users or artwork; the supplied images remain available in `public/assets/nfts` for creators to use when they choose. Do not run the legacy `database/setup.sql`; the Laravel migrations define the current schema.

For the MySQL feature suite, create a separate test database with `mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS pixmoree_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"`. Run `& 'C:\xampp\php\php.exe' vendor\bin\phpunit --configuration=phpunit.mysql.xml --testsuite=Feature`. This test configuration targets only pixmoree_test, which the test suite refreshes. Never point it at a database containing user data.

### SQLite for isolated local tests

The test environment can use SQLite in memory. To run the site locally on SQLite instead of MySQL, set `DB_CONNECTION=sqlite` and `DB_DATABASE=database/database.sqlite` in `.env`, then run the migrations. The ignored SQLite file is for local development only; the rubric's database demonstration should use MySQL.

The `users` table stores account and editable profile fields, including a unique username. Registration creates the user row and signs the new user in immediately. Users can sign in with either their email or username, and can update their display name, email, phone, bio, and profile photo in `/user/profile-settings`; email changes do not require verification or sign the user out. Each new account receives an in-app welcome notification; users can view and mark their own notifications as read from `/user/notifications`. These records are stored in the `notifications` table. A migration removes the obsolete email-verification code table and timestamp column. The `images` table stores each signed-in user's uploaded image path, MIME type, size, and description; deleting a user cascades to their image records. User uploads and NFT artwork uploads are stored on Laravel's public disk. Run `php artisan storage:link` after setup so URLs under `/storage` can reach those files; if uploaded artwork appears blank, check that `public/storage` links to `storage/app/public` and rerun the command if it is missing.

Admins can monitor and edit account profile details, moderate creator-submitted artwork, update listing prices, review payment and resale submissions, edit website pages, and view market analytics from `/week12/admin`. Admin accounts are redirected away from member-only pages such as `/user` and `/store`; the Store remains available to regular signed-in users. Admins cannot create artwork listings on behalf of their own accounts; creators submit their own artwork. The administrator-only analytics API at `/admin/api/analytics` reports asking prices, likes, views, creator followers, and daily estimated-market-value snapshots. Estimates are not completed sales; run the Laravel scheduler so `artworks:prices:snapshot` can record a daily history.

### Share a safe local database setup with collaborators

The Git repository shares schema and project files, not a live database. Each collaborator creates a local MySQL database by running `php artisan migrate --seed`. Never commit the local SQLite database, `.env`, or production credentials.

Admins can update the homepage, About page, marketplace, log-in, and registration pages from the Content Studio at `/admin/content`. Text and supported page images can be edited in the matching page editor; marketplace and gallery artwork is managed from the admin artwork screen. The About page's Ã¢â‚¬Å“people behind PixmoreeÃ¢â‚¬Â section is a separate, editable developer team, not the marketplace creator list. Developer cards support a section, quote, and validated social URLs. Admin screens share the Pixmoree visual theme. Signed-in creators can upload original artwork from `/user/create`; artwork metadata and the stored image path are saved in MySQL, while the actual image is stored on Laravel's public disk. Users can edit or delete their own artwork, and duplicate image files are rejected by SHA-256. Admins can manage all listings. Creator profile images are stored in `users.avatar_path` and can be changed in profile settings. The supplied artwork and developer photos are kept in `public/assets/nfts` and `public/assets/creators`, separate from the site background and logo.

To replace all local accounts and account-owned artwork with one verified administrator, configure `PIXMOREE_ADMIN_NAME`, `PIXMOREE_ADMIN_EMAIL`, `PIXMOREE_ADMIN_USERNAME`, and `PIXMOREE_ADMIN_PASSWORD` in the local `.env`, then run `php artisan pixmoree:reset-accounts`. The command is restricted to the local SQLite database or local `pixmoreedb` MySQL database and asks for confirmation before deleting accounts and their linked data. Keep the password in `.env` or another secret store; never commit it. The admin can sign in with the configured email or username.

Marketplace cards show creator asking prices. The member dashboard also displays simulated estimated values and daily gainers/decliners. Estimates respond to likes, views, approved simulated purchases, edition supply, and inactivity; they are not completed sale prices.

The signed-in home page shows a For You feed ranked from the user's latest liked, purchased, and viewed artwork categories. Marketplace search, category filtering, and sorting operate on current database-backed listings. Recommendations are recomputed on each page load.

## Simulated transactions

The local app uses `PAYMENTS_ENABLED=true` with `PAYMENTS_SIMULATION=true`. NFT purchases transfer one selected edition immediately and create a receipt; simulated top-ups and withdrawals update the member's wallet and ledger. The admin Transactions page lists completed simulated records. No GCash QR setup, receipt upload, real charge, or cash payout is involved. Admins continue to moderate artwork and resale submissions.

Estimated market values use asking price, engagement, approved simulated sales, edition supply, and inactivity. Daily snapshots support the gainers/decliners display. These estimates are for demonstration only and do not represent real prices or investment advice.

Amounts displayed in simulated receipts and Pixel Coin records are demonstration values and do not represent real money owed or paid.

Run `php artisan migrate` after updating, and keep `PAYMENTS_SIMULATION=true` for local demonstrations.
The legacy MySQL schema remains available as `database/setup.sql`; Apache rules deny direct web requests to the database directory and SQL files. Configure Apache to use the project's `public` directory as its document root.

The local beta accounts use local-only `@pixmoree.test` addresses; configure real email addresses before using accounts outside local development.

## Beta user and admin accounts

Create a regular beta user through `/register`; all registrations receive the `user` role and cannot grant themselves admin access. To create a beta admin, register that account normally and promote it from the project directory:

```powershell
& 'C:\xampp\php\php.exe' artisan pixmoree:make-admin admin@example.com
```

The command only promotes an existing registered account and does not set or expose a password. Both roles sign in at `/login`; users are sent to their account area and admins to the admin dashboard. No shared demo credentials are seeded or committed.

## Tests

The standard feature suite uses an in-memory SQLite database regardless of the local database configuration. Run `php artisan test --testsuite=Feature`. For MySQL, use the separate pixmoree_test database and phpunit.mysql.xml instructions above.

## Project structure

- `routes/web.php` contains the Laravel web routes.
- `resources/views` contains the guest, account, profile, and Week 12 views.
- `public/assets`, `public/css`, and `public/js` contain static site assets.
- `database/migrations` and `database/seeders` define the account, image, artwork, payment, and ownership schema. Legacy wallet tables are retained for existing records; default seeding leaves user and artwork tables empty.

For local development, run `php artisan serve` from the project root. For Apache deployment, configure the document root to the project's `public` directory.
