<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\PaymentOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManualPaymentsAndResaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_product_and_purchase_identifiers_are_persisted(): void
    {
        $user = $this->user('identity@example.test');
        $artwork = $this->artwork($user);
        $order = PaymentOrder::create([
            'purchase_id' => (string) Str::uuid(),
            'buyer_id' => $user->id,
            'type' => 'nft_purchase',
            'amount_minor' => 10000,
            'payment_method' => 'gcash',
        ]);

        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $user->account_id);
        $this->assertNotNull($user->profile()->first());
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $artwork->product_id);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $order->purchase_id);
    }

    public function test_payment_review_uses_admin_theme_and_only_lists_nft_receipts(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.payment-orders'))
            ->assertOk()
            ->assertSee('class="cms-admin-page payment-review-page"', false)
            ->assertSee('ADMIN STUDIO')
            ->assertSee('NFT purchase receipts')
            ->assertSee('There is no automatic receipt-verification API.');
    }

    public function test_creator_qr_checkout_requires_a_receipt_and_admin_approval_before_transfer(): void
    {
        config(['payments.enabled' => true]);
        Storage::fake('local');
        $seller = $this->user('seller@example.test');
        $buyer = $this->user('buyer@example.test');
        $admin = $this->admin();
        $artwork = $this->artwork($seller);
        $seller->forceFill(['gcash_qr_path' => 'creator-payment-qrs/seller-gcash.png'])->save();
        Storage::disk('local')->put('creator-payment-qrs/seller-gcash.png', 'private qr');

        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))
            ->assertOk()
            ->assertSee('PHP 200,000.00')
            ->assertSee(route('user.checkout.qr', ['artworkId' => $artwork->id, 'method' => 'gcash']))
            ->assertSee('Receipt checks are manual; there is no automatic payment-verification API.');
        $this->get(route('user.checkout.qr', ['artworkId' => $artwork->id, 'method' => 'gcash']))->assertOk();
        $this->get(route('user.checkout.qr', ['artworkId' => $artwork->id, 'method' => 'maya']))->assertNotFound();

        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'payment_method' => 'gcash',
            'transaction_reference' => 'GC-REFERENCE-001',
            'proof' => $this->proofImage('proof.png'),
        ])->assertRedirect('/user/purchases');

        $order = PaymentOrder::query()->where('buyer_id', $buyer->id)->firstOrFail();
        $this->assertSame('pending_review', $order->status);
        $this->assertSame(20000000, $order->amount_minor);
        $this->assertDatabaseMissing('artwork_ownerships', ['artwork_id' => $artwork->id]);
        $this->assertSame(0, (int) $seller->fresh()->cash_balance_minor);

        $this->actingAs($admin)->get(route('admin.payment-orders'))
            ->assertOk()
            ->assertSee('GC-REFERENCE-001')
            ->assertSee('PHP 200,000.00')
            ->assertSee('View private proof');
        $this->post(route('admin.payment-orders.review', $order), ['decision' => 'approve'])->assertRedirect();

        $this->assertDatabaseHas('artwork_ownerships', [
            'artwork_id' => $artwork->id,
            'owner_id' => $buyer->id,
            'acquired_purchase_id' => $order->purchase_id,
        ]);
        $this->assertSame('approved', $order->fresh()->status);
        $this->assertSame(0, (int) $seller->fresh()->cash_balance_minor);
        $this->assertDatabaseHas('ownership_transfers', [
            'product_id' => $artwork->product_id,
            'purchase_id' => $order->purchase_id,
        ]);
    }

    public function test_checkout_explains_when_a_creator_has_not_configured_a_payment_qr(): void
    {
        config(['payments.enabled' => true]);
        $seller = $this->user('unconfigured-seller@example.test');
        $buyer = $this->user('unconfigured-buyer@example.test');
        $artwork = $this->artwork($seller);

        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))
            ->assertOk()
            ->assertSee('This creator has not configured a GCash or Maya QR code yet.')
            ->assertDontSee('name="transaction_reference"', false);
    }

    public function test_creator_payment_qr_uploads_are_private_and_can_be_shown_to_verified_buyers(): void
    {
        config(['payments.enabled' => true]);
        Storage::fake('local');
        $seller = $this->user('qr-seller@example.test');
        $buyer = $this->user('qr-buyer@example.test');
        $artwork = $this->artwork($seller);

        $this->actingAs($seller)->post(route('user.payment-qr.update'), [
            'gcash_qr' => $this->proofImage('seller-qr.png'),
        ])->assertRedirect(route('user.profile-settings'));

        $path = $seller->fresh()->gcash_qr_path;
        $this->assertIsString($path);
        Storage::disk('local')->assertExists($path);
        $this->actingAs($seller)->get(route('user.payment-qr.preview', 'gcash'))->assertOk();
        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))->assertOk();
        $this->actingAs($buyer)->get(route('user.checkout.qr', ['artworkId' => $artwork->id, 'method' => 'gcash']))->assertOk();
        $this->actingAs($buyer)->get(route('user.payment-qr.preview', 'gcash'))->assertNotFound();
    }

    public function test_unverified_user_is_redirected_before_checkout_or_receipt_submission(): void
    {
        config(['payments.enabled' => true]);
        $seller = $this->user('seller-unverified@example.test');
        $buyer = User::create([
            'name' => 'Unverified buyer',
            'email' => 'unverified-buyer@example.test',
            'password' => 'secret-password',
        ]);
        $artwork = $this->artwork($seller);

        $this->withSession(['email_verification_user_id' => $buyer->id])
            ->actingAs($buyer)
            ->get(route('user.checkout', $artwork->id))
            ->assertRedirect(route('verification.notice'));
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'payment_method' => 'gcash',
            'transaction_reference' => 'UNVERIFIED-BUYER-001',
            'proof' => $this->proofImage('proof.png'),
        ])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('payment_orders', 0);
        $this->get(route('verification.notice'))->assertOk()->assertSee($buyer->email);
    }

    public function test_checkout_rejects_receipts_if_the_listing_price_changes_after_checkout(): void
    {
        config(['payments.enabled' => true]);
        Storage::fake('local');
        $seller = $this->user('changing-price-seller@example.test');
        $buyer = $this->user('changing-price-buyer@example.test');
        $artwork = $this->artwork($seller);
        $seller->forceFill(['gcash_qr_path' => 'creator-payment-qrs/changing-price.png'])->save();
        Storage::disk('local')->put('creator-payment-qrs/changing-price.png', 'private qr');

        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))->assertOk();
        $artwork->update(['price' => 2]);

        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'payment_method' => 'gcash',
            'transaction_reference' => 'STALE-PRICE-001',
            'proof' => $this->proofImage('proof.png'),
        ])->assertSessionHasErrors('payment_method');
        $this->assertDatabaseCount('payment_orders', 0);
    }

    public function test_owned_artwork_requires_manual_resale_review_and_transfers_to_the_next_buyer(): void
    {
        config(['payments.enabled' => true]);
        Storage::fake('local');
        $creator = $this->user('creator@example.test');
        $seller = $this->user('reseller@example.test');
        $buyer = $this->user('resale-buyer@example.test');
        $admin = $this->admin();
        $artwork = $this->artwork($creator);
        $originalOrder = PaymentOrder::create([
            'purchase_id' => (string) Str::uuid(),
            'buyer_id' => $seller->id,
            'seller_id' => $creator->id,
            'artwork_id' => $artwork->id,
            'type' => 'nft_purchase',
            'amount_minor' => 10000,
            'payment_method' => 'gcash',
            'status' => 'approved',
        ]);
        DB::table('artwork_ownerships')->insert([
            'artwork_id' => $artwork->id,
            'owner_id' => $seller->id,
            'acquired_purchase_id' => $originalOrder->purchase_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $seller->forceFill(['maya_qr_path' => 'creator-payment-qrs/reseller-maya.png'])->save();
        Storage::disk('local')->put('creator-payment-qrs/reseller-maya.png', 'private qr');

        $this->actingAs($seller)->post(route('user.artworks.resale', $artwork), [
            'price_php' => 200,
            'ownership_declaration' => '1',
            'evidence' => $this->proofImage('ownership.png'),
        ])->assertRedirect();
        $listingId = DB::table('resale_listings')->value('id');
        $this->assertSame('pending_review', DB::table('resale_listings')->where('id', $listingId)->value('status'));
        $this->actingAs($admin)->post(route('admin.resales.review', $listingId), ['decision' => 'approve'])->assertRedirect();

        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))->assertOk()->assertSee('PHP 200.00');
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'payment_method' => 'maya',
            'transaction_reference' => 'MAYA-RESALE-001',
            'proof' => $this->proofImage('payment.png'),
        ])->assertRedirect('/user/purchases');
        $resaleOrder = PaymentOrder::query()->where('buyer_id', $buyer->id)->where('type', 'nft_purchase')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.payment-orders.review', $resaleOrder), ['decision' => 'approve'])->assertRedirect();
        $this->assertSame($buyer->id, (int) DB::table('artwork_ownerships')->where('artwork_id', $artwork->id)->value('owner_id'));
        $this->assertSame('sold', DB::table('resale_listings')->where('id', $listingId)->value('status'));
        $this->assertSame(0, (int) $seller->fresh()->cash_balance_minor);
    }

    public function test_store_hides_buy_actions_for_the_current_owner_and_surfaces_resales(): void
    {
        config(['payments.enabled' => true]);
        $creator = $this->user('resale-creator@example.test');
        $resaleSeller = $this->user('resale-seller@example.test');
        $creatorArtwork = $this->artwork($creator);
        $resaleArtwork = $this->artwork($creator);

        DB::table('artwork_ownerships')->insert([
            'artwork_id' => $resaleArtwork->id,
            'owner_id' => $resaleSeller->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('resale_listings')->insert([
            'artwork_id' => $resaleArtwork->id,
            'seller_id' => $resaleSeller->id,
            'price_minor' => 25000,
            'evidence_path' => 'resale-evidence/ownership.png',
            'ownership_declaration' => 'Seller confirms ownership.',
            'status' => 'listed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Resale gallery')
            ->assertSee('Log in to buy');

        $this->actingAs($creator)->get(route('user.checkout', ['artworkId' => $creatorArtwork->id]))
            ->assertStatus(422);
        $this->actingAs($creator)->get(route('store'))
            ->assertOk()
            ->assertSee($creatorArtwork->title)
            ->assertSee('Your artwork')
            ->assertDontSee(route('user.checkout', ['artworkId' => $creatorArtwork->id]), false)
            ->assertSee('Buy resale')
            ->assertSee(route('user.checkout', ['artworkId' => $resaleArtwork->id]), false);
        $this->get(route('user.checkout', ['artworkId' => $resaleArtwork->id]))
            ->assertOk()
            ->assertSee('PHP 250.00');

        $this->actingAs($resaleSeller)->get(route('store'))
            ->assertOk()
            ->assertSee('Your resale listing')
            ->assertDontSee(route('user.checkout', ['artworkId' => $resaleArtwork->id]), false);
        $this->get(route('user.checkout', ['artworkId' => $resaleArtwork->id]))
            ->assertStatus(422);

        $this->get(route('user'))
            ->assertOk()
            ->assertSee('Resale gallery')
            ->assertSee('Your owned artwork');
        $this->get('/user/resales')->assertRedirect('/user#resale');
    }

    public function test_payment_switch_and_wallet_routes_are_removed_or_disabled_safely(): void
    {
        config(['payments.enabled' => false]);
        $buyer = $this->user('disabled-payments@example.test');
        $seller = $this->user('disabled-seller@example.test');
        $artwork = $this->artwork($seller);

        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))->assertStatus(503);
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'payment_method' => 'gcash',
            'transaction_reference' => 'UNUSED-BETA-REF',
            'proof' => $this->proofImage('proof.png'),
        ])->assertStatus(503);
        $this->get('/user/pixel-coin')->assertRedirect(route('user.purchases'));
        $this->post('/user/pixel-coin/top-ups')->assertNotFound();
        $this->post('/user/pixel-coin/withdrawals')->assertNotFound();
        $this->get('/api/pixel-coins')->assertNotFound();

        $this->assertDatabaseCount('payment_orders', 0);
        $this->assertSame(0, (int) $buyer->fresh()->cash_reserved_minor);
    }

    private function user(string $email): User
    {
        $user = User::create(['name' => 'Test user', 'email' => $email, 'password' => 'secret-password']);
        $user->markEmailAsVerified();

        return $user;
    }

    private function admin(): User
    {
        $admin = $this->user('admin-' . Str::uuid() . '@example.test');
        $admin->role = 'admin';
        $admin->save();

        return $admin;
    }

    private function artwork(User $creator): Artwork
    {
        return Artwork::create([
            'id' => 'test-artwork-' . Str::lower(Str::random(8)),
            'user_id' => $creator->id,
            'title' => 'Test artwork',
            'creator' => $creator->name,
            'category' => 'Digital Art',
            'price' => 1,
            'item_count' => 1,
            'image' => 'test-artwork-' . Str::lower(Str::random(8)) . '.png',
            'description' => 'A test artwork.',
            'status' => 'Listed',
        ]);
    }

    private function proofImage(string $filename): UploadedFile
    {
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/y5sAAAAASUVORK5CYII=', true);
        $this->assertIsString($contents);

        return UploadedFile::fake()->createWithContent($filename, $contents);
    }
}
