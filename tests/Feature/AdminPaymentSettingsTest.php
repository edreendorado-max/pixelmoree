<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPaymentSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_replace_private_gcash_qr_and_future_checkout_uses_the_new_account(): void
    {
        config(['payments.enabled' => true]);
        Storage::fake('local');

        $admin = $this->user('admin@example.test', 'admin');
        $seller = $this->user('seller@example.test');
        $buyer = $this->user('buyer@example.test');
        $artwork = Artwork::create([
            'id' => 'gcash-test-' . Str::lower(Str::random(8)),
            'user_id' => $seller->id,
            'title' => 'GCash test artwork',
            'creator' => $seller->name,
            'category' => 'Digital Art',
            'price' => 100,
            'item_count' => 1,
            'image' => 'gcash-test.png',
            'description' => 'Test artwork for admin GCash settings.',
            'status' => 'Listed',
        ]);

        $oldPath = 'admin-payment-qrs/old-account.png';
        Storage::disk('local')->put($oldPath, 'old QR');
        DB::table('admin_payment_settings')->where('id', 1)->update([
            'gcash_qr_path' => $oldPath,
            'gcash_account_name' => 'Previous account',
            'gcash_number' => '09170000000',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.payment-orders'))
            ->post(route('admin.payment-settings.gcash'), [
                'gcash_qr' => $this->qrImage(),
                'gcash_account_name' => 'Replacement account',
                'gcash_number' => '09990000000',
            ])
            ->assertRedirect(route('admin.payment-orders'));

        $settings = DB::table('admin_payment_settings')->where('id', 1)->first();
        $this->assertSame('Replacement account', $settings->gcash_account_name);
        $this->assertSame('09990000000', $settings->gcash_number);
        $this->assertNotSame($oldPath, $settings->gcash_qr_path);
        Storage::disk('local')->assertExists($settings->gcash_qr_path);
        Storage::disk('local')->assertMissing($oldPath);

        $this->get(route('admin.payment-orders'))
            ->assertOk()
            ->assertSee('Replace GCash QR screenshot')
            ->assertSee('Replacement account')
            ->assertSee('payment-review-qr-preview', false);
        $this->get(route('admin.payment-settings.gcash-qr'))->assertOk();

        $this->actingAs($buyer)
            ->get(route('user.checkout', $artwork->id))
            ->assertOk()
            ->assertSee('Replacement account')
            ->assertSee('09990000000')
            ->assertSee(route('user.checkout.qr', ['artworkId' => $artwork->id, 'method' => 'gcash']));
        $this->get(route('user.checkout.qr', ['artworkId' => $artwork->id, 'method' => 'gcash']))->assertOk();

        $this->actingAs($buyer)
            ->get(route('admin.payment-settings.gcash-qr'))
            ->assertForbidden();
    }

    private function user(string $email, string $role = 'user'): User
    {
        $user = User::create([
            'name' => 'Test user',
            'email' => $email,
            'password' => 'secret-password',
        ]);
        if ($role !== 'user') {
            $user->role = $role;
            $user->save();
        }

        return $user;
    }

    private function qrImage(): UploadedFile
    {
        $contents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/y5sAAAAASUVORK5CYII=', true);
        $this->assertIsString($contents);

        return UploadedFile::fake()->createWithContent('replacement-gcash.png', $contents);
    }
}
