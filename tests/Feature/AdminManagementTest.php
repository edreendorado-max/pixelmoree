<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_and_user_monitoring_show_core_operational_counts(): void
    {
        $admin = $this->createUser('Dashboard Admin', 'dashboard-admin@example.test', 'admin');
        $creator = $this->createUser('Dashboard Creator', 'dashboard-creator@example.test');
        Artwork::create([
            'id' => 'dashboard-artwork',
            'user_id' => $creator->id,
            'title' => 'Dashboard Artwork',
            'creator' => $creator->name,
            'category' => 'Digital Art',
            'price' => 12.50,
            'image' => 'nfts/ethereal-life.png',
            'description' => 'Artwork shown in the admin dashboard.',
            'status' => 'Listed',
        ]);

        $this->actingAs($admin)
            ->get(route('week12.admin'))
            ->assertOk()
            ->assertSee('Accounts')
            ->assertSee('Artwork listings')
            ->assertSee('Payments to review')
            ->assertSee('Resales to review')
            ->assertSee('Dashboard Artwork')
            ->assertSee(route('admin.users'), false);

        $this->get(route('admin.users'))
            ->assertOk()
            ->assertSee('Dashboard Creator')
            ->assertSee('Unverified')
            ->assertSee('1');

        $this->get(route('admin.analytics'))
            ->assertOk()
            ->assertSee('Estimated value history')
            ->assertSee(route('admin.api.analytics'), false);
        $this->get(route('admin.content.edit', 'marketplace'))
            ->assertOk()
            ->assertSee('Store previews are not available in the administrator workspace.')
            ->assertDontSee(route('store'), false);

        \Illuminate\Support\Facades\DB::table('artwork_price_snapshots')->insert([
            'artwork_id' => 'dashboard-artwork',
            'snapshot_date' => today()->toDateString(),
            'price' => 14.25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->getJson(route('admin.api.analytics'))
            ->assertOk()
            ->assertJsonPath('price_metric', 'estimated_market_value')
            ->assertJsonPath('artworks.0.id', 'dashboard-artwork')
            ->assertJsonPath('artworks.0.asking_price', 12.5)
            ->assertJsonPath('artworks.0.price_history.0.value', 14.25)
            ->assertJsonPath('creators.0.name', 'Dashboard Creator');
    }

    public function test_admin_can_monitor_accounts_and_update_creator_asking_prices(): void
    {
        $admin = $this->createUser('Role Admin', 'role-admin@example.test', 'admin');
        $creator = $this->createUser('Price Creator', 'price-creator@example.test');
        $artwork = Artwork::create([
            'id' => 'admin-priced-artwork',
            'user_id' => $creator->id,
            'title' => 'Price Managed Artwork',
            'creator' => $creator->name,
            'category' => 'Digital Art',
            'price' => 1.00,
            'image' => 'nfts/ethereal-life.png',
            'description' => 'Artwork with a price managed by an administrator.',
            'status' => 'Listed',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee($creator->email)
            ->assertDontSee('Make admin')
            ->assertDontSee('Remove admin');

        $this->post(route('admin.artworks.price', $artwork->id), ['price' => '24.95'])
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertEquals(24.95, $artwork->fresh()->price);
    }

    public function test_admin_can_edit_user_profile_and_email_change_resets_verification(): void
    {
        $admin = $this->createUser('Account Admin', 'account-admin@example.test', 'admin');
        $account = $this->createUser('Original Name', 'original@example.test');
        $account->markEmailAsVerified();

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $account))
            ->assertOk()
            ->assertSee('Edit account')
            ->assertSee('original@example.test');

        $this->put(route('admin.users.update', $account), [
            'name' => 'Updated Name',
            'username' => 'updated.name',
            'email' => 'updated@example.test',
            'phone' => '555-0199',
            'bio' => 'Updated by an administrator.',
        ])->assertRedirect(route('admin.users.edit', $account))
            ->assertSessionHas('status');

        $account->refresh();
        $this->assertSame('Updated Name', $account->name);
        $this->assertSame('updated.name', $account->username);
        $this->assertSame('updated@example.test', $account->email);
        $this->assertNull($account->email_verified_at);
        $this->assertSame('555-0199', $account->phone);
        $this->assertSame('Updated by an administrator.', $account->bio);
    }

    public function test_admin_pages_are_not_available_to_regular_users(): void
    {
        $user = $this->createUser('Regular User', 'regular-user@example.test');

        $this->actingAs($user);
        foreach ([
            route('week12.admin'),
            route('week12.admin.artworks'),
            route('admin.users'),
            route('admin.payment-orders'),
            route('admin.content.edit', 'home'),
        ] as $adminPage) {
            $this->get($adminPage)->assertForbidden();
        }
        $this->get(route('store'))->assertOk();
        $this->get(route('user.profile-settings'))->assertOk();

        $this->post(route('admin.artworks.price', ['id' => 'missing-artwork']), ['price' => '1.00'])
            ->assertForbidden();
        $this->post('/week12/admin/artworks/missing-artwork/status', ['status' => 'Listed'])
            ->assertForbidden();
        $this->post(route('admin.content.update', 'home'), ['content' => []])
            ->assertForbidden();
        $this->get(route('admin.api.analytics'))->assertForbidden();
    }

    public function test_regular_user_navigation_does_not_show_admin_tools(): void
    {
        $user = $this->createUser('Navigation User', 'navigation-user@example.test');

        $this->actingAs($user)
            ->get(route('user'))
            ->assertOk()
            ->assertDontSee('data-member-action="admin"', false)
            ->assertDontSee(route('week12.admin'), false);

        $this->actingAs($this->createUser('Navigation Admin', 'navigation-admin@example.test', 'admin'))
            ->get(route('user'))
            ->assertRedirect(route('week12.admin'));

        $this->get(route('store'))->assertRedirect(route('week12.admin'));
        $this->get(route('user.profile-settings'))->assertRedirect(route('week12.admin'));
    }

    public function test_admin_artwork_creation_route_is_not_available(): void
    {
        $admin = $this->createUser('No Upload Admin', 'no-upload-admin@example.test', 'admin');

        $this->actingAs($admin)
            ->post('/week12/admin/artworks')
            ->assertMethodNotAllowed();
    }

    private function createUser(string $name, string $email, string $role = 'user'): User
    {
        $user = User::create([
            'name' => $name,
            'username' => str_replace(' ', '', $name),
            'email' => $email,
            'password' => 'Password1!',
        ]);
        $user->forceFill(['role' => $role])->save();

        return $user;
    }
}
