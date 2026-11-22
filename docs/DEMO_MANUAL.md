# Pixmoree Client and Admin Demonstration Manual

Use a local database and two regular accounts. Purchases and creator withdrawals run in simulation mode. Do not enter personal payment details or use real money.

## Panel 1 - Project overview

Pixmoree is a Laravel digital art marketplace. Demonstrate account registration, creator listings, simulated NFT purchases, receipts, edition ownership, notifications, creator earnings, market estimates, and admin tools.

## Panel 2 - Start and sign in

1. Start XAMPP Apache and MySQL, then open `http://127.0.0.1:8000`.
2. Confirm `.env` has `PAYMENTS_ENABLED=true` and `PAYMENTS_SIMULATION=true`.
3. Register a creator account and a separate buyer account. Registration signs each user in immediately.
4. Register a third account for administration, then promote it with the `pixmoree:make-admin` command in README.

## Panel 3 - Create artwork

1. Sign in as the creator and open Create.
2. Enter a title, description, category, asking price, edition count, and image.
3. Confirm the uploaded image preview, create the listing, and check it in My Artwork.
4. As admin, review artwork moderation and analytics. Upload a different image if testing duplicate-image validation.

## Panel 4 - Simulate a purchase

1. Sign in as the buyer and open the Store.
2. Select an available edition and choose **Simulate purchase**. No payment details or proof are requested.
3. Open Purchases and view or print the receipt. Confirm it shows the receipt ID, edition number, unique item ID, seller-to-buyer ownership transfer, and simulation-only amount.
4. Open Me > Owned and follow the receipt link on the acquired edition. The receipt should identify the same item.
5. Check the creator's Notifications and Earnings page for the sale notification and simulated PHP earnings credit. Open the sale receipt from Recent artwork sales.
6. In Admin Studio > Transactions, confirm the completed purchase and receipt ID.

## Panel 5 - Simulate a creator withdrawal

1. Sign in as the creator and open Earnings after a simulated sale has been recorded.
2. Enter an amount no greater than the simulated earnings balance and choose **Simulate withdrawal**.
3. Confirm the balance and ledger update, then open the withdrawal receipt from Recent simulated withdrawals.
4. Explain that no cash payout was made and no bank, GCash, or other payout destination is used.
5. Open the member dashboard's market values panel. Explain that engagement, approved simulated sales, edition supply, and inactivity affect estimates and daily movers.
6. Run the Laravel scheduler for daily price snapshots when demonstrating gainers and decliners.

## Panel 6 - Admin tools and presentation evidence

- Admin Studio includes artwork moderation, user management, market analytics, simulated transaction records, resale evidence review, and website content editing.
- Resale review concerns listing evidence only; it is not a payment review or legal copyright decision.
- Capture readable phone- and desktop-width screenshots of registration, artwork creation and preview, simulated checkout, purchase receipt, buyer collection, notifications, creator earnings, withdrawal receipt, market values, and Admin Studio.
- Check keyboard navigation, visible focus, contrast, and validation messages in a browser. Show the group GitHub contribution history during the presentation.

## Rubric evidence checklist

- [ ] Account registration, login, and welcome notification
- [ ] Local password reset redirects directly from email submission to new password creation
- [ ] Artwork create, preview, edit, and moderation flow
- [ ] Simulated purchase, printable receipt, notification, and ownership transfer
- [ ] Simulated creator earnings and withdrawal with a receipt and ledger entry
- [ ] Admin dashboard, analytics, transaction records, resale review, and content editor
- [ ] Responsive screenshots, accessibility walkthrough, migration status, and feature suite result
