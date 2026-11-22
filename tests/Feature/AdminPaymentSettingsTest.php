<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPaymentSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_transaction_screen_is_simulation_only_and_has_no_real_payment_setup(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin-' . Str::uuid() . '@example.test',
            'password' => 'secret-password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($admin)->get(route('admin.payment-orders'))
            ->assertOk()
            ->assertSee('Transaction records')
            ->assertSee('No external payment or payout is processed')
            ->assertDontSee('GCash account name')
            ->assertDontSee('Verify and transfer item');

        $this->actingAs($admin)->post('/admin/payment-settings/gcash')->assertNotFound();
        $this->actingAs($admin)->post('/admin/payment-orders/00000000-0000-0000-0000-000000000001/review')->assertNotFound();
    }
}
