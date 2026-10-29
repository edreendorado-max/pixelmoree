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

    public function test_a_user_can_upload_and_replace_their_profile_banner(): void
    {
        Storage::fake('public');
        $user = User::create([
            'name' => 'Banner Artist',
            'email' => 'banner@example.test',
            'password' => Hash::make('secret-password'),
        ]);
        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lFwAAAAASUVORK5CYII=');
        $this->assertNotFalse($imageContent);

        $this->actingAs($user)
            ->post('/user/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'banner' => UploadedFile::fake()->createWithContent('first-banner.png', $imageContent),
            ])
            ->assertRedirect('/user/profile-settings');

        $firstBannerPath = substr($user->fresh()->banner_path, strlen('storage/'));
        Storage::disk('public')->assertExists($firstBannerPath);
        $this->get('/user/profile-settings')
            ->assertOk()
            ->assertSee('profile-settings-banner-preview')
            ->assertDontSee('Your image library', false)
            ->assertDontSee('Upload an image', false);
        $this->get('/user')
            ->assertOk()
            ->assertSee($user->fresh()->banner_path);

        $this->actingAs($user)
            ->post('/user/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'banner' => UploadedFile::fake()->createWithContent('second-banner.png', $imageContent),
            ])
            ->assertRedirect('/user/profile-settings');

        $secondBannerPath = substr($user->fresh()->banner_path, strlen('storage/'));
        $this->assertNotSame($firstBannerPath, $secondBannerPath);
        Storage::disk('public')->assertMissing($firstBannerPath);
        Storage::disk('public')->assertExists($secondBannerPath);
    }

    public function test_a_user_can_change_password_after_verifying_the_current_password(): void
    {
        $user = User::create([
            'name' => 'Password Artist',
            'email' => 'password@example.test',
            'password' => 'secret-password',
        ]);

        $this->actingAs($user)
            ->get('/user/profile-settings')
            ->assertOk()
            ->assertSee('Change password')
            ->assertSee('at least 8 characters');

        $this->put('/user/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'easyPass8',
            'new_password_confirmation' => 'easyPass8',
        ])->assertSessionHasErrors('current_password');

        $this->put('/user/password', [
            'current_password' => 'secret-password',
            'new_password' => 'short',
            'new_password_confirmation' => 'short',
        ])->assertSessionHasErrors('new_password');

        $this->assertTrue(Hash::check('secret-password', $user->fresh()->password));

        $this->put('/user/password', [
            'current_password' => 'secret-password',
            'new_password' => 'easyPass8',
            'new_password_confirmation' => 'easyPass8',
        ])->assertRedirect('/user/profile-settings')->assertSessionHas('status', 'Your password has been changed.');

        $this->assertTrue(Hash::check('easyPass8', $user->fresh()->password));

        $this->post('/logout')->assertRedirect('/login');
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'easyPass8',
        ])->assertRedirect('/user');
        $this->assertAuthenticatedAs($user->fresh());
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
        $this->get('/user/profile-settings')
            ->assertOk()
            ->assertDontSee('Your image library', false)
            ->assertDontSee('Upload an image', false);

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
