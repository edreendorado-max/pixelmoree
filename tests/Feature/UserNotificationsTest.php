<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_saves_the_user_and_a_welcome_notification(): void
    {
        config(['mail.default' => 'array']);

        $this->post(route('register.store'), [
            'name' => 'New Artist',
            'email' => 'new-artist@example.test',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'terms' => '1',
        ])->assertRedirect(route('verification.notice'))
            ->assertSessionHasErrors('code');

        $user = User::query()->where('email', 'new-artist@example.test')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Artist',
            'email' => 'new-artist@example.test',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'App\\Notifications\\AccountWelcomeNotification',
        ]);

        $this->actingAs($user)
            ->get(route('user.notifications'))
            ->assertOk()
            ->assertSee('Welcome to Pixmoree')
            ->assertSee('Verify your email address to complete setup.');
    }

    public function test_users_can_only_manage_their_own_notifications(): void
    {
        $firstUser = User::create([
            'name' => 'First User',
            'email' => 'first@example.test',
            'password' => 'secret-password',
        ]);
        $secondUser = User::create([
            'name' => 'Second User',
            'email' => 'second@example.test',
            'password' => 'secret-password',
        ]);
        $firstNotification = $firstUser->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'test',
            'data' => ['title' => 'First user notice', 'message' => 'Private to the first user.'],
        ]);
        $secondNotification = $secondUser->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'test',
            'data' => ['title' => 'Second user notice', 'message' => 'Private to the second user.'],
        ]);

        $this->actingAs($firstUser)
            ->get(route('user.notifications'))
            ->assertOk()
            ->assertSee('First user notice')
            ->assertDontSee('Second user notice');

        $this->post(route('user.notifications.read', $firstNotification->id))
            ->assertRedirect(route('user.notifications'));
        $this->assertNotNull($firstNotification->fresh()->read_at);

        $this->post(route('user.notifications.read', $secondNotification->id))
            ->assertNotFound();
        $this->assertNull($secondNotification->fresh()->read_at);

        $this->actingAs($secondUser)
            ->post(route('user.notifications.read-all'))
            ->assertRedirect(route('user.notifications'));
        $this->assertNotNull($secondNotification->fresh()->read_at);
    }
}
