# Pixmoree Rubric-Based System Audit

**Review date:** 2026-11-19  
**Rubric:** Final Examination Rubrics, ITEL 304 Integrative Programming Technologies 2  
**Scope:** Repository code, routes, migrations, setup and presentation documents, and the current SQLite feature suite. This is a repository review, not a live evaluator score.

## Current result

The website's payment-like actions are configured as simulations. NFT purchases, Pixel Coin top-ups, and withdrawals write in-app records only. Purchase receipts, edition ownership changes, seller credits, account notifications, and simulated price analytics work without a payment provider or GCash configuration.

- SQLite feature suite: **62 tests passed, 663 assertions**.
- A fresh MariaDB feature run was attempted using the isolated `pixmoree_test` config, but MySQL was not listening on `127.0.0.1:3306`; that database run could not start.
- The earlier repository audit recorded a successful migration of the local `pixmoreedb` schema. No migration was added for this update.
- Browser checks at phone and desktop widths, SMTP delivery, and group GitHub contribution history still require presentation-time evidence.

### Provisional rubric estimate

| Rubric criterion | Estimate | Evidence and remaining gap |
|---|---:|---|
| Subject Matter | **20/25 - Good** | Laravel MVC, database relationships, CRUD, authentication, access checks, validation, edition ownership, notifications, and simulation receipts are implemented. |
| Creativity | **20/25 - Good, provisional** | Pixmoree branding, artwork previews, itemized editions, simulated market values, engagement, and responsive styling are present. Capture and review phone/desktop screenshots and browser usability evidence. |
| Final Product | **20/25 - Good** | The current SQLite feature suite passes. MariaDB could not be retested because the local service is stopped. The website's external payment-like actions are disabled by design; the demo actions run in simulation mode. |
| Structure and Flow | **20/25 - Good, provisional** | README setup, MVC and ERD diagrams, updated client/admin walkthrough, and automated feature coverage are present. The group still needs to show its GitHub contribution history during the presentation. |
| **Estimated total** | **80/100** | Provisional. The evaluator's live browser review and presentation evidence can change the score. |

## Work completed

- Configured local payment-like actions for simulation mode only. No GCash QR setup, payment proof, real charge, or cash payout is required.
- Simulated NFT purchases create approved orders and receipts, transfer one unique edition item, credit the seller's simulated Pixel Coin balance, and notify both buyer and seller.
- Simulated top-ups and withdrawals update the wallet, ledger, receipt history, and account notifications.
- Updated Admin Studio to show completed simulation records and removed payment approval and external payment setup routes. Artwork and resale moderation, account management, analytics, and content editing remain available.
- Connected estimated market values and daily gainers/decliners to asking price, engagement, approved simulated sales, edition supply, and inactivity.
- Added previews for selected image uploads and the current/replacement artwork image.
- Added in-app welcome, password-change/reset, purchase, sale, top-up, and withdrawal notifications.
- Replaced manual-payment instructions in README, demo manual, and project transaction-flow diagrams.
- Updated the feature tests to cover the simulation-only transaction flow.

## Remaining presentation and environment work

1. Start XAMPP MySQL and rerun `vendor\\bin\\phpunit --configuration=phpunit.mysql.xml --testsuite=Feature` against the separate `pixmoree_test` database.
2. Capture readable phone- and desktop-width screenshots of registration, artwork creation and previews, checkout, receipt, collection ownership, notifications, market values, and Admin Studio.
3. In a browser, check keyboard navigation, visible focus, contrast, responsive layout, and validation/error states.
4. Show the group GitHub contribution history and explain the diagrams and data relationships during the presentation.
5. Configure SMTP only if demonstrating delivery of password-reset email; email is not required for registration or the simulated marketplace actions.

## Evidence reviewed

- `README.md`, `.env.example`, and `phpunit.mysql.xml`.
- `docs/PROJECT_DIAGRAMS.md` and `docs/DEMO_MANUAL.md`.
- Laravel controllers, models, Blade templates, migrations, and feature tests.
- SQLite feature run: 62 tests passed, 663 assertions.
- MariaDB rerun unavailable: local port 3306 refused the connection.
