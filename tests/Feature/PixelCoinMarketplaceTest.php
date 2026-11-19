<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Support\PixelCoinConversion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PixelCoinMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'payments.enabled' => true,
            'payments.php_per_eth' => 200,
            'payments.pixel_coin_topup_numerator' => 5,
            'payments.pixel_coin_topup_denominator' => 6,
            'payments.pixel_coin_payout_php_per_coin' => 1.2,
        ]);
        Storage::fake('local');
    }

    public function test_topup_waits_for_admin_approval_and_credits_the_safe_conversion_amount(): void
    {
        $buyer = $this->user('topup-buyer@example.test');
        $admin = $this->admin();
        $this->configureAdminQr();
        $conversion = app(PixelCoinConversion::class);

        $this->assertSame(16666, $conversion->coinsForCashMinor(20000));
        $this->assertLessThanOrEqual(20000, (int) round($conversion->coinsForCashMinor(20000) * 1.2));

        $this->from(route('user.pixel-coins'))
            ->actingAs($buyer)
            ->post(route('user.pixel-coins.topups'), [
                'amount_php' => '19.99',
                'transaction_reference' => 'TOPUP-TOO-SMALL',
                'proof' => $this->proofImage('small.png'),
            ])
            ->assertSessionHasErrors('amount_php');
        $this->assertDatabaseCount('payment_orders', 0);

        $this->actingAs($buyer)
            ->post(route('user.pixel-coins.topups'), [
                'amount_php' => '200.00',
                'transaction_reference' => 'TOPUP-200-001',
                'proof' => $this->proofImage('topup.png'),
            ])
            ->assertRedirect();

        $order = PaymentOrder::query()->where('type', 'pixel_coin_topup')->firstOrFail();
        $this->assertSame('pending_review', $order->status);
        $this->assertSame(20300, (int) $order->amount_minor);
        $this->assertSame(300, (int) $order->fee_amount_minor);
        $this->assertSame(16666, (int) $order->coin_amount);
        $this->assertSame(0, (int) $buyer->fresh()->pixel_coin_balance);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $order->purchase_id);
        Storage::disk('local')->assertExists($order->proof_path);

        $this->actingAs($buyer)
            ->from(route('user.pixel-coins'))
            ->post(route('user.pixel-coins.topups'), [
                'amount_php' => '200.00',
                'transaction_reference' => 'TOPUP-200-001',
                'proof' => $this->proofImage('duplicate.png'),
            ])
            ->assertSessionHasErrors('transaction_reference');

        $this->actingAs($admin)
            ->get(route('admin.payment-orders'))
            ->assertOk()
            ->assertSee('TOPUP-200-001')
            ->assertSee('166.66 PC');
        $this->actingAs($admin)
            ->post(route('admin.payment-orders.review', $order), ['decision' => 'approve'])
            ->assertRedirect();

        $this->assertSame('approved', $order->fresh()->status);
        $this->assertSame(16666, (int) $buyer->fresh()->pixel_coin_balance);
        $this->assertDatabaseHas('pixel_coin_transactions', [
            'user_id' => $buyer->id,
            'payment_order_id' => $order->purchase_id,
            'type' => 'topup',
            'direction' => 'credit',
            'amount' => 16666,
            'balance_after' => 16666,
        ]);
        $this->assertDatabaseHas('pixel_coin_transactions', [
            'transaction_id' => DB::table('pixel_coin_transactions')->where('payment_order_id', $order->purchase_id)->value('transaction_id'),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.payment-orders.review', $order), ['decision' => 'approve'])
            ->assertStatus(409);
        $this->assertSame(1, DB::table('pixel_coin_transactions')->where('payment_order_id', $order->purchase_id)->count());
    }

    public function test_pixel_coin_purchase_debits_buyer_credits_creator_and_transfers_only_the_selected_item(): void
    {
        $seller = $this->user('coin-seller@example.test');
        $buyer = $this->user('coin-buyer@example.test');
        $anotherBuyer = $this->user('coin-second-buyer@example.test');
        $buyer->forceFill(['pixel_coin_balance' => 30000])->save();
        $anotherBuyer->forceFill(['pixel_coin_balance' => 30000])->save();
        $artwork = Artwork::create([
            'id' => 'two-copy-coin-artwork',
            'user_id' => $seller->id,
            'title' => 'Two copy coin artwork',
            'creator' => $seller->name,
            'category' => 'Digital Art',
            'price' => 1,
            'item_count' => 2,
            'image' => 'two-copy.png',
            'description' => 'Two uniquely numbered editions.',
            'status' => 'Listed',
        ]);
        $items = DB::table('artwork_items')->where('artwork_id', $artwork->id)->orderBy('edition_number')->get();
        $this->assertCount(2, $items);
        $this->assertNotSame($items[0]->item_id, $items[1]->item_id);

        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))->assertOk();
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'item_id' => $items[0]->item_id,
            'payment_method' => 'pixel_coin',
        ])->assertRedirect(route('user.purchases'));

        $order = PaymentOrder::query()->where('buyer_id', $buyer->id)->firstOrFail();
        $this->assertSame('approved', $order->status);
        $this->assertSame(16666, (int) $order->coin_amount);
        $this->assertSame(13334, (int) $buyer->fresh()->pixel_coin_balance);
        $this->assertSame(16666, (int) $seller->fresh()->pixel_coin_balance);
        $this->assertDatabaseHas('artwork_items', [
            'item_id' => $items[0]->item_id,
            'owner_id' => $buyer->id,
            'status' => 'owned',
            'acquired_purchase_id' => $order->purchase_id,
        ]);
        $this->assertDatabaseHas('ownership_transfers', [
            'item_id' => $items[0]->item_id,
            'from_user_id' => $seller->id,
            'to_user_id' => $buyer->id,
            'purchase_id' => $order->purchase_id,
        ]);
        $this->assertDatabaseHas('pixel_coin_transactions', [
            'user_id' => $buyer->id,
            'payment_order_id' => $order->purchase_id,
            'direction' => 'debit',
            'amount' => 16666,
        ]);
        $this->assertDatabaseHas('pixel_coin_transactions', [
            'user_id' => $seller->id,
            'payment_order_id' => $order->purchase_id,
            'direction' => 'credit',
            'amount' => 16666,
        ]);

        $this->actingAs($anotherBuyer)->get(route('user.checkout', $artwork->id))->assertOk();
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'item_id' => $items[0]->item_id,
            'payment_method' => 'pixel_coin',
        ])->assertStatus(409);
        $this->assertSame(30000, (int) $anotherBuyer->fresh()->pixel_coin_balance);
        $this->assertSame(1, DB::table('ownership_transfers')->where('item_id', $items[0]->item_id)->count());
    }

    public function test_withdrawal_minimum_reserves_coins_and_admin_rejection_refunds_them(): void
    {
        $creator = $this->user('withdraw-creator@example.test');
        $admin = $this->admin();
        $creator->forceFill(['pixel_coin_balance' => 30000])->save();
        $this->configureCreatorGcash($creator);

        $this->from(route('user.pixel-coins'))
            ->actingAs($creator)
            ->post(route('user.pixel-coins.withdrawals'), ['coin_amount' => '199.99'])
            ->assertSessionHasErrors('coin_amount');
        $this->assertSame(30000, (int) $creator->fresh()->pixel_coin_balance);

        $this->actingAs($creator)
            ->post(route('user.pixel-coins.withdrawals'), ['coin_amount' => '200.00'])
            ->assertRedirect();
        $order = PaymentOrder::query()->where('type', 'creator_payout')->firstOrFail();
        $this->assertSame('pending_review', $order->status);
        $this->assertSame(24000, (int) $order->amount_minor);
        $this->assertSame(20000, (int) $order->coin_amount);
        $this->assertSame(10000, (int) $creator->fresh()->pixel_coin_balance);
        $this->assertSame(20000, (int) $creator->fresh()->pixel_coin_reserved);

        $this->actingAs($admin)
            ->post(route('admin.payment-orders.withdrawal-review', $order), ['decision' => 'reject'])
            ->assertRedirect();
        $this->assertSame('rejected', $order->fresh()->status);
        $this->assertSame(30000, (int) $creator->fresh()->pixel_coin_balance);
        $this->assertSame(0, (int) $creator->fresh()->pixel_coin_reserved);
        $this->assertDatabaseHas('pixel_coin_transactions', [
            'user_id' => $creator->id,
            'payment_order_id' => $order->purchase_id,
            'type' => 'withdrawal_refund',
            'direction' => 'credit',
            'amount' => 20000,
            'balance_after' => 30000,
        ]);
        $this->actingAs($admin)
            ->post(route('admin.payment-orders.withdrawal-review', $order), ['decision' => 'paid', 'payout_reference' => 'PAID-TOO-LATE'])
            ->assertStatus(409);
    }

    public function test_withdrawal_paid_decision_records_unique_payout_reference_and_clears_reservation(): void
    {
        $creator = $this->user('paid-withdraw-creator@example.test');
        $admin = $this->admin();
        $creator->forceFill(['pixel_coin_balance' => 25000])->save();
        $this->configureCreatorGcash($creator);
        $this->actingAs($creator)->post(route('user.pixel-coins.withdrawals'), ['coin_amount' => '200'])
            ->assertRedirect();
        $order = PaymentOrder::query()->where('type', 'creator_payout')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.payment-orders.withdrawal-review', $order), [
                'decision' => 'paid',
                'payout_reference' => 'GCASH-PAYOUT-001',
            ])
            ->assertRedirect();
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame('GCASH-PAYOUT-001', $order->fresh()->payout_reference);
        $this->assertSame(0, (int) $creator->fresh()->pixel_coin_reserved);

        $otherCreator = $this->user('second-paid-withdraw@example.test');
        $otherCreator->forceFill(['pixel_coin_balance' => 20000])->save();
        $this->configureCreatorGcash($otherCreator);
        $this->actingAs($otherCreator)->post(route('user.pixel-coins.withdrawals'), ['coin_amount' => '200'])
            ->assertRedirect();
        $otherOrder = PaymentOrder::query()->where('buyer_id', $otherCreator->id)->where('type', 'creator_payout')->firstOrFail();
        $this->actingAs($admin)
            ->from(route('admin.payment-orders'))
            ->post(route('admin.payment-orders.withdrawal-review', $otherOrder), [
                'decision' => 'paid',
                'payout_reference' => 'GCASH-PAYOUT-001',
            ])
            ->assertSessionHasErrors('payout_reference');
        $this->assertSame('pending_review', $otherOrder->fresh()->status);
    }

    private function configureAdminQr(): void
    {
        $path = 'admin-payment-qrs/test-admin.png';
        Storage::disk('local')->put($path, 'private admin QR');
        DB::table('admin_payment_settings')->where('id', 1)->update([
            'gcash_qr_path' => $path,
            'gcash_account_name' => 'Pixmoree Admin',
            'gcash_number' => '09170000000',
        ]);
    }

    private function configureCreatorGcash(User $user): void
    {
        $path = 'creator-payment-qrs/' . $user->id . '.png';
        Storage::disk('local')->put($path, 'private creator QR');
        $user->forceFill(['gcash_number' => '09170000000', 'gcash_qr_path' => $path])->save();
    }

    private function user(string $email): User
    {
        $user = User::create(['name' => 'Test user', 'email' => $email, 'password' => 'secret-password']);

        return $user;
    }

    private function admin(): User
    {
        $user = $this->user('admin-' . Str::uuid() . '@example.test');
        $user->forceFill(['role' => 'admin'])->save();

        return $user;
    }

    private function proofImage(string $filename): UploadedFile
    {
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/y5sAAAAASUVORK5CYII=', true);
        $this->assertIsString($contents);

        return UploadedFile::fake()->createWithContent($filename, $contents);
    }
}
