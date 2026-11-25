<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\PaymentOrder;
use App\Models\User;
use App\Support\ArtworkPriceDashboard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManualPaymentsAndResaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['payments.enabled' => true, 'payments.simulation' => true]);
    }

    public function test_simulated_purchase_issues_receipt_transfers_item_notifies_both_accounts_and_credits_creator_earnings(): void
    {
        $seller = $this->user('seller@example.test');
        $buyer = $this->user('buyer@example.test');
        $artwork = $this->artwork($seller);
        $itemId = DB::table('artwork_items')->where('artwork_id', $artwork->id)->value('item_id');

        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))
            ->assertOk()
            ->assertSee('ORDER CHECKOUT')
            ->assertSee('Choose a payment account')
            ->assertSee('value="card"', false)
            ->assertSee('value="bank_transfer"', false)
            ->assertSee('value="gcash"', false)
            ->assertSee('value="maya"', false)
            ->assertSee('name="simulation_reference"', false)
            ->assertSee('No payment provider is connected.')
            ->assertSee(asset('css/store.css'), false)
            ->assertSee('Choose the edition item');
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'item_id' => $itemId,
            'payment_method' => 'simulation',
            'checkout_option' => 'card',
            'simulation_reference' => 'DEMO-PURCHASE-001',
        ])->assertRedirect(route('user.purchases'))->assertSessionHas('status');

        $order = PaymentOrder::query()->where('buyer_id', $buyer->id)->firstOrFail();
        $this->assertSame('approved', $order->status);
        $this->assertSame('simulation', $order->payment_method);
        $this->assertSame('card', $order->payment_provider);
        $this->assertSame('DEMO-PURCHASE-001', $order->simulation_reference);
        $this->assertSame($buyer->id, (int) DB::table('artwork_items')->where('item_id', $itemId)->value('owner_id'));
        $this->assertDatabaseHas('ownership_transfers', ['item_id' => $itemId, 'purchase_id' => $order->purchase_id]);
        $this->assertSame((int) $order->amount_minor, (int) $seller->fresh()->cash_balance_minor);
        $this->assertDatabaseHas('cash_ledger_entries', [
            'user_id' => $seller->id,
            'purchase_id' => $order->purchase_id,
            'type' => 'sale',
            'direction' => 'credit',
            'amount_minor' => $order->amount_minor,
        ]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $buyer->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $seller->id]);

        $this->get(route('user.purchase-orders.receipt', $order))
            ->assertOk()
            ->assertSee($order->purchase_id)
            ->assertSee('TRANSACTION RECORD')
            ->assertSee('Edition ownership transfer')
            ->assertSee($seller->name)
            ->assertSee($buyer->name)
            ->assertSee('Payment account')
            ->assertSee('Simulation reference')
            ->assertSee('DEMO-PURCHASE-001')
            ->assertSee($itemId);
        $this->actingAs($seller)->get(route('admin.payment-orders'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.payment-orders'))
            ->assertOk()->assertSee('Transaction records')->assertSee($order->purchase_id)
            ->assertDontSee('Verify and transfer item')->assertDontSee('Admin GCash account')->assertDontSee('Pixel Coin');
    }

    public function test_simulation_configuration_is_required_and_real_payment_fields_are_rejected(): void
    {
        $seller = $this->user('disabled-seller@example.test');
        $buyer = $this->user('disabled-buyer@example.test');
        $artwork = $this->artwork($seller);
        $itemId = DB::table('artwork_items')->where('artwork_id', $artwork->id)->value('item_id');

        config(['payments.simulation' => false]);
        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))->assertStatus(503);
        config(['payments.simulation' => true]);
        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))->assertOk();
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'item_id' => $itemId,
            'payment_method' => 'simulation',
            'checkout_option' => 'bank_transfer',
        ])->assertSessionHasErrors('simulation_reference');

        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'item_id' => $itemId,
            'payment_method' => 'simulation',
            'checkout_option' => 'card',
            'simulation_reference' => 'DEMO-REFERENCE-001',
            'transaction_reference' => 'NO-EXTERNAL-PAYMENT',
        ])->assertSessionHasErrors('transaction_reference');

        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'item_id' => $itemId,
            'payment_method' => 'simulation',
            'checkout_option' => 'cash',
            'simulation_reference' => 'DEMO-REFERENCE-002',
        ])->assertSessionHasErrors('checkout_option');

        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'item_id' => $itemId,
            'payment_method' => 'pixel_coin',
            'checkout_option' => 'card',
        ])->assertSessionHasErrors('payment_method');
    }

    public function test_homepage_refreshes_market_estimates_and_links_buy_buttons_directly_to_checkout(): void
    {
        $creator = $this->user('homepage-creator@example.test');
        $artwork = $this->artwork($creator);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Market est.')
            ->assertSee('data-artwork-buy', false)
            ->assertSee(route('user.checkout', ['artworkId' => $artwork->id]), false)
            ->assertSee(route('api.artworks.market-prices'), false);

        $expectedEstimate = (new ArtworkPriceDashboard())->simulatedEstimate($artwork->fresh())['value'];
        $this->getJson(route('api.artworks.market-prices'))
            ->assertOk()
            ->assertJsonPath("prices.{$artwork->id}", $expectedEstimate);

        $this->actingAs($creator)->get(route('user'))
            ->assertOk()
            ->assertDontSee('data-artwork-buy', false);
    }

    public function test_each_checkout_option_records_the_same_account_purchase_without_processing_payment(): void
    {
        $seller = $this->user('options-seller@example.test');
        $buyer = $this->user('options-buyer@example.test');
        $artwork = $this->artwork($seller, 4);

        foreach (['card', 'bank_transfer', 'gcash', 'maya'] as $index => $option) {
            $itemId = DB::table('artwork_items')
                ->where('artwork_id', $artwork->id)
                ->where('status', 'available')
                ->orderBy('edition_number')
                ->value('item_id');

            $this->actingAs($buyer)
                ->get(route('user.checkout', $artwork->id))
                ->assertOk();
            $this->post(route('user.purchase-orders.store'), [
                'artwork_id' => $artwork->id,
                'item_id' => $itemId,
                'payment_method' => 'simulation',
                'checkout_option' => $option,
                'simulation_reference' => 'DEMO-OPTION-' . $index,
            ])->assertRedirect(route('user.purchases'));
        }

        $this->assertDatabaseCount('payment_orders', 4);
        $this->assertSame(
            ['simulation', 'simulation', 'simulation', 'simulation'],
            PaymentOrder::query()->orderBy('created_at')->pluck('payment_method')->all(),
        );
        $this->assertSame(
            ['card', 'bank_transfer', 'gcash', 'maya'],
            PaymentOrder::query()->orderBy('created_at')->pluck('payment_provider')->all(),
        );
    }

    public function test_simulated_resale_purchase_transfers_the_owned_item_to_the_next_buyer(): void
    {
        $creator = $this->user('original-creator@example.test');
        $seller = $this->user('reseller@example.test');
        $buyer = $this->user('resale-buyer@example.test');
        $admin = $this->admin();
        $artwork = $this->artwork($creator);
        $itemId = DB::table('artwork_items')->where('artwork_id', $artwork->id)->value('item_id');
        $oldOrder = PaymentOrder::create([
            'purchase_id' => (string) Str::uuid(), 'buyer_id' => $seller->id, 'seller_id' => $creator->id,
            'artwork_id' => $artwork->id, 'item_id' => $itemId, 'type' => 'nft_purchase',
            'amount_minor' => 10000, 'payment_method' => 'simulation', 'status' => 'approved',
        ]);
        DB::table('artwork_items')->where('item_id', $itemId)->update([
            'owner_id' => $seller->id, 'status' => 'owned', 'acquired_purchase_id' => $oldOrder->purchase_id,
        ]);
        DB::table('ownership_transfers')->insert([
            'product_id' => $artwork->product_id, 'item_id' => $itemId,
            'from_user_id' => $creator->id, 'to_user_id' => $seller->id,
            'purchase_id' => $oldOrder->purchase_id, 'amount_minor' => $oldOrder->amount_minor,
            'currency' => 'PHP', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('artwork_ownerships')->insert([
            'artwork_id' => $artwork->id, 'owner_id' => $seller->id,
            'acquired_purchase_id' => $oldOrder->purchase_id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $marketValueEth = (new ArtworkPriceDashboard())->simulatedEstimate($artwork->fresh())['value'];
        $expectedResalePriceMinor = (int) round($marketValueEth * (int) config('payments.php_per_eth') * 100);
        $this->actingAs($seller)->post(route('user.artworks.resale', $artwork), [
            'price_php' => 200, 'quantity' => 1, 'ownership_declaration' => '1', 'evidence' => 'seller-uploaded-proof',
        ])->assertSessionHasErrors(['price_php', 'evidence']);
        $this->assertDatabaseCount('resale_listings', 0);

        $this->actingAs($seller)->post(route('user.artworks.resale', $artwork), [
            'quantity' => 1, 'ownership_declaration' => '1',
        ])->assertRedirect();
        $listingId = DB::table('resale_listings')->value('id');
        $this->assertSame($expectedResalePriceMinor, (int) DB::table('resale_listings')->where('id', $listingId)->value('price_minor'));
        $this->assertNull(DB::table('resale_listings')->where('id', $listingId)->value('evidence_path'));
        $this->actingAs($admin)->post(route('admin.resales.review', $listingId), ['decision' => 'approve'])->assertRedirect();
        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))->assertOk();
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id, 'item_id' => $itemId, 'payment_method' => 'simulation',
            'checkout_option' => 'card', 'simulation_reference' => 'DEMO-RESALE-001',
        ])->assertRedirect(route('user.purchases'));

        $this->assertSame($buyer->id, (int) DB::table('artwork_items')->where('item_id', $itemId)->value('owner_id'));
        $this->assertSame('sold', DB::table('resale_listings')->where('id', $listingId)->value('status'));
        $this->assertSame('approved', PaymentOrder::query()->where('buyer_id', $buyer->id)->value('status'));
        $resaleOrder = PaymentOrder::query()->where('buyer_id', $buyer->id)->firstOrFail();
        $this->assertSame($expectedResalePriceMinor, (int) $resaleOrder->amount_minor);
        $this->actingAs($buyer)->get(route('user.purchase-orders.receipt', $resaleOrder))
            ->assertOk()
            ->assertSee('Resale purchase receipt')
            ->assertSee('Transaction type')
            ->assertSee('Resale purchase');
        $this->actingAs($admin)->get(route('admin.payment-orders'))
            ->assertOk()
            ->assertSee('Ownership purchase receipts:')
            ->assertSee($oldOrder->purchase_id);
    }

    public function test_buyer_can_resell_only_the_selected_quantity_of_purchased_items(): void
    {
        $creator = $this->user('quantity-creator@example.test');
        $seller = $this->user('quantity-seller@example.test');
        $firstBuyer = $this->user('quantity-first-buyer@example.test');
        $secondBuyer = $this->user('quantity-second-buyer@example.test');
        $admin = $this->admin();
        $artwork = $this->artwork($creator, 4);
        $ownedItemIds = DB::table('artwork_items')
            ->where('artwork_id', $artwork->id)
            ->orderBy('edition_number')
            ->limit(3)
            ->pluck('item_id');

        foreach ($ownedItemIds as $itemId) {
            $purchaseId = (string) Str::uuid();
            PaymentOrder::create([
                'purchase_id' => $purchaseId, 'buyer_id' => $seller->id, 'seller_id' => $creator->id,
                'artwork_id' => $artwork->id, 'item_id' => $itemId, 'type' => 'nft_purchase',
                'amount_minor' => 10000, 'payment_method' => 'simulation', 'status' => 'approved',
            ]);
            DB::table('artwork_items')->where('item_id', $itemId)->update([
                'owner_id' => $seller->id, 'status' => 'owned', 'acquired_purchase_id' => $purchaseId,
            ]);
        }

        $this->actingAs($seller)
            ->get('/user')
            ->assertOk()
            ->assertDontSee('Your owned artwork')
            ->assertDontSee('Resale price per item (PHP)');

        $this->post(route('user.artworks.resale', $artwork), [
            'quantity' => 4, 'ownership_declaration' => '1',
        ])->assertSessionHasErrors('quantity');
        $this->assertDatabaseCount('resale_listings', 0);

        $this->post(route('user.artworks.resale', $artwork), [
            'quantity' => 2, 'ownership_declaration' => '1',
        ])->assertRedirect();
        $listingId = DB::table('resale_listings')->value('id');
        $this->assertSame(2, (int) DB::table('resale_listings')->where('id', $listingId)->value('quantity'));

        $this->actingAs($admin)
            ->post(route('admin.resales.review', $listingId), ['decision' => 'approve'])
            ->assertRedirect();

        $itemIds = $ownedItemIds->all();
        foreach ([$firstBuyer, $secondBuyer] as $index => $buyer) {
            $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))
                ->assertOk()
                ->assertSee('Choose the edition item')
                ->assertSee('Edition '.($index + 1));
            $sellerItemId = DB::table('artwork_items')
                ->whereIn('item_id', $itemIds)
                ->where('owner_id', $seller->id)
                ->where('status', 'owned')
                ->orderBy('edition_number')
                ->value('item_id');
            $this->post(route('user.purchase-orders.store'), [
                'artwork_id' => $artwork->id, 'item_id' => $sellerItemId,
                'payment_method' => 'simulation', 'checkout_option' => 'card',
                'simulation_reference' => 'DEMO-QUANTITY-'.$index,
            ])->assertRedirect(route('user.purchases'));
        }

        $this->assertSame(0, (int) DB::table('resale_listings')->where('id', $listingId)->value('quantity'));
        $this->assertSame('sold', DB::table('resale_listings')->where('id', $listingId)->value('status'));
        $this->assertSame(1, (int) DB::table('artwork_items')->where('artwork_id', $artwork->id)->where('owner_id', $seller->id)->count());
    }

    public function test_artwork_detail_shows_resale_action_only_to_a_purchased_item_owner(): void
    {
        $creator = $this->user('detail-creator@example.test');
        $owner = $this->user('detail-owner@example.test');
        $visitor = $this->user('detail-visitor@example.test');
        $artwork = $this->artwork($creator);
        $itemId = DB::table('artwork_items')->where('artwork_id', $artwork->id)->value('item_id');
        $purchaseId = (string) Str::uuid();
        PaymentOrder::create([
            'purchase_id' => $purchaseId, 'buyer_id' => $owner->id, 'seller_id' => $creator->id,
            'artwork_id' => $artwork->id, 'item_id' => $itemId, 'type' => 'nft_purchase',
            'amount_minor' => 10000, 'payment_method' => 'simulation', 'status' => 'approved',
        ]);
        DB::table('artwork_items')->where('item_id', $itemId)->update([
            'owner_id' => $owner->id, 'status' => 'owned', 'acquired_purchase_id' => $purchaseId,
        ]);

        $this->actingAs($owner)->get(route('week12.client.artwork', ['id' => $artwork->id]))
            ->assertOk()
            ->assertSee('Resell owned item')
            ->assertSee('Current estimated resale price')
            ->assertDontSee('name="price_php"', false)
            ->assertDontSee('name="evidence"', false)
            ->assertSee('class="w12-resale-disclosure mt-3"', false)
            ->assertDontSee('class="w12-resale-disclosure mt-3" open', false);
        $this->actingAs($visitor)->get(route('week12.client.artwork', ['id' => $artwork->id]))
            ->assertOk()
            ->assertDontSee('Resell owned item');
    }

    private function user(string $email): User
    {
        return User::create(['name' => 'Test user', 'email' => $email, 'password' => 'secret-password']);
    }

    private function admin(): User
    {
        $admin = $this->user('admin-' . Str::uuid() . '@example.test');
        $admin->forceFill(['role' => 'admin'])->save();

        return $admin;
    }

    private function artwork(User $creator, int $itemCount = 1): Artwork
    {
        return Artwork::create([
            'id' => 'test-artwork-' . Str::lower(Str::random(8)), 'user_id' => $creator->id,
            'title' => 'Test artwork', 'creator' => $creator->name, 'category' => 'Digital Art',
            'price' => 1, 'item_count' => $itemCount, 'image' => 'test-artwork.png',
            'description' => 'A test artwork.', 'status' => 'Listed',
        ]);
    }

}
