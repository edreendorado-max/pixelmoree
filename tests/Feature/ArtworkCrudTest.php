<?php

namespace Tests\Feature;

use App\Models\Artwork;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArtworkCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_and_week14_artwork_crud_routes_require_authentication(): void
    {
        $this->get(route('user.dashboard'))->assertRedirect(route('login'));
        $this->get(route('week14.artworks.index'))->assertRedirect(route('login'));
        $this->get(route('week14.artworks.create'))->assertRedirect(route('login'));
        $this->post(route('week14.artworks.store'))->assertRedirect(route('login'));
        $this->get(route('week14.artworks.edit', ['id' => 'private-artwork']))->assertRedirect(route('login'));
        $this->patch(route('week14.artworks.update', ['id' => 'private-artwork']))->assertRedirect(route('login'));
        $this->delete(route('week14.artworks.destroy', ['id' => 'private-artwork']))->assertRedirect(route('login'));
    }

    public function test_a_user_can_create_list_update_and_delete_their_artwork(): void
    {
        Storage::fake('public');
        $user = User::create([
            'name' => 'Artwork Owner',
            'email' => 'owner@example.test',
            'password' => 'secret-password',
        ]);
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lFwAAAAASUVORK5CYII=');

        $this->actingAs($user)
            ->post(route('user.artworks.store'), [
                'title' => 'First Artwork',
                'description' => 'An original pixel artwork.',
                'category' => 'Digital Art',
                'price' => '12.50',
                'item_count' => 1,
                'image' => UploadedFile::fake()->createWithContent('artwork.png', $image),
                'originality' => '1',
            ])
            ->assertRedirect(route('user.create'));

        $artwork = $user->artworks()->firstOrFail();
        $this->assertDatabaseHas('artworks', [
            'id' => $artwork->id,
            'title' => 'First Artwork',
            'user_id' => $user->id,
        ]);

        $this->get(route('user.nft-management'))
            ->assertOk()
            ->assertSee('First Artwork')
            ->assertSee(route('user.artworks.edit', ['id' => $artwork->id]));
        $this->get(route('user.artworks.edit', ['id' => $artwork->id]))
            ->assertOk()
            ->assertSee('Are you sure you want to permanently delete this artwork listing?', false);

        $this->from(route('user.artworks.edit', ['id' => $artwork->id]))
            ->patch(route('user.artworks.update', ['id' => $artwork->id]), [
                'title' => '',
                'description' => 'Changed description.',
                'category' => 'Illustration',
                'price' => '15.00',
                'item_count' => 2,
            ])
            ->assertRedirect(route('user.artworks.edit', ['id' => $artwork->id]))
            ->assertSessionHasErrors('title');
        $this->assertDatabaseHas('artworks', ['id' => $artwork->id, 'title' => 'First Artwork']);

        $this->patch(route('user.artworks.update', ['id' => $artwork->id]), [
            'title' => 'Updated Artwork',
            'description' => 'Updated description.',
            'category' => 'Illustration',
            'price' => '15.00',
            'item_count' => 2,
        ])->assertRedirect(route('user.nft-management'));
        $this->assertDatabaseHas('artworks', [
            'id' => $artwork->id,
            'title' => 'Updated Artwork',
            'description' => 'Updated description.',
            'price' => '15.00',
            'item_count' => 2,
        ]);

        $this->delete(route('user.artworks.destroy', ['id' => $artwork->id]))
            ->assertRedirect(route('user.nft-management'));
        $this->assertDatabaseMissing('artworks', ['id' => $artwork->id]);
    }

    public function test_a_user_cannot_manage_another_users_artwork(): void
    {
        $owner = User::create([
            'name' => 'Artwork Owner',
            'email' => 'owner@example.test',
            'password' => 'secret-password',
        ]);
        $otherUser = User::create([
            'name' => 'Another User',
            'email' => 'other@example.test',
            'password' => 'secret-password',
        ]);
        $artwork = Artwork::create([
            'id' => 'private-artwork',
            'user_id' => $owner->id,
            'title' => 'Private Artwork',
            'creator' => $owner->name,
            'category' => 'Digital Art',
            'price' => '10.00',
            'item_count' => 1,
            'image' => 'artworks/private.png',
            'description' => 'Private listing.',
            'status' => 'Listed',
        ]);

        $this->actingAs($otherUser)
            ->get(route('user.artworks.edit', ['id' => $artwork->id]))
            ->assertNotFound();
        $this->delete(route('user.artworks.destroy', ['id' => $artwork->id]))
            ->assertNotFound();
        $this->assertDatabaseHas('artworks', ['id' => $artwork->id, 'user_id' => $owner->id]);
    }
}
