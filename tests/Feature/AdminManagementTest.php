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
            'status' => 'Review',
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
    }

    public function test_admin_can_manage_roles_and_update_creator_asking_prices(): void
    {
        $admin = $this->createUser('Role Admin', 'role-admin@example.test', 'admin');
        $otherAdmin = $this->createUser('Second Admin', 'second-admin@example.test', 'admin');
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
            ->post(route('admin.users.role', $creator), ['role' => 'admin'])
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertSame('admin', $creator->fresh()->role);

        $this->post(route('admin.users.role', $otherAdmin), ['role' => 'user'])
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertSame('user', $otherAdmin->fresh()->role);

        $this->post(route('admin.artworks.price', $artwork->id), ['price' => '24.95'])
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertEquals(24.95, $artwork->fresh()->price);
    }

    public function test_admin_role_cannot_be_changed_by_a_user_or_changed_by_its_owner(): void
    {
        $admin = $this->createUser('Protected Admin', 'protected-admin@example.test', 'admin');
        $user = $this->createUser('Regular User', 'regular-user@example.test');

        $this->actingAs($user)
            ->get(route('admin.users'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.users.role', $admin), ['role' => 'user'])
            ->assertStatus(409);
        $this->assertSame('admin', $admin->fresh()->role);
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
