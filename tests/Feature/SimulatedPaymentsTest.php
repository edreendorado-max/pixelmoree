<?php

namespace Tests\Feature;

use App\Models\PaymentOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SimulatedPaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['payments.enabled' => true, 'payments.simulation' => true]);
    }

    public function test_simulated_withdrawal_debits_php_earnings_and_creates_a_receipt(): void
    {
        $creator = $this->user('withdraw-creator@example.test');
        $creator->forceFill(['cash_balance_minor' => 30000])->save();

        $this->actingAs($creator)->from(route('user.earnings'))
            ->post(route('user.earnings.withdrawals'), ['amount_php' => '200.00'])
            ->assertRedirect(route('user.earnings'))
            ->assertSessionHas('status');

        $order = PaymentOrder::query()->where('type', 'creator_payout')->firstOrFail();
        $this->assertSame('paid', $order->status);
        $this->assertSame('simulation', $order->payment_method);
        $this->assertSame(20000, (int) $order->amount_minor);
        $this->assertSame(10000, (int) $creator->fresh()->cash_balance_minor);
        $this->assertDatabaseHas('cash_ledger_entries', [
            'user_id' => $creator->id,
            'purchase_id' => $order->purchase_id,
            'type' => 'withdrawal',
            'direction' => 'debit',
            'amount_minor' => 20000,
            'balance_after_minor' => 10000,
        ]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $creator->id]);
        $this->get(route('user.purchase-orders.receipt', $order))
            ->assertOk()->assertSee('Withdrawal receipt')->assertSee('No payment or payout was processed');
    }

    public function test_withdrawal_cannot_exceed_simulated_earnings_or_use_an_invalid_amount(): void
    {
        $creator = $this->user('limited-creator@example.test');
        $creator->forceFill(['cash_balance_minor' => 10000])->save();

        $this->actingAs($creator)->from(route('user.earnings'))
            ->post(route('user.earnings.withdrawals'), ['amount_php' => '0.00'])
            ->assertSessionHasErrors('amount_php');
        $this->post(route('user.earnings.withdrawals'), ['amount_php' => '100.01'])
            ->assertSessionHasErrors('amount_php');

        $this->assertSame(10000, (int) $creator->fresh()->cash_balance_minor);
        $this->assertDatabaseCount('payment_orders', 0);
        $this->assertDatabaseCount('cash_ledger_entries', 0);
    }

    public function test_coin_wallet_and_topup_urls_are_removed(): void
    {
        $user = $this->user('former-wallet-user@example.test');
        $this->actingAs($user)->get('/user/pixel-coins')->assertNotFound();
        $this->post('/user/pixel-coins/top-ups', ['amount_php' => '50.00'])->assertNotFound();
        $this->post('/user/pixel-coins/withdrawals', ['coin_amount' => '100.00'])->assertNotFound();
    }

    public function test_withdrawals_are_unavailable_when_simulation_mode_is_off(): void
    {
        $creator = $this->user('disabled-withdrawal@example.test');
        $creator->forceFill(['cash_balance_minor' => 30000])->save();
        config(['payments.simulation' => false]);

        $this->actingAs($creator)->post(route('user.earnings.withdrawals'), ['amount_php' => '20.00'])
            ->assertStatus(503);
        $this->assertSame(30000, (int) $creator->fresh()->cash_balance_minor);
        $this->assertDatabaseCount('payment_orders', 0);
    }

    private function user(string $email): User
    {
        return User::create([
            'name' => 'Test user',
            'username' => 'user-' . Str::lower(Str::random(8)),
            'email' => $email,
            'password' => 'secret-password',
        ]);
    }
}
