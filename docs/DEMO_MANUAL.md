# Pixmoree Client and Admin Demonstration Manual

Use a local database and two regular accounts. Payment-like actions run in simulation mode: do not use personal payment details, QR codes, or real money.

## Panel 1 — Project overview

Pixmoree is a Laravel digital art marketplace. Demonstrate registration, creator listings, simulated NFT purchases, receipts, edition ownership, notifications, market estimates, and admin tools.

## Panel 2 — Start and sign in

1. Start XAMPP Apache and MySQL and open `http://127.0.0.1:8000`.
2. Confirm `.env` has `PAYMENTS_ENABLED=true` and `PAYMENTS_SIMULATION=true`.
3. Register a creator account and a separate buyer account. Registration signs the user in immediately.
4. Register a third account for administration, then promote it with the `pixmoree:make-admin` command documented in README.

## Panel 3 — Create artwork

1. Sign in as the creator and open Create.
2. Enter a title, description, category, asking price, edition count, and image.
3. Confirm the uploaded image preview, create the listing, and check it in My Artwork.
4. As admin, inspect artwork moderation and analytics. Upload a different image if testing duplicate-image validation.

## Panel 4 — Simulate a purchase

1. Sign in as the buyer and open the Store.
2. Select an available edition and choose **Simulate purchase**. No payment details or proof are requested.
3. Open Purchases and view or print the receipt. Confirm the edition appears in the buyer's collection.
4. Check the creator's Notifications and Pixel Coin balance for the sale notification and simulated credit.
5. In Admin Studio → Transactions, confirm the completed purchase and receipt ID.

## Panel 5 — Pixel Coins and estimated market values

1. Simulate a Pixel Coin top-up and check its receipt and ledger credit.
2. Simulate a withdrawal and confirm the wallet debit and transaction record. No funds are sent.
3. Open the member dashboard's simulated market values panel. Explain that engagement, approved simulated sales, supply, and inactivity affect the estimates and daily movers.
4. Run the Laravel scheduler for daily price snapshots when demonstrating the gainers and decliners.

## Panel 6 — Admin tools and presentation evidence

- Admin Studio includes artwork moderation, user management, market analytics, completed simulation records, resale evidence review, and website content editing.
- Resale review concerns listing evidence only; it is not a payment review or legal copyright decision.
- Capture readable phone- and desktop-width screenshots of registration, artwork creation and preview, simulated checkout, receipt, buyer collection, notifications, market values, and Admin Studio.
- Check keyboard navigation, visible focus, contrast, and validation messages in a browser. Show the group GitHub contribution history during the presentation.

## Rubric evidence checklist

- [ ] Account registration, login, and welcome notification
- [ ] Artwork create, preview, edit, and moderation flow
- [ ] Simulated purchase, printable receipt, notification, and ownership transfer
- [ ] Simulated wallet top-up and withdrawal with ledger entries
- [ ] Admin dashboard, analytics, transaction records, resale review, and content editor
- [ ] Responsive screenshots, accessibility walkthrough, migration status, and feature suite result
