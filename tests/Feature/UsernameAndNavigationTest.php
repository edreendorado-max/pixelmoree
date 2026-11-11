<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsernameAndNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_pages_use_the_pixmoree_design_and_are_only_available_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('class="auth-page"', false)
            ->assertSee('class="auth-card"', false)
            ->assertSee('action="'.route('login.store').'"', false)
            ->assertSee('css/site.css', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('class="auth-page"', false)
            ->assertSee('class="auth-card auth-card-register"', false)
            ->assertSee('action="'.route('register.store').'"', false)
            ->assertSee('css/site.css', false);

        $user = User::create([
            'name' => 'Signed In User',
            'email' => 'signed-in@example.test',
            'password' => 'secret-password',
        ]);

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('home'));

        $this->get(route('register'))
            ->assertRedirect(route('home'));

        $this->post(route('login.store'), [
            'email' => 'another@example.test',
            'password' => 'secret-password',
        ])->assertRedirect(route('home'));

        $this->post(route('register.store'), [
            'name' => 'Another User',
            'email' => 'another@example.test',
            'password' => 'ExamplePass1!',
            'password_confirmation' => 'ExamplePass1!',
            'terms' => '1',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseMissing('users', ['email' => 'another@example.test']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_user_can_sign_in_with_their_username(): void
    {
        $user = User::create([
            'name' => 'Admin Artist',
            'username' => 'InsanAdmin1',
            'email' => 'admin@example.test',
            'password' => 'ExamplePass1!',
        ]);
        $user->forceFill(['role' => 'admin'])->save();
        $user->markEmailAsVerified();

        $this->post(route('login.store'), [
            'email' => 'insanadmin1',
            'password' => 'ExamplePass1!',
        ])->assertRedirect(route('week12.admin'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_signed_in_user_is_redirected_from_the_guest_homepage_to_their_account(): void
    {
        $user = User::create([
            'name' => 'Signed In User',
            'email' => 'member@example.test',
            'password' => 'secret-password',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('user'));

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('href="'.route('user').'"', false)
            ->assertDontSee('href="/login"', false);

        $this->get(route('user'))
            ->assertOk()
            ->assertSee('class="navbar-brand" href="'.route('user').'"', false);

        $this->get(route('nft.guest', ['id' => 'sample-artwork']))
            ->assertRedirect(route('user.nft.show', ['id' => 'sample-artwork']));

        $this->get(route('user.nft.show', ['id' => 'sample-artwork']))
            ->assertRedirect(route('week12.client.artwork', ['id' => 'sample-artwork']));
    }

    public function test_registration_rejects_usernames_that_only_differ_by_case(): void
    {
        User::create([
            'name' => 'Existing User',
            'username' => 'PixelArtist',
            'email' => 'existing@example.test',
            'password' => 'ExamplePass1!',
        ]);

        $this->post(route('register.store'), [
            'name' => 'Another User',
            'username' => 'pixelartist',
            'email' => 'another@example.test',
            'password' => 'ExamplePass1!',
            'password_confirmation' => 'ExamplePass1!',
            'terms' => '1',
        ])->assertSessionHasErrors('username');
    }
}
