<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountAndImageCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_register_and_update_their_profile(): void
    {
        $this->post('/register', [
            'name' => 'Pixel Artist',
            'email' => 'artist@example.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'terms' => '1',
        ])->assertRedirect('/user/profile-settings');

        $user = User::where('email', 'artist@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertSame('user', $user->role);

        $this->get('/user/profile-settings')->assertOk()->assertSee('Pixel Artist');

        $this->post('/user/profile', [
            'name' => 'Updated Artist',
            'email' => 'updated@example.test',
            'phone' => '555-0100',
            'bio' => 'I make pixel art.',
        ])->assertRedirect('/user/profile-settings');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Artist',
            'email' => 'updated@example.test',
            'phone' => '555-0100',
            'bio' => 'I make pixel art.',
        ]);
    }

    public function test_a_registered_user_can_log_in_and_log_out(): void
    {
        User::create([
            'name' => 'Returning Artist',
            'email' => 'returning@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $this->post('/login', [
            'email' => 'returning@example.test',
            'password' => 'secret-password',
        ])->assertRedirect('/user');
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_a_user_can_create_read_update_and_delete_their_images(): void
    {
        Storage::fake('public');
        $user = User::create([
            'name' => 'Image Artist',
            'email' => 'images@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lFwAAAAASUVORK5CYII=');
        $this->assertNotFalse($imageContent);

        $this->actingAs($user)
            ->post('/user/images', [
                'image' => UploadedFile::fake()->createWithContent('portfolio.png', $imageContent),
                'alt_text' => 'Original description',
            ])
            ->assertRedirect('/user/profile-settings');

        $image = Image::where('user_id', $user->id)->firstOrFail();
        Storage::disk('public')->assertExists($image->path);
        $this->get('/user/profile-settings')->assertOk()->assertSee('Original description');

        $this->post("/user/images/{$image->id}/update", [
            'alt_text' => 'Updated description',
        ])->assertRedirect('/user/profile-settings');
        $this->assertDatabaseHas('images', [
            'id' => $image->id,
            'alt_text' => 'Updated description',
        ]);

        $this->post("/user/images/{$image->id}/delete")
            ->assertRedirect('/user/profile-settings');
        $this->assertDatabaseMissing('images', ['id' => $image->id]);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_a_user_cannot_manage_another_users_images(): void
    {
        $owner = User::create([
            'name' => 'Image Owner',
            'email' => 'owner@example.test',
            'password' => Hash::make('secret-password'),
        ]);
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.test',
            'password' => Hash::make('secret-password'),
        ]);
        $image = $owner->images()->create([
            'path' => 'images/owner.png',
            'mime_type' => 'image/png',
            'size_bytes' => 100,
            'alt_text' => null,
        ]);

        $this->actingAs($otherUser)
            ->post("/user/images/{$image->id}/delete")
            ->assertNotFound();

        $this->assertDatabaseHas('images', ['id' => $image->id]);
    }

    public function test_profile_settings_requires_authentication(): void
    {
        $this->get('/user')->assertRedirect('/login');
        $this->get('/user/profile-settings')->assertRedirect('/login');
        $this->get('/admin/content/about')->assertRedirect('/login');
        $this->post('/user/profile')->assertRedirect('/login');
        $this->get('/week12/admin')->assertRedirect('/login');
    }

    public function test_admin_access_is_granted_only_after_cli_promotion(): void
    {
        $user = User::create([
            'name' => 'Beta Admin',
            'email' => 'admin@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $this->actingAs($user)->get('/week12/admin')->assertForbidden();

        $this->artisan('pixmoree:make-admin', ['email' => $user->email])
            ->expectsOutput('Admin access granted to admin@example.test.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'admin']);
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect('/week12/admin');

        $this->actingAs($user->fresh())
            ->get('/week12/admin')
            ->assertOk()
            ->assertSee('Dashboard overview');
    }

    public function test_admin_promotion_does_not_create_unknown_accounts(): void
    {
        $this->artisan('pixmoree:make-admin', ['email' => 'missing@example.test'])
            ->expectsOutput('No registered user was found with that email address.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', ['email' => 'missing@example.test']);
    }
}
