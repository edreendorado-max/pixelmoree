# Pixmoree Repository Audit

**Review date:** 2026-11-22
**Scope:** Laravel application code, routes, views, current migrations, tests, and project Markdown. This is a repository and feature-suite review, not a live browser or production review.

## Findings

- NFT checkout and creator withdrawals are simulation-only. Checkout creates an in-app PHP order, transfers one edition item, records a creator earnings credit, and sends account notifications. A withdrawal debits the creator's recorded balance and creates a receipt. No payment or payout provider is connected.
- Pixel Coin purchases, wallet pages, withdrawals, and top-ups have been removed. Legacy wallet and manual-payment schema fields are removed by a new migration; old fake coin balances are not converted into PHP earnings.
- Market estimates, creator sales analytics, and purchase recommendations now read simulated purchase orders instead of the removed coin ledger.
- README, demo instructions, project diagrams, seeded marketplace copy, member screens, and Admin Studio transaction descriptions now match the simulation-only flow.

## Verification

- SQLite feature suite: **68 tests passed, 718 assertions**.
- The suite exercises simulated purchase and withdrawal flows, balance validation, receipt access, removed top-up routes, artwork ownership transfer, and admin transaction records.
- Both pending cleanup migrations were applied successfully to the configured local XAMPP MySQL database after creating a backup. Back up any other database with old Pixel Coin or manual-payment records before applying them.
- Responsive browser appearance, accessibility, SMTP delivery, and production hosting were not reviewed here.

## Admin form follow-up

- Admin Studio pages were rendering an empty CSRF token because the shared Week 12 layout initialized the token to an empty string before the admin layout fallback. This caused POST forms, including artwork price saves, to return HTTP 419. The shared layout now initializes the token with Laravel's `csrf_token()` helper.
- Removed the unused explanatory banner from artwork management.
- Removed the repeated market-analytics warning banner; the page still labels values as estimates, and transaction pages retain clear notices that no payment or blockchain asset is transferred.
- Targeted admin feature tests: **6 passed, 70 assertions**, including regression checks for the rendered CSRF token and removed analytics banner.

## Receipt and account-recovery follow-up

- In local development, submitting a known account email on the password-reset page now redirects directly to the new-password form. Unknown email requests keep a generic response, and production reset requests still use the configured email flow.
- Purchase receipts now identify the receipt, artwork, edition number, unique item ID, recorded amount, and ownership transfer from seller to buyer. The print layout hides navigation and prints on a light background.
- Buyers can open receipts from purchase history and their collection. Creators can open a sale receipt from recent earnings. Member action confirmations share one visible message area so a completed purchase or listing action is not hidden in another page section.
- Removed amber warning styling and exclamation icons from the reviewed page feedback. Validation errors remain visible as text.
- The configured MySQL database reports all repository migrations as applied; no migration was pending for this update.
- Regular users are now redirected to their member dashboard when a saved post-login destination requires admin access. Valid member destinations, such as Store, are still honored.

## Read-only schema cleanup review

- `creator_follows` is present in the configured database but has no current application reads or writes; it remains from the removed follow feature. It is the clearest unused-table candidate, but dropping it would discard historical follow records.
- `user_profiles` duplicates phone, bio, avatar, and banner fields that current screens and controllers read from `users`. The `User` save hook mirrors those values into `user_profiles`, but no current application code consumes the relation. This is a consolidation candidate, not a safe drop until the duplicate sync and schema are migrated together.
- `ownership_transfers` is written for purchases but has no current application reads. It overlaps transfer details stored in payment orders and edition items, while also serving as a potential audit history; retain it unless that history is explicitly unnecessary.
- `artwork_ownerships` overlaps edition-level ownership in `artwork_items`, but the resale flow still reads it and purchase handling still updates it for single-item artwork. It is not safe to remove without first migrating those paths and reconciling existing ownership.
- Profile images, notifications, payment orders, cash-ledger entries, resale listings, artwork likes/views, and market-value snapshots all have current application uses. The old Pixel Coin and email-verification tables were already removed by applied migrations; their historical migration files should remain.

## Presentation follow-up

1. Run the migrations against a separate MySQL staging database before updating a shared/local MySQL database.
2. Capture phone- and desktop-width screenshots of account creation, artwork creation, simulated checkout, purchase receipt, creator earnings, simulated withdrawal receipt, and Admin Studio.
3. Check keyboard navigation, visible focus, contrast, responsive layout, and validation messages in a browser.
4. Configure SMTP only if demonstrating delivery of password-reset emails.
