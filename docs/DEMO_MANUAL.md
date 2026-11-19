# Pixmoree Client and Admin Demonstration Manual

This guide is laid out as six short panels for a tri-fold handout. Use a local test database and test GCash details for the presentation. The application has no GCash API and does not verify receipts automatically. Payments are disabled by default.

## Panel 1 - Cover

**Pixmoree**  
Digital art marketplace for creators and collectors.

Demonstrate the client-to-admin workflow: register, publish an artwork, submit a purchase, review it as an administrator, and confirm the edition ownership record.

## Panel 2 - Start and sign in

1. Start XAMPP Apache and MySQL.
2. Open the local site at http://127.0.0.1:8000.
3. Select Register and create a client account with an email address and password.
4. Registration signs you in immediately; no email code is required.
5. To create an administrator for the demo, register that account and run the admin-promotion command documented in README.

Google and Facebook sign-in are not available. Email is used for account identification and password reset.

## Panel 3 - Create an artwork

1. Sign in as a client and open the creator artwork form.
2. Enter the artwork title, description, category, price, edition count, and image.
3. Submit the artwork and confirm its moderation status in the account area.
4. As admin, open the artwork moderation screen and approve or reject the submission.
5. For a multi-copy artwork, confirm each edition has a different Item ID.

## Panel 4 - Buy an edition

1. Confirm the payment switch is enabled only in the isolated demo environment.
2. As admin, open Admin Studio, then Payments. Upload a test admin GCash QR image and enter the matching recipient name and number.
3. As a signed-in client, open a listed artwork and choose one available edition item.
4. For a test GCash purchase, submit the test transaction reference and receipt screenshot.
5. Confirm the order appears as pending review and the selected item is reserved.
6. As admin, inspect the pending record and receipt. Approve or reject it.
7. After approval, confirm the buyer owns the selected Item ID and the creator receives Pixel Coins. After rejection, confirm the item is available again.

Never approve a receipt based only on its image; verify the transaction in the GCash account used for the demonstration.

## Panel 5 - Pixel Coins and withdrawal

1. Submit a top-up receipt for at least PHP 20. The current example conversion awards at most 166.66 PC per PHP 200 base top-up, adds a 1.5% fee, and caps coin awards so cash-out value does not exceed the base amount.
2. Confirm coins are not credited while the top-up is pending. Approve it as admin and verify the user's balance and ledger.
3. Buy a listed NFT with Pixel Coins and verify that the buyer is debited and the creator is credited in the same transaction.
4. In Profile settings, save a test GCash number and private payout QR.
5. Request at least 200 PC for withdrawal. Confirm the coins are reserved while the admin reviews the request.
6. As admin, either mark the request paid with a unique payout reference or reject it. Rejection must restore the reserved coins.

## Panel 6 - Admin checks and troubleshooting

- Admin routes require an administrator account.
- GCash receipts and payout QR images are private files. A client must not be able to open another user's proof or payout QR.
- If password-reset email does not arrive, check SMTP settings, spam, and the Laravel log. Email is not required for registration or purchases.
- If payment actions return service unavailable, check that payments are enabled and the admin QR is configured. Do not turn on real-money processing for a class demo.
- If another device cannot connect, open the host computer's private IPv4 address and port 8000. Do not enter localhost on the phone.
- Save screenshots of registration, creator artwork, client checkout, admin pending review, and final ownership for the presentation. Check that names, transaction IDs, and balances are readable before printing.

## Presentation evidence checklist

- [ ] Registration and immediate account access
- [ ] Creator artwork submission and admin moderation
- [ ] GCash receipt record before and after admin review
- [ ] Unique Item ID and ownership before and after approval
- [ ] Pixel Coin top-up, purchase ledger, and withdrawal decision
- [ ] MySQL migration status and automated test result
- [ ] Screenshots readable at presentation size
