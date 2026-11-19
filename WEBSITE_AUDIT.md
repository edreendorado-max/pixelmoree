# Pixmoree Rubric-Based System Audit

**Review date:** 2026-11-19  
**Rubric:** Final Examination Rubrics, ITEL 304 Integrative Programming Technologies 2  
**Scope:** Source code, routes, migrations, setup and presentation documents, automated tests, and local MySQL migration status. This is a repository and test review, not a live evaluator score.

## Overall result

The earlier audit baseline passed the feature suite on both the default SQLite test setup and XAMPP's MySQL-compatible MariaDB server. After that baseline, the requested account-verification removal updated application code, migrations, documentation, and feature tests. Those latest changes have not been rerun through the suite.

- SQLite: 71 tests passed, 820 assertions.
- MariaDB 10.4.32 using the MySQL connection: 71 tests passed, 820 assertions.
- The existing pixmoreedb schema was migrated without dropping its records; all project migrations are marked as run.
- The local .env now points the site to pixmoreedb. A separate pixmoree_test database is used only by the MySQL test configuration.
- The LAN health endpoint returned HTTP 200 from this computer.

Payments remain disabled. The admin GCash QR still must be configured before GCash checkouts or top-ups can be submitted. Real SMTP delivery, real GCash transfers, browser appearance across devices, and GitHub contribution history were not verified.

### Provisional rubric estimate

| Rubric criterion | Estimate | Evidence and remaining gap |
|---|---:|---|
| Subject Matter | **20/25 - Good** | Laravel MVC, relationships, CRUD, authentication, access checks, validation, and transaction records are present. Email verification has been removed; registration signs users in immediately. The earlier migration baseline and MVC request flow are documented. |
| Creativity | **20/25 - Good, provisional** | Pixmoree branding, responsive styling, artwork editions, and status feedback are present. The manual now includes client/admin steps and screenshot guidance, but actual phone/desktop screenshots and browser usability checks still need to be captured. |
| Final Product | **20/25 - Good** | The earlier feature suite passed on SQLite and MariaDB. Updated tests now cover registration without email verification and checkout without a verification gate, alongside GCash receipt review and Pixel Coin workflows. The updated suite has not been run yet. Payments remain disabled pending the admin QR and operational review. |
| Structure and Flow | **20/25 - Good, provisional** | README setup, a tri-fold demonstration guide, three system/workflow diagrams, an ERD, and test evidence are present. GitHub contribution history must still be shown by the project group during presentation. |
| **Estimated total** | **80/100** | The score is provisional; visual presentation and live demonstration evidence can change it. |

## Fixes made

- Replaced outdated social sign-in tests with checks that Google and Facebook routes are unavailable and email/password login remains.
- Rewrote old creator-QR and PHP-wallet tests to match the current admin-QR, Pixel Coin, and unique-edition workflows.
- Added Pixel Coin lifecycle coverage for top-up review, creator purchase credits, minimum withdrawals, payout decisions, refunds, and duplicate payout references.
- Changed top-up conversion to award at most 5/6 PC per PHP while preserving cash-out at PHP 1.20 per PC. A PHP 200 base top-up awards 166.66 PC and charges PHP 3.00 in fees; PHP 20 awards 16.66 PC and charges PHP 0.30 in fees.
- Added a server-side conversion cap: coins awarded cannot cash out for more than the top-up base amount. The page preview applies the same cap, and a migration caps unsettled orders created under the old rate.
- Updated admin and client payment descriptions to show the current policy. The Pixel Coin page now explains that payment actions are unavailable while the payment switch is off.
- Configured the local project for XAMPP MySQL, migrated the existing pixmoreedb database, and added a separate phpunit.mysql.xml configuration for repeatable MySQL feature testing.
- Added docs/PROJECT_DIAGRAMS.md and docs/DEMO_MANUAL.md with the MVC flow, registration/sign-in flow, transaction flow, ERD, presentation walkthrough, and screenshot checklist.
- Removed email-verification routes, services, UI, model middleware support, and stale account-status copy. Registration and email changes now work without SMTP; password-reset email still uses the configured mail provider.

## Remaining demonstration work

1. Run the updated feature suite on SQLite and the separate MariaDB test database.
2. Upload and verify the admin GCash QR, confirm the recipient details, and keep real payments disabled unless the payment process and customer terms are ready.
3. Configure SMTP and verify password-reset delivery if that feature will be used.
4. Capture readable screenshots of registration, artwork creation, checkout, admin review, and transferred ownership at phone and desktop widths.
5. Test a second device on the same Wi-Fi using the host PC's LAN IP, and check keyboard navigation, focus, contrast, and form errors.
6. Show the group GitHub contribution history and explain the diagrams and database relationships during the presentation.

## Evidence reviewed

- Attached RUBRICS.pdf.
- README.md, .env.example, and phpunit.mysql.xml.
- docs/PROJECT_DIAGRAMS.md and docs/DEMO_MANUAL.md.
- Laravel controllers, models, Blade templates, and migrations.
- SQLite feature run: 71 tests passed, 820 assertions.
- MariaDB feature run: 71 tests passed, 820 assertions.
- MySQL migration status for pixmoreedb: all migrations applied.
- Local health endpoint: HTTP 200.

