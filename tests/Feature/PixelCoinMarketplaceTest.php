<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Support\PixelCoinConversion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PixelCoinMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'payments.enabled' => true, 'payments.simulation' => true,
            'payments.php_per_eth' => 200, 'payments.pixel_coin_topup_numerator' => 5,
            'payments.pixel_coin_topup_denominator' => 6, 'payments.pixel_coin_payout_php_per_coin' => 1.2,
        ]);
    }

    public function test_simulated_topup_credits_wallet_and_creates_receipt_and_notification(): void
    {
        $buyer = $this->user('topup-buyer@example.test');
        $conversion = app(PixelCoinConversion::class);
        $this->assertSame(16666, $conversion->coinsForCashMinor(20000));

        $this->from(route('user.pixel-coins'))->actingAs($buyer)
            ->post(route('user.pixel-coins.topups'), ['amount_php' => '19.99'])
            ->assertSessionHasErrors('amount_php');
        $this->assertDatabaseCount('payment_orders', 0);

        $this->post(route('user.pixel-coins.topups'), ['amount_php' => '200.00'])->assertRedirect();
        $order = PaymentOrder::query()->where('type', 'pixel_coin_topup')->firstOrFail();
        $this->assertSame('approved', $order->status);
        $this->assertSame('simulation', $order->payment_method);
        $this->assertSame(20300, (int) $order->amount_minor);
        $this->assertSame(300, (int) $order->fee_amount_minor);
        $this->assertSame(16666, (int) $buyer->fresh()->pixel_coin_balance);
        $this->assertDatabaseHas('pixel_coin_transactions', [
            'user_id' => $buyer->id, 'payment_order_id' => $order->purchase_id,
            'type' => 'topup', 'direction' => 'credit', 'amount' => 16666, 'balance_after' => 16666,
        ]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $buyer->id]);
        $this->get(route('user.purchase-orders.receipt', $order))->assertOk()->assertSee($order->purchase_id);
    }

    public function test_pixel_coin_purchase_debits_buyer_credits_creator_and_transfers_selected_edition(): void
    {
        $seller = $this->user('coin-seller@example.test');
        $buyer = $this->user('coin-buyer@example.test');
        $buyer->forceFill(['pixel_coin_balance' => 30000])->save();
        $artwork = Artwork::create([
            'id' => 'coin-artwork-' . Str::lower(Str::random(8)), 'user_id' => $seller->id,
            'title' => 'Coin artwork', 'creator' => $seller->name, 'category' => 'Digital Art',
            'price' => 1, 'item_count' => 2, 'image' => 'coin-artwork.png',
            'description' => 'Two editions.', 'status' => 'Listed',
        ]);
        $items = DB::table('artwork_items')->where('artwork_id', $artwork->id)->orderBy('edition_number')->get();

        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))->assertOk();
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id, 'item_id' => $items[0]->item_id, 'payment_method' => 'pixel_coin',
        ])->assertRedirect(route('user.purchases'));

        $order = PaymentOrder::query()->where('buyer_id', $buyer->id)->firstOrFail();
        $this->assertSame('approved', $order->status);
        $this->assertSame(16666, (int) $order->coin_amount);
        $this->assertSame(13334, (int) $buyer->fresh()->pixel_coin_balance);
        $this->assertSame(16666, (int) $seller->fresh()->pixel_coin_balance);
        $this->assertDatabaseHas('artwork_items', [
            'item_id' => $items[0]->item_id, 'owner_id' => $buyer->id,
            'status' => 'owned', 'acquired_purchase_id' => $order->purchase_id,
        ]);
        $this->assertSame(1, DB::table('ownership_transfers')->where('item_id', $items[0]->item_id)->count());
        $this->assertSame(2, DB::table('notifications')->count());
        $this->assertNull(DB::table('artwork_items')->where('item_id', $items[1]->item_id)->value('owner_id'));
    }

    public function test_simulated_withdrawal_is_immediate_and_never_requires_a_real_payout_destination(): void
    {
        $creator = $this->user('withdraw-creator@example.test');
        $creator->forceFill(['pixel_coin_balance' => 30000])->save();
        $this->actingAs($creator)->from(route('user.pixel-coins'))
            ->post(route('user.pixel-coins.withdrawals'), ['coin_amount' => '199.99'])
            ->assertSessionHasErrors('coin_amount');
        $this->assertSame(30000, (int) $creator->fresh()->pixel_coin_balance);

        $this->post(route('user.pixel-coins.withdrawals'), ['coin_amount' => '200.00'])->assertRedirect();
        $order = PaymentOrder::query()->where('type', 'creator_payout')->firstOrFail();
        $this->assertSame('paid', $order->status);
        $this->assertSame('simulation', $order->payment_method);
        $this->assertSame(10000, (int) $creator->fresh()->pixel_coin_balance);
        $this->assertSame(0, (int) $creator->fresh()->pixel_coin_reserved);
        $this->assertDatabaseHas('pixel_coin_transactions', [
            'user_id' => $creator->id, 'payment_order_id' => $order->purchase_id,
            'type' => 'withdrawal', 'direction' => 'debit', 'amount' => 20000,
        ]);
        $this->get(route('user.purchase-orders.receipt', $order))->assertOk()->assertSee('No real payment was processed');
    }

    private function user(string $email): User
    {
        return User::create(['name' => 'Test user', 'email' => $email, 'password' => 'secret-password']);
    }
}
