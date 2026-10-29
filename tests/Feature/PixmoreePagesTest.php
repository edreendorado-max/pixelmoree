<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\ArtworkSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('artworks', 7);
        $this->assertSame(4, DB::table('artworks')->distinct()->count('user_id'));
        $this->assertSame(7, DB::table('artworks')->distinct()->count('image_hash'));
        foreach (DB::table('artworks')->pluck('image') as $image) {
            $this->assertFileExists(public_path('assets/' . $image));
        }
        $admin = User::create([
            'name' => 'Beta Admin',
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->get('/week12')->assertOk()->assertSee('Ethereal Life');
        $this->get('/week12/artworks/neon-wanderer')->assertOk()->assertSee('Ethereal Life');
        $home = $this->get('/');
        $home->assertOk()->assertSee('Trending Creators');
        $this->assertSame(7, substr_count($home->getContent(), 'class="col-6 col-sm-3 art-column"'));
        $this->assertSame(4, substr_count($home->getContent(), 'class="creator-mini-card"'));
        $this->get('/about')
            ->assertOk()
            ->assertSee('The people behind Pixmoree.')
            ->assertSee('assets/creators/noah.jpg')
            ->assertSee('assets/creators/kenneth.png');
        $this->actingAs($admin);
        $this->get('/week12/admin')->assertOk()->assertSee('Dashboard overview');
        $this->get('/week12/admin/artworks')->assertOk()->assertSee('Artwork management');
    }

    public function test_admin_can_edit_public_page_copy_and_about_artwork(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.test',
            'password' => 'secret-password',
        ]);
        $admin = User::create([
            'name' => 'Content Admin',
            'email' => 'content-admin@example.test',
            'password' => 'secret-password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();

        $this->actingAs($user)
            ->get('/admin/content/about')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/content/about')
            ->assertOk()
            ->assertSee('Edit website content')
            ->assertSee('Mission artwork');

        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lFwAAAAASUVORK5CYII=');
        $this->assertNotFalse($imageContent);

        $this->post('/admin/content/about', [
            'content' => [
                'hero' => ['heading' => 'A refreshed Pixmoree story'],
                'stories' => [1 => ['heading' => 'A refreshed mission']],
            ],
            'images' => [
                'stories' => [1 => ['image' => UploadedFile::fake()->createWithContent('mission.png', $imageContent)]],
            ],
        ])->assertRedirect('/admin/content/about');

        $aboutPage = DB::table('site_pages')->where('slug', 'about')->first();
        $this->assertNotNull($aboutPage);
        $aboutContent = json_decode($aboutPage->content, true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('A refreshed Pixmoree story', $aboutContent['hero']['heading']);
        $this->assertSame('A refreshed mission', $aboutContent['stories'][1]['heading']);
        $uploadedPath = str_replace('storage/', '', $aboutContent['stories'][1]['image']);
        Storage::disk('public')->assertExists($uploadedPath);

        $this->get('/about')
            ->assertOk()
            ->assertSee('A refreshed Pixmoree story')
            ->assertSee('A refreshed mission');

        $this->post('/admin/content/home', [
            'content' => ['hero_heading' => 'A refreshed home page'],
        ])->assertRedirect('/admin/content/home');
        $this->get('/')->assertOk()->assertSee('A refreshed home page');

        $this->post('/admin/content/marketplace', [
            'content' => ['hero' => ['heading_before' => 'Discover a refreshed']],
        ])->assertRedirect('/admin/content/marketplace');
        $this->get('/week12')->assertOk()->assertSee('Discover a refreshed');
    }

    public function test_admin_can_create_update_and_delete_an_artwork(): void
    {
        $this->seed(ArtworkSeeder::class);
        $admin = User::create([
            'name' => 'Beta Admin',
            'email' => 'admin@example.test',
            'password' => 'secret-password',
        ]);
        $admin->forceFill(['role' => 'admin'])->save();
        $this->actingAs($admin);

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

    public function test_creator_artwork_uploads_are_persistent_and_identical_images_are_rejected(): void
    {
        Storage::fake('public');
        $this->seed(ArtworkSeeder::class);
        $creator = User::query()->where('email', 'noah.creator@pixmoree.test')->firstOrFail();
        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lFwAAAAASUVORK5CYII=');
        $this->assertNotFalse($imageContent);

        $listing = [
            'title' => 'Noah Original',
            'description' => 'A newly uploaded original illustration.',
            'category' => 'Illustration',
            'price' => '0.50',
            'originality' => '1',
            'image' => UploadedFile::fake()->createWithContent('noah-original.png', $imageContent),
        ];

        $this->actingAs($creator)
            ->post('/user/artworks', $listing)
            ->assertRedirect('/user/create')
            ->assertSessionHasNoErrors();

        $saved = DB::table('artworks')->where('title', 'Noah Original')->first();
        $this->assertNotNull($saved);
        $this->assertSame($creator->id, $saved->user_id);
        $this->assertSame('Listed', $saved->status);
        $this->assertNotNull($saved->image_hash);
        Storage::disk('public')->assertExists(substr($saved->image, strlen('storage/')));
        $this->get('/')->assertOk()->assertSee('Noah Original');
        $this->get('/week12')->assertOk()->assertSee('8 listings loaded from the database.');

        $listing['image'] = UploadedFile::fake()->createWithContent('same-original.png', $imageContent);
        $this->post('/user/artworks', $listing)->assertSessionHasErrors('image');
        $this->assertDatabaseCount('artworks', 8);

        $this->patch('/user/artworks/' . $saved->id, [
            'title' => 'Noah Updated Original',
            'description' => 'The creator updated this listing.',
            'category' => 'Digital Art',
            'price' => '0.75',
        ])->assertRedirect('/user/nft-management');
        $this->assertDatabaseHas('artworks', [
            'id' => $saved->id,
            'title' => 'Noah Updated Original',
            'price' => '0.75',
        ]);

        $otherUser = User::create([
            'name' => 'Another creator',
            'email' => 'another@example.test',
            'password' => 'secret-password',
        ]);
        $this->actingAs($otherUser)
            ->delete('/user/artworks/' . $saved->id)
            ->assertNotFound();

        $this->actingAs($creator)
            ->delete('/user/artworks/' . $saved->id)
            ->assertRedirect('/user/nft-management');
        $this->assertDatabaseMissing('artworks', ['id' => $saved->id]);
        Storage::disk('public')->assertMissing(substr($saved->image, strlen('storage/')));
    }

    public function test_trending_creators_are_capped_at_twelve_live_accounts(): void
    {
        $this->seed(ArtworkSeeder::class);
        for ($index = 1; $index <= 10; $index++) {
            $creator = User::create([
                'name' => 'extra-creator-' . $index,
                'email' => 'extra-creator-' . $index . '@example.test',
                'password' => 'secret-password',
            ]);
            $creator->artworks()->create([
                'id' => 'extra-artwork-' . $index,
                'title' => 'Extra artwork ' . $index,
                'creator' => $creator->name,
                'category' => 'Digital Art',
                'price' => 1,
                'image' => 'uploads/extra-' . $index . '.png',
                'description' => 'An extra creator artwork.',
                'status' => 'Listed',
            ]);
        }

        $home = $this->get('/')->assertOk();
        $this->assertSame(12, substr_count($home->getContent(), 'class="creator-mini-card"'));
        $this->assertSame(17, substr_count($home->getContent(), 'class="col-6 col-sm-3 art-column"'));
    }

    public function test_creator_profile_changes_are_reflected_on_the_about_page(): void
    {
        Storage::fake('public');
        $this->seed(ArtworkSeeder::class);
        $creator = User::query()->where('email', 'noah.creator@pixmoree.test')->firstOrFail();
        $imageContent = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lFwAAAAASUVORK5CYII=');
        $this->assertNotFalse($imageContent);

        $this->actingAs($creator)->post('/user/profile', [
            'name' => 'Noah Updated',
            'email' => $creator->email,
            'phone' => '',
            'bio' => 'I make digital illustrations.',
            'avatar' => UploadedFile::fake()->createWithContent('noah-avatar.png', $imageContent),
        ])->assertRedirect('/user/profile-settings');

        $creator->refresh();
        $this->assertSame('Noah Updated', $creator->name);
        $this->assertSame('I make digital illustrations.', $creator->bio);
        Storage::disk('public')->assertExists(substr($creator->avatar_path, strlen('storage/')));
        $this->get('/about')
            ->assertOk()
            ->assertSee('Noah Updated')
            ->assertSee('I make digital illustrations.');
    }
}
