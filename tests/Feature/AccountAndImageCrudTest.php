<?php

namespace Tests\Feature;

use App\Models\Image;
use App\Models\User;
use App\Mail\NewAccountWelcomeMail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountAndImageCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.example.test',
            'mail.from.address' => 'no-reply@example.test',
        ]);
        Mail::fake();
    }

    public function test_a_visitor_can_register_and_update_their_profile(): void
    {
        $this->post('/register', [
            'name' => 'Pixel Artist',
            'email' => 'artist@example.test',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'terms' => '1',
        ])->assertRedirect('/user/profile-settings');

        $this->assertDatabaseHas('users', [
            'email' => 'artist@example.test',
            'name' => 'Pixel Artist',
        ]);
        Mail::assertSent(NewAccountWelcomeMail::class, function (NewAccountWelcomeMail $mail): bool {
            return $mail->hasTo('artist@example.test')
                && $mail->user->name === 'Pixel Artist';
        });
        $user = User::where('email', 'artist@example.test')->firstOrFail();
        $this->assertSame('pixelartist', $user->username);
        $this->assertAuthenticatedAs($user);
        $this->get('/user/profile-settings')
            ->assertOk()
            ->assertSee('data-notification-popup', false)
            ->assertSee('Welcome to Pixmoree, Pixel Artist!');

        $user->refresh();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('StrongPass1!', $user->password));
        $this->assertSame('user', $user->role);

        $this->get('/user/profile-settings')->assertOk()->assertSee('Pixel Artist')->assertSee('Back to account');

        $this->post('/user/profile', [
            'name' => 'Updated Artist',
            'email' => 'artist@example.test',
            'phone' => '555-0100',
            'bio' => 'I make pixel art.',
        ])->assertRedirect('/user/profile-settings');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Artist',
            'email' => 'artist@example.test',
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

        $this->post('/logout')
            ->assertRedirect('/login')
            ->assertSessionHas('notification_popup.title', 'You are logged out');
        $this->assertGuest();
        $this->get('/login')
            ->assertOk()
            ->assertSee('data-notification-popup', false)
            ->assertSee('You are logged out');
    }

    public function test_registration_does_not_require_email_delivery_or_a_verification_code(): void
    {
        config(['mail.default' => 'array']);
        $this->post('/register', [
            'name' => 'New Artist',
            'email' => 'new-artist@example.test',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'terms' => '1',
        ])->assertRedirect('/user/profile-settings');

        $user = User::where('email', 'new-artist@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasTable('email_verification_codes'));
    }

    public function test_a_user_can_request_and_complete_a_password_reset(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'Password Reset User',
            'email' => 'reset@example.test',
            'password' => 'old-password',
        ]);

        $this->get('/forgot-password')->assertOk()->assertSee('Reset password');
        $this->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status', 'If an account exists for that email, a reset link has been sent.');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use (&$token): bool {
            $token = $notification->token;

            return true;
        });
        $this->assertIsString($token);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('Choose a new password');
        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecret1!',
            'password_confirmation' => 'NewSecret1!',
        ])->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Your password has been reset. You can now sign in.');

        $this->assertTrue(Hash::check('NewSecret1!', $user->fresh()->password));
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'NewSecret1!',
        ])->assertRedirect('/user');
    }

    public function test_local_password_reset_shows_a_simulated_link_without_sending_email(): void
    {
        Notification::fake();
        config(['app.env' => 'local']);
        $user = User::create([
            'name' => 'Local Password Reset User',
            'email' => 'local-reset@example.test',
            'password' => 'OldSecret1!',
        ]);

        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Local demo mode: no email is sent.');

        $response = $this->from('/forgot-password')->post('/forgot-password', ['email' => $user->email]);
        $response->assertRedirect('/forgot-password')
            ->assertSessionHas('status', 'If an account exists for that email, a reset link is ready.');

        $resetUrl = $response->getSession()->get('simulated_reset_url');
        $this->assertIsString($resetUrl);
        $this->assertStringContainsString('/reset-password/', $resetUrl);
        $this->get($resetUrl)->assertOk()->assertSee('no email is sent');

        $token = basename((string) parse_url($resetUrl, PHP_URL_PATH));
        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecret1!',
            'password_confirmation' => 'NewSecret1!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewSecret1!', $user->fresh()->password));
        Notification::assertNotSentTo($user, ResetPassword::class);
    }

    public function test_registration_rejects_passwords_missing_required_character_types(): void
    {
        foreach (['short', 'lowercase1!', 'Uppercase!', 'NoSpecial1'] as $password) {
            $this->post('/register', [
                'name' => 'Password Test',
                'email' => 'password-test@example.test',
                'password' => $password,
                'password_confirmation' => $password,
                'terms' => '1',
            ])->assertSessionHasErrors('password');
        }

        $this->assertDatabaseMissing('users', ['email' => 'password-test@example.test']);
    }

    public function test_email_verification_routes_have_been_removed(): void
    {
        $this->get('/email/verify')->assertNotFound();
        $this->post('/email/verify', ['code' => '123456'])->assertNotFound();
        $this->post('/email/verification/resend')->assertNotFound();
    }

    public function test_changing_email_does_not_sign_the_user_out_or_require_verification(): void
    {
        $user = User::create([
            'name' => 'Email Change',
            'email' => 'old-address@example.test',
            'password' => Hash::make('SecretPass1!'),
        ]);
        $this->actingAs($user)->post('/user/profile', [
            'name' => $user->name,
            'email' => 'new-address@example.test',
        ])->assertRedirect('/user/profile-settings');

        $user->refresh();
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame('new-address@example.test', $user->email);
    }

    public function test_password_reset_request_does_not_disclose_unknown_emails(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'missing@example.test'])
            ->assertRedirect()
            ->assertSessionHas('status', 'If an account exists for that email, a reset link has been sent.');

        Notification::assertNothingSent();
    }

    public function test_google_sign_in_routes_are_removed(): void
    {
        $this->get('/auth/google/redirect')->assertNotFound();
        $this->get('/auth/google/callback')->assertNotFound();
        $this->get('/login')->assertOk()->assertDontSee('Continue with Google');
        $this->assertDatabaseMissing('users', ['email' => 'google@example.test']);
    }

    public function test_facebook_sign_in_routes_are_removed(): void
    {
        $this->get('/auth/facebook/redirect')->assertNotFound();
        $this->get('/auth/facebook/callback')->assertNotFound();
        $this->get('/login')->assertOk()->assertDontSee('Continue with Facebook');
        $this->assertDatabaseMissing('users', ['email' => 'facebook@example.test']);
    }

    public function test_removed_social_routes_do_not_authenticate_existing_accounts(): void
    {
        $user = User::create([
            'name' => 'Existing Account',
            'email' => 'existing@example.test',
            'password' => 'secret-password',
        ]);
        $this->get('/auth/google/callback')->assertNotFound();
        $this->get('/auth/facebook/callback')->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'existing@example.test']);
        $this->assertGuest();
    }

    public function test_email_password_login_remains_the_only_login_path(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertDontSee('Continue with Google')
            ->assertDontSee('Continue with Facebook');
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
            ->assertSee('at least 6 characters');

        $this->put('/user/password', [
            'current_password' => 'wrong-password',
            'new_password' => 'EasyPass8!',
            'new_password_confirmation' => 'EasyPass8!',
        ])->assertSessionHasErrors('current_password');

        $this->put('/user/password', [
            'current_password' => 'secret-password',
            'new_password' => 'short',
            'new_password_confirmation' => 'short',
        ])->assertSessionHasErrors('new_password');

        $this->assertTrue(Hash::check('secret-password', $user->fresh()->password));

        $this->put('/user/password', [
            'current_password' => 'secret-password',
            'new_password' => 'EasyPass8!',
            'new_password_confirmation' => 'EasyPass8!',
        ])->assertRedirect('/user/profile-settings')->assertSessionHas('status', 'Your password has been changed.');

        $this->assertTrue(Hash::check('EasyPass8!', $user->fresh()->password));

        $this->post('/logout')->assertRedirect('/login');
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'EasyPass8!',
        ])->assertRedirect('/user');
        $this->assertAuthenticatedAs($user->fresh());
        Mail::assertNothingOutgoing();
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
        $this->get('/user/artworks')->assertRedirect('/login');
        $this->get('/user/artworks/private-artwork/edit')->assertRedirect('/login');
        $this->post('/user/artworks')->assertRedirect('/login');
        $this->patch('/user/artworks/private-artwork', [])->assertRedirect('/login');
        $this->delete('/user/artworks/private-artwork')->assertRedirect('/login');
        $this->get('/week12/admin')->assertRedirect('/login');
        $this->get('/week12/admin/artworks')->assertRedirect('/login');
    }

    public function test_admin_access_is_granted_only_after_cli_promotion(): void
    {
        $user =         User::create([
            'name' => 'Beta Admin',
            'email' => 'admin@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $this->actingAs($user)->get('/week12/admin')->assertForbidden();

        $this->artisan('pixmoree:make-admin', ['email' => $user->email])
            ->expectsOutput('Admin access granted to admin@example.test.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'role' => 'admin']);
        $this->post('/logout')->assertRedirect('/login');
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
