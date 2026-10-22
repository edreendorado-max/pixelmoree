<?php

namespace Tests\Feature;

use Database\Seeders\ArtworkSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PixmoreePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('Pixmoree');
        $this->get('/about')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_week12_marketplace_and_admin_screens_render_seeded_artwork(): void
    {
        $this->seed(ArtworkSeeder::class);

        $this->get('/week12')->assertOk()->assertSee('Neon Wanderer');
        $this->get('/week12/artworks/neon-wanderer')->assertOk()->assertSee('Neon Wanderer');
        $this->get('/week12/admin')->assertOk()->assertSee('Dashboard overview');
        $this->get('/week12/admin/artworks')->assertOk()->assertSee('Artwork management');
    }

    public function test_admin_can_create_update_and_delete_an_artwork(): void
    {
        $this->seed(ArtworkSeeder::class);
        $this->post('/week12/admin/artworks', [
            'title' => 'Test Artwork',
            'creator' => 'test.creator',
            'category' => 'Space',
            'price' => '2.50',
            'image' => 'anime-style-character-space.jpg',
            'description' => 'A test listing for the Laravel feature suite.',
        ])->assertRedirect();

        $artwork = DB::table('artworks')->where('title', 'Test Artwork')->first();
        $this->assertNotNull($artwork);
        $this->assertSame('Review', $artwork->status);

        $this->post("/week12/admin/artworks/{$artwork->id}/status", ['status' => 'Listed'])
            ->assertRedirect('/week12/admin/artworks');
        $this->assertDatabaseHas('artworks', ['id' => $artwork->id, 'status' => 'Listed']);

        $this->post("/week12/admin/artworks/{$artwork->id}/delete")
            ->assertRedirect('/week12/admin/artworks');
        $this->assertDatabaseMissing('artworks', ['id' => $artwork->id]);
    }
}
