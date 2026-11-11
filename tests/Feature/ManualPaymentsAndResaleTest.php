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
            'type' => 'cash_topup',
            'amount_minor' => 10000,
            'payment_method' => 'gcash',
        ]);

        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $user->account_id);
        $this->assertNotNull($user->profile()->first());
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $artwork->product_id);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $order->purchase_id);
    }

    public function test_cash_wallet_page_shows_real_balance_reserved_amount_and_withdrawal_form(): void
    {
        $user = $this->user('wallet-page@example.test');
        $user->cash_balance_minor = 22222;
        $user->cash_reserved_minor = 1000;
        $user->save();

        $this->actingAs($user)->get(route('user.pixel-coin'))
            ->assertOk()
            ->assertSee('PHP 222.22')
            ->assertSee('approximately USD 3.83')
            ->assertSee('PHP 10.00')
            ->assertSee('Top-ups, artwork purchases, and withdrawals are disabled during beta.')
            ->assertDontSee('name="paypal_email"', false)
            ->assertSee('Cash ledger');
    }

    public function test_payment_review_uses_the_admin_theme(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.payment-orders'))
            ->assertOk()
            ->assertSee('class="cms-admin-page payment-review-page"', false)
            ->assertSee('ADMIN STUDIO')
            ->assertSee('Payment and resale review');
    }

    public function test_manual_nft_payment_does_not_transfer_ownership_before_admin_approval(): void
    {
        config(['payments.enabled' => true]);
        Storage::fake('local');
        $seller = $this->user('seller@example.test');
        $buyer = $this->user('buyer@example.test');
        $admin = $this->admin();
        $artwork = $this->artwork($seller);

        $this->actingAs($buyer)->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'payment_method' => 'gcash',
            'transaction_reference' => 'GC-REFERENCE-001',
            'proof' => $this->proofImage('proof.png'),
        ])->assertRedirect('/user/purchases');

        $order = PaymentOrder::query()->where('buyer_id', $buyer->id)->firstOrFail();
        $this->assertSame('pending_review', $order->status);
        $this->assertDatabaseMissing('artwork_ownerships', ['artwork_id' => $artwork->id]);
        $this->assertSame(0, (int) $seller->fresh()->cash_balance_minor);

        $this->actingAs($admin)->post(route('admin.payment-orders.review', $order), [
            'decision' => 'approve',
        ])->assertRedirect();

        $this->assertDatabaseHas('artwork_ownerships', [
            'artwork_id' => $artwork->id,
            'owner_id' => $buyer->id,
            'acquired_purchase_id' => $order->purchase_id,
        ]);
        $this->assertSame('approved', $order->fresh()->status);
        $this->assertGreaterThan(0, (int) $seller->fresh()->cash_balance_minor);
        $this->assertDatabaseHas('ownership_transfers', [
            'product_id' => $artwork->product_id,
            'purchase_id' => $order->purchase_id,
        ]);
    }

    public function test_unverified_user_can_sign_in_but_cannot_submit_an_nft_purchase(): void
    {
        config(['payments.enabled' => true]);
        $seller = $this->user('seller@example.test');
        $buyer = User::create([
            'name' => 'Unverified buyer',
            'email' => 'unverified-buyer@example.test',
            'password' => 'secret-password',
        ]);
        $artwork = $this->artwork($seller);

        $this->withSession(['email_verification_user_id' => $buyer->id])
            ->actingAs($buyer)
            ->post(route('user.purchase-orders.store'), [
                'artwork_id' => $artwork->id,
                'payment_method' => 'gcash',
                'transaction_reference' => 'UNVERIFIED-BUYER-001',
            ])
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('payment_orders', 0);
        $this->get(route('verification.notice'))->assertOk()->assertSee($buyer->email);
    }

    public function test_withdrawal_reserves_funds_until_transfer_is_recorded_and_releases_after_rejection(): void
    {
        config(['payments.enabled' => true]);
        $user = $this->user('withdrawal@example.test');
        $user->cash_balance_minor = 30000;
        $user->save();
        $admin = $this->admin();

        $this->actingAs($user)->post(route('user.cash.withdrawals'), [
            'amount_php' => 100,
            'paypal_email' => 'payee@example.test',
        ])->assertRedirect();
        $order = PaymentOrder::query()->where('type', 'withdrawal')->firstOrFail();
        $this->assertSame(30000, (int) $user->fresh()->cash_balance_minor);
        $this->assertSame(10000, (int) $user->fresh()->cash_reserved_minor);

        $this->actingAs($admin)->post(route('admin.payment-orders.review', $order), [
            'decision' => 'approve',
        ])->assertRedirect();
        $this->assertSame('payout_approved', $order->fresh()->status);
        $this->assertSame(30000, (int) $user->fresh()->cash_balance_minor);
        $this->assertSame(10000, (int) $user->fresh()->cash_reserved_minor);

        $this->actingAs($admin)->post(route('admin.payment-orders.review', $order), [
            'decision' => 'mark_paid',
            'payout_reference' => 'PAYPAL-SENT-001',
        ])->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('PAYPAL-SENT-001', $order->fresh()->payout_reference);
        $this->assertSame(20000, (int) $user->fresh()->cash_balance_minor);
        $this->assertSame(0, (int) $user->fresh()->cash_reserved_minor);
        $this->assertDatabaseHas('cash_ledger_entries', [
            'purchase_id' => $order->purchase_id,
            'type' => 'paypal_payout',
            'direction' => 'debit',
        ]);

        $this->actingAs($user)->post(route('user.cash.withdrawals'), [
            'amount_php' => 100,
            'paypal_email' => 'payee@example.test',
        ])->assertRedirect();
        $rejectedOrder = PaymentOrder::query()->where('type', 'withdrawal')->where('status', 'pending_review')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.payment-orders.review', $rejectedOrder), [
            'decision' => 'approve',
        ])->assertRedirect();
        $this->post(route('admin.payment-orders.review', $rejectedOrder), [
            'decision' => 'mark_paid',
            'payout_reference' => 'PAYPAL-SENT-001',
        ])->assertRedirect()->assertSessionHasErrors('payout_reference');
        $this->assertSame(10000, (int) $user->fresh()->cash_reserved_minor);
        $this->actingAs($admin)->post(route('admin.payment-orders.review', $rejectedOrder), [
            'decision' => 'reject',
        ])->assertRedirect();
        $this->assertSame(0, (int) $user->fresh()->cash_reserved_minor);
        $this->assertSame(20000, (int) $user->fresh()->cash_balance_minor);
    }

    public function test_prepaid_load_credits_pixel_coin_only_after_review_at_configured_rate(): void
    {
        config(['payments.enabled' => true]);
        Storage::fake('local');
        config(['payments.pixel_coin_per_php' => 100]);
        $user = $this->user('prepaid@example.test');
        $admin = $this->admin();

        $this->actingAs($user)->post(route('user.pixel.prepaid-topups'), [
            'amount_php' => 50,
            'transaction_reference' => 'LOAD-REFERENCE-001',
            'proof' => $this->proofImage('load.png'),
        ])->assertRedirect();
        $order = PaymentOrder::query()->where('type', 'pixel_topup')->firstOrFail();
        $this->assertSame(0, (int) $user->fresh()->pixel_coin_balance);
        $this->assertSame(0, (int) $user->fresh()->cash_balance_minor);

        $this->actingAs($admin)->post(route('admin.payment-orders.review', $order), [
            'decision' => 'approve',
        ])->assertRedirect();

        $this->assertSame(5000, (int) $user->fresh()->pixel_coin_balance);
        $this->assertSame(0, (int) $user->fresh()->cash_balance_minor);
        $this->assertDatabaseHas('pixel_coin_transactions', [
            'user_id' => $user->id,
            'type' => 'prepaid_load_top_up',
            'amount' => 5000,
        ]);
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

        $this->actingAs($seller)->post(route('user.artworks.resale', $artwork), [
            'price_php' => 200,
            'ownership_declaration' => '1',
            'evidence' => $this->proofImage('ownership.png'),
        ])->assertRedirect();
        $listingId = DB::table('resale_listings')->value('id');
        $this->assertSame('pending_review', DB::table('resale_listings')->where('id', $listingId)->value('status'));

        $this->actingAs($admin)->post(route('admin.resales.review', $listingId), ['decision' => 'approve'])->assertRedirect();
        $this->actingAs($buyer)->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'payment_method' => 'paypal',
            'transaction_reference' => 'PAYPAL-RESALE-001',
            'proof' => $this->proofImage('payment.png'),
        ])->assertRedirect('/user/purchases');
        $resaleOrder = PaymentOrder::query()->where('buyer_id', $buyer->id)->where('type', 'nft_purchase')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.payment-orders.review', $resaleOrder), ['decision' => 'approve'])->assertRedirect();
        $this->assertSame($buyer->id, (int) DB::table('artwork_ownerships')->where('artwork_id', $artwork->id)->value('owner_id'));
        $this->assertSame('sold', DB::table('resale_listings')->where('id', $listingId)->value('status'));
        $this->assertSame(20000, (int) $seller->fresh()->cash_balance_minor);
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

    public function test_purchase_top_up_withdrawal_and_resale_actions_are_rejected_during_beta(): void
    {
        config(['payments.enabled' => false]);
        $user = $this->user('disabled-payments@example.test');
        $seller = $this->user('disabled-seller@example.test');
        $artwork = $this->artwork($seller);

        $this->actingAs($user)->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'payment_method' => 'gcash',
            'transaction_reference' => 'UNUSED-BETA-REF',
        ])->assertStatus(503);
        $this->post(route('user.cash.topups'))->assertStatus(503);
        $this->post(route('user.pixel.prepaid-topups'))->assertStatus(503);
        $this->post(route('user.cash.withdrawals'))->assertStatus(503);
        $this->post(route('user.artworks.resale', $artwork))->assertStatus(503);
        $this->postJson('/api/pixel-coins/top-ups', ['amount' => 1000])->assertStatus(503);
        $this->postJson('/api/pixel-coins/purchases', ['artwork_id' => $artwork->id])->assertStatus(503);

        $this->assertDatabaseCount('payment_orders', 0);
        $this->assertDatabaseCount('resale_listings', 0);
        $this->assertDatabaseCount('pixel_coin_transactions', 0);
        $this->assertSame(0, (int) $user->fresh()->cash_reserved_minor);
        $this->assertSame(0, (int) $user->fresh()->pixel_coin_balance);
    }
}
