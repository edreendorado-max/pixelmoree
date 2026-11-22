<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\PaymentOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
            ->assertOk()->assertSee('SIMULATED CHECKOUT')->assertSee('no money is charged');
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'item_id' => $itemId,
            'payment_method' => 'simulation',
        ])->assertRedirect(route('user.purchases'))->assertSessionHas('status');

        $order = PaymentOrder::query()->where('buyer_id', $buyer->id)->firstOrFail();
        $this->assertSame('approved', $order->status);
        $this->assertSame('simulation', $order->payment_method);
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
            ->assertOk()->assertSee($order->purchase_id)->assertSee('SIMULATED TRANSACTION');
        $this->actingAs($seller)->get(route('admin.payment-orders'))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.payment-orders'))
            ->assertOk()->assertSee('Simulated transactions')->assertSee($order->purchase_id)
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
            'transaction_reference' => 'NO-EXTERNAL-PAYMENT',
        ])->assertSessionHasErrors('transaction_reference');

        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id,
            'item_id' => $itemId,
            'payment_method' => 'pixel_coin',
        ])->assertSessionHasErrors('payment_method');
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
        DB::table('artwork_ownerships')->insert([
            'artwork_id' => $artwork->id, 'owner_id' => $seller->id,
            'acquired_purchase_id' => $oldOrder->purchase_id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($seller)->post(route('user.artworks.resale', $artwork), [
            'price_php' => 200, 'ownership_declaration' => '1', 'evidence' => $this->proofImage('ownership.png'),
        ])->assertRedirect();
        $listingId = DB::table('resale_listings')->value('id');
        $this->actingAs($admin)->post(route('admin.resales.review', $listingId), ['decision' => 'approve'])->assertRedirect();
        $this->actingAs($buyer)->get(route('user.checkout', $artwork->id))->assertOk();
        $this->post(route('user.purchase-orders.store'), [
            'artwork_id' => $artwork->id, 'item_id' => $itemId, 'payment_method' => 'simulation',
        ])->assertRedirect(route('user.purchases'));

        $this->assertSame($buyer->id, (int) DB::table('artwork_items')->where('item_id', $itemId)->value('owner_id'));
        $this->assertSame('sold', DB::table('resale_listings')->where('id', $listingId)->value('status'));
        $this->assertSame('approved', PaymentOrder::query()->where('buyer_id', $buyer->id)->value('status'));
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

    private function artwork(User $creator): Artwork
    {
        return Artwork::create([
            'id' => 'test-artwork-' . Str::lower(Str::random(8)), 'user_id' => $creator->id,
            'title' => 'Test artwork', 'creator' => $creator->name, 'category' => 'Digital Art',
            'price' => 1, 'item_count' => 1, 'image' => 'test-artwork.png',
            'description' => 'A test artwork.', 'status' => 'Listed',
        ]);
    }

    private function proofImage(string $filename): UploadedFile
    {
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/y5sAAAAASUVORK5CYII=', true);
        $this->assertIsString($contents);

        return UploadedFile::fake()->createWithContent($filename, $contents);
    }
}
