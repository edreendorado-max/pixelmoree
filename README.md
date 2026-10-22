# Pixmoree Guest Homepage — Laravel + Bootstrap

This package adds a guest-facing Pixmoree landing page based on the supplied screenshot and keeps the existing marketplace browsing UI below it.

## Included
- Week 12 Bootstrap 5 marketplace under `/week12`: client gallery and artwork details, plus an admin overview and artwork manager sharing a responsive layout. Artwork listings persist in MySQL; purchase/favorite buttons remain simulated.
- Guest homepage hero with Welcome headline, gradient call-to-action, sparkle details, supplied Pixmoree logo, and background artwork.
- Dedicated About page covering Pixmoree, its mission, marketplace features, student-project context, and project contact guidance, with editable creator profile placeholders.
- The guest and user About pages include a student project brief with purpose, core features, learning goals, tools/technology stack, project scope/limitations, and a project start date of October 15, 2026.
- Glass-style login and account-creation pages using the supplied background and Pixmoree logo, with Google/Facebook sign-in UI placeholders.
- The login screen includes a browser-only demo account: `demo@pixmoree.test` / `PixmoreeDemo2026!`. Signing in saves a sample profile and demo session in local browser storage; it is not a real or secured account.
- Separate `/admin` dashboard demo with overview metrics and NFT, user, transaction, category, and settings panels.
- `/user` is a user-only creative homepage with a left sidebar, quick collection/purchase/favorites summary, marketplace browsing, and top-right search/profile/notifications controls. The Dashboard shows the saved profile name/username, custom banner, local demo counts for likes and sold listings, and a gallery of uploaded and owned artwork with a Create action. `/user/collection` groups My Uploads, Owned artwork, and My Favorites into tabs; `/user/favorites` opens the favorites tab directly. Dashboard, Purchases, Profile Settings, Notifications, and About are also available as separate account pages; the user About page uses the full story, project, tools, team, and contact content from the guest About page.
- `/user/create` provides a local demo listing form for original artwork. Created listings appear in the Dashboard and can be removed; this does not create a real marketplace listing.
- `/user/profile-settings` provides a custom profile banner and avatar, display name, email, phone, bio, social link, and email-notification preference. `/user/settings` remains an alias. Profile details and images are saved in browser storage.
- User sidebar links include Create, Home, My Collection, Dashboard, Purchases, Terms & Originality, Notifications, Settings, and About. Payment is opened through an NFT's Buy action, not from account menus. The profile menu includes a demo Log out link back to the guest experience.
- Existing trending collections, creator showcase, and artwork gallery retained beneath the hero.
- Selecting an NFT as a guest opens a compact preview; account users can open `/user/nft/{id}` for a full detail page with the artwork, description, creator, price, category, token and collection information, and favorites. Blockchain, standard, and royalty values are explicitly marked as sample data; listing details are kept in the current browser.
- `/user/payment` is only opened from an NFT's Buy action and provides an order summary, GCash, Maya, MariBank, bank transfer, Visa/Mastercard, other e-wallet, demo wallet, and Pixel Coin checkout, plus local transaction history. The user demo wallet starts with 2,500 PIXEL COIN; 500, 1,000, and 2,500 PIXEL COIN demo top-ups can be added locally. Pixel Coin checkout uses the fixed sample rate 1 ETH = 1,000 PIXEL COIN. These credits have no cash value, cannot be redeemed, and do not involve real deposits. No payment credentials are collected and no money or cryptocurrency is transferred. `/user/terms` includes the demo marketplace terms and anti-plagiarism/originality policy; creators must confirm permission/originality when creating a listing.
- Guest creator/profile features remain demo links; artwork favorites can be saved locally in the browser and filtered from the gallery on `/user`.
- `/login` and `/register` (also `/create-account`) provide front-end authentication screens. Configure an authentication backend and OAuth credentials before using them for real accounts.
- `/admin` is a front-end demonstration only. It is not protected by authentication and its sample management actions do not persist; do not use it with real marketplace or user data.
- `/user` pages are front-end demos, not authenticated accounts. Favorites, demo collection items, purchase records, recent views, profile changes, and pending checkout details are stored in local browser storage only; demo purchases do not process payments. The terms and originality policy is a draft and needs professional/legal review before use in a live service.
- Responsive styles for desktop, tablet, and mobile.

## Run with XAMPP and MySQL

1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. In phpMyAdmin, import [`database/setup.sql`](database/setup.sql). It creates the `pixelmoree` database, an `artworks` table, and three fictional starter listings. Re-importing it updates those starter rows.
3. Optionally copy `.env.example` to `.env` and set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` to match your local MySQL account. The XAMPP defaults are already used if `.env` is absent; the default XAMPP account is often `root` with an empty password.
4. Open the project through XAMPP (for example `http://localhost/pixelmoree/week12`) or run `C:\xampp\php\php.exe -S localhost:8000 index.php` from the project folder and open `http://localhost:8000/week12`.

The artwork admin screen can add fictional listings, approve/unlist them, or delete them; changes persist in the local database. The database user needs access to `pixelmoree`. Do not publish a populated `.env` file or put real credentials in the repository.

Bootstrap 5.3 and Bootstrap Icons are loaded from CDN. This is a guest-facing front end; authentication and account features need to be supplied by your Laravel application.

## Week 12 routes

Week 12 routes are available at:

- `/week12` — client marketplace
- `/week12/artworks/neon-wanderer` — client artwork detail
- `/week12/admin` — admin overview
- `/week12/admin/artworks` — admin artwork list

The pages use [`resources/views/layouts/week12.blade.php`](resources/views/layouts/week12.blade.php), Bootstrap 5.3, and [`public/css/week12.css`](public/css/week12.css). The standalone XAMPP entry point uses the PDO store in [`app/`](app), while `routes/web.php` exposes the same MySQL-backed screens in a Laravel host. SQL setup and fictional starter data are in [`database/setup.sql`](database/setup.sql). The repository itself is a lightweight PHP prototype, not a complete Laravel scaffold; it does not include `artisan`, `composer.json`, or `vendor/`.

See [`WEEK12-SUBMISSION.md`](WEEK12-SUBMISSION.md) for the activity plan, testing checklist, screenshot checklist, and submission items that still require team-provided details.

## GitHub

Repository: <https://github.com/edreendorado-max/pixelmoree>
